<?php

/* By Oto Brglez - <oto.brglez@tend.si> */
/* $Id$ */

class tx_tendyoutube_tcemainprocdm {

    function processDatamap_preProcessFieldArray($incomingFieldArray, $table, $id, &$ref) {
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
