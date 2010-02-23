<?php

/* By Oto Brglez - <oto.brglez@tend.si> */
/* $Id$ */

class tx_tendyoutube_tcemainprocdm {

    /* Pred shranjevanjem v bazo */
    function processDatamap_preProcessFieldArray($incomingFieldArray, $table, $id, &$ref) {
        /*
        if($table == "tx_tendyoutube_video") {
            $pom = $this->getRows(sprintf("SELECT v.* FROM tx_tendyoutube_video v WHERE v.uid = %d",$id));
            if(count($pom) == 0) return;

            $tyt = new tx_tendyoutube();
            $tyt->setVideo($pom[0]);
            $tyt->setNewVideo($incomingFieldArray);
            
            //TODO:: Ne gre to... ne vem zaka?! MRS Typo3!!! $tyt->updateVideo();
        } */
    }

    /* Po shranjevanju v bazo */
    function processDatamap_postProcessFieldArray($status, $table, $id, $fieldArray, &$ref) {
    }

    /* Ko se izvedejo cisto vse operacije */
    function processDatamap_afterAllOperations(&$ref) {
    }

}//eofclass

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/tend_youtube/class.tx_tendyoutube_tcemainprocdm.php"])
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/tend_youtube/class.tx_tendyoutube_tcemainprocdm.php"]);
