<?php

if(!function_exists("gr")) {
    function gr() {
        foreach(explode("\n",file_get_contents(__file__)) as $n) if(strpos($n,"Id:") !== false) {
                list(,,,$p) = explode(" ",$n);
                return $p;
        }
    };
}

define("podpis",sprintf("/* tend_youtube-svn_relese_%d by Oto Brglez - <oto.brglez@tend.si> */\n",intval(gr())));

/* $Id$ */

$sp = stristr(php_os, 'WIN')?"\\":"/";
define("sp",$sp);
require_once(PATH_tslib.'class.tslib_pibase.php');
require_once( dirname(__file__).$sp."..".$sp."class.tx_tendyoutube.php");
require_once(t3lib_extMgm::extPath("jquery")."class.tx_jquery.php");


class tx_tendyoutube_pi1 extends tslib_pibase {
    var $prefixId      = 'tx_tendyoutube_pi1';
    var $scriptRelPath = 'pi1/class.tx_tendyoutube_pi1.php';
    var $extKey        = 'tend_youtube';
    var $pi_checkCHash = true;
    var $conf_ts       = array();

    function init($content, $conf) {
        $this->conf_ts = $conf;
    }

    /* main */
    function main($content, $conf) {
        $this->conf_ts = $conf;
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();

        tx_jquery::includeLib();

        $tmp_js = file_get_contents(t3lib_extMgm::siteRelPath("tend_youtube")."res/js/tend_youtube.js");
        $GLOBALS['TSFE']->additionalHeaderData[$this->prefixId."_js"]
                = TSpagegen::inline2TempFile(podpis.$tmp_js, 'js');

        /* Varovalo - upload_user mora biti nastavljen! */
        if(empty($this->conf_ts["upload_user"]))
            return "Please set upload_user in TS!<br/>".PHP_EOL;
        
        return $this->buildForm();
    }

    /* Naredi formo */
    function buildForm() {
        $this->templateObrazec = $this->cObj->fileResource($this->conf["main_template"] ? $this->conf["main_template"] :
                "EXT:tend_youtube/res/templates/tend_youtube_upload.html");

        $template["total"] = $this->cObj->getSubpart($this->templateObrazec,'###template###');
        $template["cat_selected"] = $this->cObj->getSubpart($template["total"],'###cat_selected###');
        $template["cat"] = $this->cObj->getSubpart($template["total"],'###cat###');

        $content = $this->cObj->substituteMarkerArray($template["total"], array(
                "###form_name###"=>$this->prefixId,
                "###pid###"=>$this->pid,
                "###max_file_size###" => 10000, // TS!
                "###form_action###"=>$this->pi_getPageLink($GLOBALS['TSFE']->id)));

        /* ### video ### */
        $error_all = "";
        $video = new tx_tendyoutube();
        $video->setYouTubeConf( unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['tend_youtube']) );

