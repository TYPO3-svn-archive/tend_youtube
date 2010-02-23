<?php

/* By Oto Brglez - <otobrglez@gmail.com> */

if (!defined ('PATH_typo3conf')) die ('Could not access this script directly!');

require_once(PATH_tslib . 'class.tslib_pibase.php');
require_once 'class.tx_tendyoutube.php';
require_once("Zend/Loader.php");

Zend_Loader::loadClass("Zend_Gdata_YouTube");
Zend_Loader::loadClass('Zend_Gdata_AuthSub');
Zend_Loader::loadClass('Zend_Gdata_App_Exception');
Zend_Loader::loadClass('Zend_Gdata_ClientLogin');

$out = array();

function getYTAC($username,$arr){
  foreach($arr as $user)
      if($user["lab"] == trim($username))
          return $user;
  return false;
};

class tx_tendyoutube_eid extends tslib_pibase {
    var $prefixId      = 'tx_tendyoutube_eid';
    var $scriptRelPath = 'class.tx_tendyoutube_eid.php';
    var $extKey        = 'tend_youtube';

    function eid_main() {
        $GLOBALS['TSFE']->fe_user = tslib_eidtools::initFeUser();
        tslib_eidtools::connectDB();
        
        $conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['tend_youtube']);

        $ytx = new tx_tendyoutube();

        $api_ids = array();
        foreach($conf as $key=>$val)
            if(strpos($key,"_lab_")!==false || strpos($key,"_user_")!==false || strpos($key,"_pass_")!==false || strpos($key,"_key_")!==false ){
                $api_ids[  (int)substr($key,strrpos($key, "_")+1) ][ "id" ] = (int)substr($key,strrpos($key, "_")+1);
                $api_ids[  (int)substr($key,strrpos($key, "_")+1) ][ implode("", array_slice(explode("_",$key),3,1)) ] = trim($val);
            }


        $sql = "
        SELECT video.*, cat.cat_title FROM tx_tendyoutube_video video, tx_tendyoutube_category cat
            WHERE video.deleted=0 AND video.hidden=1 AND video.is_error=0 AND video.cat = cat.uid
        LIMIT 5
        ";

        
        $rx = $ytx->getRows($sql);
        if($rx != false)
            foreach($rx as $video) {
          
                $c = getYTAC($video["youtube_user"],$api_ids);
                $yt = getYT($c["user"], $c["pass"], $c["key"]);

                $out["upload"][] = array("file_name"=> trim($video["file_name"]));

                // create a new VideoEntry object
                $myVideoEntry = new Zend_Gdata_YouTube_VideoEntry();

                // create a new Zend_Gdata_App_MediaFileSource object
                $filesource = $yt->newMediaFileSource($video["raw_video"]);
                $filesource->setContentType($video["file_mime"]);

                // set slug header
                $filesource->setSlug($video["file_name"]);

                // add the filesource to the video entry
                $myVideoEntry->setMediaSource($filesource);

                $myVideoEntry->setVideoTitle($video["title"]);
                $myVideoEntry->setVideoDescription($video["description"]);

                // The category must be a valid YouTube category!
                $myVideoEntry->setVideoCategory($video["cat_title"]);

                // Set keywords. Please note that this must be a comma-separated string
                // and that individual keywords cannot contain whitespace
                $myVideoEntry->SetVideoTags($video["keywords"]);

                // set some developer tags -- this is optional
                // (see Searching by Developer Tags for more details)
                $myVideoEntry->setVideoDeveloperTags(array('tend_youtube'));

                // set the video's location -- this is also optional
                /*
                $yt->registerPackage('Zend_Gdata_Geo');
                $yt->registerPackage('Zend_Gdata_Geo_Extension');
                $where = $yt->newGeoRssWhere();
                $position = $yt->newGmlPos('37.0 -122.0');
                $where->point = $yt->newGmlPoint($position);
                $myVideoEntry->setWhere($where);
                */

                // upload URI for the currently authenticated user
                $uploadUrl = 'http://uploads.gdata.youtube.com/feeds/api/users/default/uploads';

                // try to upload the video, catching a Zend_Gdata_App_HttpException,
                // if available, or just a regular Zend_Gdata_App_Exception otherwise
                $napaka = false;
                try {
                    $newEntry = $yt->insertEntry($myVideoEntry, $uploadUrl, 'Zend_Gdata_YouTube_VideoEntry');
                    $id = explode('/',$newEntry->getId());
                    $id = $id[count($id)-1];
                    $out["upload"][count($out["upload"])-1]["video_id"] = trim($id);
                } catch (Zend_Gdata_App_HttpException $httpException) {
                    $napaka = true;
                    $out["upload"][count($out["upload"])-1]["Zend_Gdata_App_HttpException"] = $httpException->getRawResponseBody();
                } catch (Zend_Gdata_App_Exception $e) {
                    $napaka = true;
                    $out["upload"][count($out["upload"])-1]["Zend_Gdata_App_Exception"] = $e->getMessage();
                }

                if($napaka == false) {
                    $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_tendyoutube_video',
                            'uid='.intval($video["uid"]),
                            array('youtube_id'=>$id,'tstamp'=> time(), 'hidden'=>0,'is_error'=>0));
                } else {
                    $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_tendyoutube_video',
                            'uid='.intval($video["uid"]),
                            array('tstamp'=> time(), 'hidden'=>0,'is_error'=>1));
                };

                unset($yt);
        }; // foreach

        /*
         * List sync
        */

        /* ZA VSE USERJE */
        $yt_users = explode(",",$conf["tend_youtube_users"]);
        array_walk($yt_users, create_function('&$v,$k','$v=trim($v);'));

        foreach($yt_users as $yt_user):

            if( getYTAC($yt_user,$api_ids) != false ){
                unset($yt);
                $c = getYTAC($yt_user,$api_ids);
                $yt = getYT($c["user"], $c["pass"], $c["key"]);
            }
      
            $vids = $yt->getuserUploads($yt_user);

            foreach($vids as $video) {
                $out["sync"][] = array("title"=>$video->getVideoTitle());
                $out["sync"][count($out["sync"])-1]["video_id"] = $video->getVideoId();
                $out["sync"][count($out["sync"])-1]["updated"] = $video->getUpdated()->getText();

                $p = $ytx->getRows("SELECT uid FROM tx_tendyoutube_video v
                    WHERE v.youtube_id='".$video->getVideoId()."' LIMIT 1");
                if($p==false) {
                    $data_i = array("pid"=>$pid,
                            "cruser_id" => 0,
                            "author" => 0,
                            "tstamp" => time(),
                            "crdate" => strtotime($video->getUpdated()->getText()),
                            "hidden" => 0,
                            "deleted" => 0,
                            "title" => $video->getVideoTitle(),
                            "keywords" => implode(", ", $video->getVideoTags()),
                            "youtube_id" => $video->getVideoId(),
                            "youtube_user" => $yt_user,
                            "youtube_cat" => $video->getVideoCategory(),
                            "description" => $video->getVideoDescription(),
                    );

                    $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_tendyoutube_video',$data_i);
                }//endif

            }// foreach

            
        endforeach; // konec up zanke

        /*
         * OUTPUT
         */
        echo t3lib_div::array2json($out);
    }

}// class

