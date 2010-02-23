<?php

/* By Oto Brglez - <oto.brglez@tend.si> */

/* $Id$ */

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath("jquery")."class.tx_jquery.php");

class tx_tendyoutube_pi2 extends tslib_pibase {
    var $prefixId      = 'tx_tendyoutube_pi2';		// Same as class name
    var $scriptRelPath = 'pi2/class.tx_tendyoutube_pi2.php';	// Path to this script relative to the extension dir.
    var $extKey        = 'tend_youtube';	// The extension key.
    var $pi_checkCHash = true;

    function main($content, $conf) {
        define("podpis","/* Oto Brglez - <oto.brglez@tend.si> */\n");
        $this->conf_ts = $conf;

        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();

        tx_jquery::includeLib();


        /* Default CSS */
        $tmp_css = file_get_contents(t3lib_extMgm::siteRelPath("tend_youtube")."res/css/tend_youtube.css");

        $GLOBALS['TSFE']->additionalHeaderData[$this->prefixId."_css"]
                = TSpagegen::inline2TempFile(podpis.$tmp_css, 'css');

        /* CSS za prettyPhoto
        $tmp_css = file_get_contents(t3lib_extMgm::siteRelPath("tend_youtube")."src/prettyPhoto/css/prettyPhoto.css");
        //$tmp_css = file_get_contents(t3lib_extMgm::siteRelPath("tend_youtube")."res/css/prettyPhoto.css");
        $GLOBALS['TSFE']->additionalHeaderData[$this->prefixId."_pp_css"]
                = TSpagegen::inline2TempFile(podpis.$tmp_css, 'css');
         *  
        */
        $GLOBALS['TSFE']->additionalHeaderData[$this->prefixId."_pp_css"] =
                '<link href="typo3conf/ext/tend_youtube/src/prettyPhoto/css/prettyPhoto.css" type="text/css" rel="stylesheet""></link>';

        /* Za prettyPhoto JS*/
        $tmp_css = file_get_contents(t3lib_extMgm::siteRelPath("tend_youtube")."src/prettyPhoto/js/jquery.prettyPhoto.js");
        $GLOBALS['TSFE']->additionalHeaderData[$this->prefixId."_pp_js"]
                = TSpagegen::inline2TempFile(podpis.$tmp_css, 'js');

        /* JS */
        $js = '

        jQuery.noConflict();

        (function($) {

            $(document).ready(function(){
                $(".video_ph a[rel^=\'prettyPhoto\']").prettyPhoto({theme:\'light_square\'});
            });

        })(jQuery); 

        ';

        $GLOBALS['TSFE']->additionalHeaderData[$this->prefixId."_pp_js_activate"] = TSpagegen::inline2TempFile(podpis.$js, 'js');

        return $this->pi_wrapInBaseClass( tx_tendyoutube_pi2::cleanNL( $this->buildAll() ) );
    }

    static function clean($str) {
        $str = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $str);
        $str = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/", "", $str);
        return $str;
    }

    static function cleanNL($str) {
        $str = preg_replace('/\s+/', ' ', $str);
        $str = trim($str);
        return str_replace(array("\n","\r\n"), '', $str);
    }

    function buildAll() {
        $stran = intval(t3lib_div::_GP('stran'));
        $per_page = intval($this->conf_ts["per_page"])==0?3:intval($this->conf_ts["per_page"]);

        $this->templateObrazec = $this->cObj->fileResource($this->conf["main_template"] ? $this->conf["main_template"] :
                "EXT:tend_youtube/res/templates/tend_youtube_list.html");

        $template["total"] = $this->cObj->getSubpart($this->templateObrazec,'###template###');
        $template["video"] = $this->cObj->getSubpart($template["total"],'###video###');

        $content = $this->cObj->substituteMarkerArray($template["total"], array(
                "###form_name###"=>$this->prefixId,
                "###pid###"=>$this->pid,
                "###max_file_size###" => 10000, // TS!
                "###form_action###"=>$this->pi_getPageLink($GLOBALS['TSFE']->id)));

        $cnf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['tend_youtube']);

        //TODO: Odstrani!
        $user_slot = $cnf["tend_youtube_user"];

        $users_to_see = explode(",",$this->conf_ts["users_to_see"]);
        array_walk( $users_to_see, create_function('&$v,$k','$v=trim($v);'));

        /* Dobim podatke s katertimi naredim fetch */
        $api_conf = array();
        $n = false; // id racuna.
        foreach( $cnf as $k=>$val){
            if(strpos($k,"_api")) $api_conf[$k] = $val;
            if(trim($val) == trim($this->conf_ts["user_for_fetch"])){ $n = (int)substr($k,strrpos($k,"_")+1); }
        }

        $fetch_user = array();
        if($n!=false){
            foreach($cnf as $k=>$val){
                if(strpos($k,"_".$n)!==false)
                    $fetch_user[ substr($k,0,strrpos($k,"_".$n)) ] = trim($val);
            }
        }

        $yt = new tx_tendyoutube();
        $yt->setYouTubeConf( $cnf );

        $youtube = $yt->authAndGetGData(
                    $fetch_user["tend_youtube_api_user"],
                    $fetch_user["tend_youtube_api_pass"],
                    $fetch_user["tend_youtube_api_key"]
                );

        $user_filter = "\n AND (";
        foreach($users_to_see as $u2see)
            $user_filter .= sprintf(" youtube_user = '%s' OR",$u2see);
        $user_filter = substr($user_filter,0,strlen($user_filer)-3); // OR -2
        $user_filter .= " )";

        /* Database Videos */
        $video_database = $yt->getRows("SELECT * FROM tx_tendyoutube_video WHERE
                deleted=0 AND hidden=0 AND is_error=0 ".$user_filter."
                ORDER BY crdate DESC");

        $video_list = array(); // list

        /* YouTube Videos */
        foreach($users_to_see as $user_slot) {
            $video_youtube = @ $youtube->getuserUploads($user_slot);
            if($video_database!=false && $video_youtube!=false) {
                /* Preverim, ce je v obeh */
                foreach($video_database as $vb) {
                    foreach($video_youtube as $vy) {
                        if($vy->getVideoId() == $vb["youtube_id"]) {
                            $video_list[] = array("video" => $vy, "db"=>$vb);
                            break;
                        }
                    };
                }
            }; // konecif
        } // foreach

        if($stran < 0) $stran = 0;
        if($stran != 0 && $stran > ceil(count($video_list)/$stran) ) $stran = ceil(count($video_list)/$stran);

        foreach( array_slice($video_list,($stran*$per_page),$per_page) as $video_x) {
            $video = $video_x["video"];
            $video_db = $video_x["db"];

            /* Category */
            $cat = "";
            if(intval($video_db["cat"]) == 0){
                $cat = $video->getVideoCategory();
            } else {
                $cat = "&nbsp;";
            };

            $videoThumbnails = $video->getVideoThumbnails();
            $img = $videoThumbnails[ array_rand( array_slice($videoThumbnails, 0, count($videoThumbnails)-1 ),1) ];

            $p_title = str_replace('Š','&#352;',$video->getVideoTitle());
            $p_description = str_replace('Š','&#352;',$video->getVideoDescription());

            $videos_tmp .= $this->cObj->substituteMarkerArray($template["video"],
                    array(
                        "###title###"=> $p_title,
                        "###description###"=>  $p_description,
                        "###description_short###"=> shortDesc($p_description,10),
                        "###category###"=> $cat,
                        "###tags###"=>$video->getVideoTags(),
                        "###image_url###"=> $img["url"],
                        "###image_width###"=>$img["width"],
                        "###image_height###"=>$img["height"],
                        "###video_url###"=>substr($video->getVideoWatchPageUrl(),0,
                    strpos($video->getVideoWatchPageUrl() , "&feature=youtube_gdata")),
            ));
        };
        /* ##### */

        $content = $this->cObj->substituteSubpart($content, '###video_list###', $videos_tmp);

        $strani = count($video_list);
        $g_naprej = $this->pi_linkTP("Naprej", array("stran"=>$stran+1));
        $g_nazaj = $this->pi_linkTP("Nazaj", array("stran"=>$stran-1));

        if($stran == 0) $g_nazaj = "";
        if($stran+1 >= ceil(count($video_list)/$per_page)) $g_naprej ="";

        $pages_n = "";
        for($i=1; $i<= ceil(count($video_list)/$per_page); $i++)
            $pages_n .= " ".$this->pi_linkTP( ($i-1==$stran)?("<span class=\"active\">".$i."</span>"):$i , array("stran"=>$i-1));

        $pager = $g_nazaj." ".$pages_n." ".$g_naprej;

        /* Skrijem pager? */
        if(isset($this->conf_ts["pager_visible"]))
        $pager = intval($this->conf_ts["pager_visible"])==0?"": $pager;

        $content = $this->cObj->substituteMarkerArray($content, array("###pager###"=>$pager));
        return $content;
    }
}// eofclass

function shortDesc($str,$len=5){
        if(trim($str)=="") return "";
        $pom = implode(" ", array_slice( explode(" ",$str), 0, $len));
        return $pom."...";
}


function smartEnc($string) { // se vedno ne deluje

    $tot = "";
    $string_p = " &.abcčdefghijklmnoprsštuvzž1234567890";

    foreach(str_split($string) as $c) {
        foreach(str_split($string_p) as $p) {
            if(strtoupper($c) == strtoupper($p)) {
                $tot .= $c;
                continue;
            }
        }
    }

    return $tot;
}

function fixEncoding($input, $output_encoding="UTF8") {
    if(!function_exists('mb_detect_encoding')) return $input;
    $encoding = mb_detect_encoding($input);
    switch($encoding) {
        case 'ASCII':
        case $output_encoding:
            return $input;
        case '':
            return mb_convert_encoding($input, $output_encoding);
        default:
            return mb_convert_encoding($input, $output_encoding, $encoding);
    }
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_youtube/pi2/class.tx_tendyoutube_pi2.php'])
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_youtube/pi2/class.tx_tendyoutube_pi2.php']);
