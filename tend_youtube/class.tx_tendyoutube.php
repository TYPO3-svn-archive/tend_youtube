<?php
/* By Oto Brglez - <oto.brglez@tend.si> */

/* $Id$ */

$dir = implode(DIRECTORY_SEPARATOR, explode(DIRECTORY_SEPARATOR,dirname(__file__)));
set_include_path(get_include_path() . PATH_SEPARATOR . $dir);

require_once("Zend/Loader.php");
Zend_Loader::loadClass("Zend_Gdata_YouTube");
Zend_Loader::loadClass('Zend_Gdata_AuthSub');
Zend_Loader::loadClass('Zend_Gdata_App_Exception');
Zend_Loader::loadClass('Zend_Gdata_ClientLogin'); 

class tx_tendyoutube {

    private $video; // Video
    private $pid; // pid
    private $yt_conf = null;
    private $yt;
    private $sql;

    public function getSQL(){
        return $this->sql;
    }

    /* Konstruktor */
    public function  __construct($pid=false) {
        if($pid) $this->pid = pid;
    }

    public function authenticate() {
    }

    public function setYouTubeConf($key) {
        $this->yt_conf = $key;
    }

    /* Nastavi podatke */
    public function upload2YouTube($video) {
        $this->video = (array)$video; // video data;
    }

    /* Pomaga pri fetchanju */
    public function getRows($sql) {
        $this->sql = $sql;
        $res = $GLOBALS['TYPO3_DB']->sql_query($sql);
        if($GLOBALS['TYPO3_DB']->sql_num_rows($res)==0) return false;
        $data = array();
        while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) $data[] = $row;
        return $data;
    }

    /* Vrne kategorije */
    public function getAllVideoCategories() {
        $sql = sprintf("SELECT
            cat.uid, cat.cat_title, cat.tstamp, cat.crdate, cat.cat_title as title
            FROM tx_tendyoutube_category cat
            WHERE cat.deleted=0 AND cat.hidden=0
            ORDER BY cat_title ASC");
        $cat = $this->getRows($sql);
        return count($cat)==0?false:$cat;
    }

    /* Vrne vse vide */
    public function getPlaylistList() {
        return $this->yt->getPlaylistListFeed($this->yt_conf["username"]);
    }

    public function authAndGetGData($user_p,$pass_p,$api_key_p){
        $conf = $this->yt_conf;
        /*
        $user = $conf["tend_youtube_api_user"];
        $user_slot = $conf["tend_youtube_user"];
        $pass = $conf["tend_youtube_api_pass"];
        $api_key = $conf["tend_youtube_api_key"];
        */
        $pid = $conf["tend_youtube_pid"];

        $user = $user_p;
        $pass = $pass_p;
        $api_key = $api_key_p;

        $authenticationURL= 'https://www.google.com/youtube/accounts/ClientLogin';
        $httpClient = Zend_Gdata_ClientLogin::getHttpClient(
                $username = $user, $password = $pass, $service = 'youtube', $client = null,
                $source = 'tend_youtube', // a short string identifying your application
                $loginToken = null, $loginCaptcha = null, $authenticationURL);

        $developerKey = $api_key;
        $applicationId = 'tend_youtube';
        $clientId = 'tend_youtube';

        return new Zend_Gdata_YouTube($httpClient, $applicationId, $clientId, $developerKey);
    }

}// eofclass

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_youtube/class.tx_tendyoutube.php']) {
    include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_youtube/class.tx_tendyoutube.php']);
}