function getYT($user,$pass,$api_key){
        /*
        $user = $conf["tend_youtube_api_user"];
        $pass = $conf["tend_youtube_api_pass"];
        $api_key = $conf["tend_youtube_api_key"];
        $pid = $conf["tend_youtube_pid"];
        */

        $authenticationURL= 'https://www.google.com/youtube/accounts/ClientLogin';
        $httpClient = Zend_Gdata_ClientLogin::getHttpClient(
                $username = $user, $password = $pass, $service = 'youtube', $client = null,
                $source = 'tend_youtube',
                $loginToken = null, $loginCaptcha = null, $authenticationURL);

        $developerKey = $api_key;
        $applicationId = 'tend_youtube';
        $clientId = 'tend_youtube';

        $yt = new Zend_Gdata_YouTube($httpClient, $applicationId, $clientId, $developerKey);
        return $yt;
}


function printVideoFeed($videoFeed) {
    $count = 1;
    foreach ($videoFeed as $videoEntry) {
        echo "Entry # " . $count . "\n";
        printVideoEntry($videoEntry);
        echo "\n";
        $count++;
    }
}

function printVideoEntry($videoEntry) {
    // the videoEntry object contains many helper functions
    // that access the underlying mediaGroup object
    echo 'Video: ' . $videoEntry->getVideoTitle() . "\n";
    echo 'Video ID: ' . $videoEntry->getVideoId() . "\n";
    echo 'Updated: ' . $videoEntry->getUpdated() . "\n";
    echo 'Description: ' . $videoEntry->getVideoDescription() . "\n";
    echo 'Category: ' . $videoEntry->getVideoCategory() . "\n";
    echo 'Tags: ' . implode(", ", $videoEntry->getVideoTags()) . "\n";
    echo 'Watch page: ' . $videoEntry->getVideoWatchPageUrl() . "\n";
    echo 'Flash Player Url: ' . $videoEntry->getFlashPlayerUrl() . "\n";
    echo 'Duration: ' . $videoEntry->getVideoDuration() . "\n";
    echo 'View count: ' . $videoEntry->getVideoViewCount() . "\n";
    echo 'Rating: ' . $videoEntry->getVideoRatingInfo() . "\n";
    echo 'Geo Location: ' . $videoEntry->getVideoGeoLocation() . "\n";
    echo 'Recorded on: ' . $videoEntry->getVideoRecorded() . "\n";

    // see the paragraph above this function for more information on the
    // 'mediaGroup' object. in the following code, we use the mediaGroup
    // object directly to retrieve its 'Mobile RSTP link' child
    foreach ($videoEntry->mediaGroup->content as $content) {
        if ($content->type === "video/3gpp") {
            echo 'Mobile RTSP link: ' . $content->url . "\n";
        }
    }

    echo "Thumbnails:\n";
    $videoThumbnails = $videoEntry->getVideoThumbnails();

    foreach($videoThumbnails as $videoThumbnail) {
        echo $videoThumbnail['time'] . ' - ' . $videoThumbnail['url'];
        echo ' height=' . $videoThumbnail['height'];
        echo ' width=' . $videoThumbnail['width'] . "\n";
    }
}

$extensionkey = t3lib_div::makeInstance('tx_tendyoutube_eid');
$extensionkey->eid_main();

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/tend_youtube/class.tx_tendyoutube_eid.php"])
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/tend_youtube/class.tx_tendyoutube_eid.php"]);