        $yt_x = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['tend_youtube']);

        $polja = array("description","title","keywords","raw_video");
        $error_st = array();
        $vsebina_st = array();
        $upload_done=false;
        $data = array();
        
        foreach($polja as $polje) {
            $data[$polje] = trim($this->piVars[$polje]);
            $error_st[sprintf("###error_%s###",$polje)] = "";
            $vsebina_st[sprintf("###d_%s###",$polje)] = trim($this->piVars[$polje])!=""?trim($this->piVars[$polje]):"";
        }//eforeach

        $allOk = true;

        if(intval($this->piVars["postit"])==1)
            if(trim($data["description"]) == "" ||
                    trim($data["title"]) == "" ||
                    trim($data["keywords"]) == "" ||
                    basename($_FILES['tx_tendyoutube_pi1']['name']['raw_video'])=="" ) {
                $error_all = "<br/><span style=\"color:red\">Fill all required fields!</span>";
                $allOk = false;
            }

        if(intval($this->piVars["postit"])==1)
            if($allOk==true) {

                $ff = new t3lib_basicFileFunctions();
                $name = basename( $_FILES['tx_tendyoutube_pi1']['name']['raw_video']);
                $name = $ff->getUniqueName($ff->cleanFileName($name), "uploads/tx_tendyoutube");
                $target_path = $name;

                if(move_uploaded_file($_FILES['tx_tendyoutube_pi1']['tmp_name']['raw_video'], $target_path)) {
                    $error_all = "<br/><span style=\"color:green;\">The file ".  basename( $_FILES['tx_tendyoutube_pi1']['name']['raw_video'])." has been uploaded.</span>";
                } else {
                    $allOk==false;
                    $error_all = "<br/><span style=\"color:red;\">There was an error uploading the file, please try again!</span>";
                }

                if($allOk==true) {
                    $user_uid = intval($GLOBALS['TSFE']->fe_user->user["uid"]);
                    $user_id = $user_uid; // ;)

                    if($user_uid==0) {
                        $allOk = false;
                        $error_all = "<br/><span style=\"color:red;\">Only memebers can upload videos!</span>";
                    } else {
                        // SAVE RECORD

                        $GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;
                        $data_i = array("pid"=>intval($GLOBALS['TSFE']->id),
                                "cruser_id" => intval($user_uid),
                                "author" => intval($user_uid),
                                "tstamp" => time(),
                                "crdate" => time(),
                                "hidden" => 1, // skrit. ko se nalozi je odkrit
                                "deleted" => 0,
                                "title" => $this->piVars["title"],
                                "cat" => (int)$this->piVars["cat"],
                                "keywords" => $this->piVars["keywords"],
                                "description" => $this->piVars["description"],
                                "raw_video" => $target_path,
                                "file_name" => basename($target_path),
                                "file_md5" => md5_file($target_path),
                                "file_mime" =>  trim($_FILES['tx_tendyoutube_pi1']['type']['raw_video']) ,
                                "youtube_user" => $this->conf_ts["upload_user"],
                        );

                        $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_tendyoutube_video',$data_i);
                        $upload_done = true;
                    };
                };
        };

        $cat_list = "";
        $categories = $video->getAllVideoCategories();
        if($categories)
            foreach($categories as $cat) {
                $cat_list .= $this->cObj->substituteMarkerArray($template["cat"],
                        array("###value###"=>$cat["uid"],
                            "###text###"=>$cat["title"]));
            }

        // Napaka
        $content = $this->cObj->substituteMarkerArray($content, $error_st);

        // Vsebina
        $content = $this->cObj->substituteMarkerArray($content, $vsebina_st);

        $script = '
        <script type="text/javascript">
            /* Pozeni prenost... */
      
            jQuery("#upload_state").fadeIn("slow");
            jQuery.getJSON(\'index.php?eID=tend_youtube\', function(data) {
                jQuery("#upload_state").fadeOut("slow",function(){
                    window.location.href = unescape(window.location.href);
                });
            });

        </script>
        ';

        $content = $this->cObj->substituteMarkerArray($content,
                array("###error_all###" => $error_all,
                    "###cache###" => "", // no_cache=0
                    "###js###" => ($upload_done==true?$script:""),
                    "###off###"=> (intval($GLOBALS['TSFE']->fe_user->user["uid"])==0?' disabled="disabled"':"")  ));

        $content = $this->cObj->substituteSubpart($content, '###cat_list###', $cat_list);
        return $this->pi_wrapInBaseClass( "\n".tx_tendyoutube_pi1::cleanNL($content) );
    }

    /* Pocisti string */
    function clean($str) {
        return $str;
        $str = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $str);
        $str = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/", "", $str);
        return $str;
    }

    /* Odstrani newline */
    function cleanNL($str) {
        return $str;
        $str = preg_replace('/\s+/', ' ', $str);
        $str = trim($str);
        return str_replace(array("\n","\r\n"), '', $str);
    }

}// eofclass

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_youtube/pi1/class.tx_tendyoutube_pi1.php'])
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_youtube/pi1/class.tx_tendyoutube_pi1.php']);
