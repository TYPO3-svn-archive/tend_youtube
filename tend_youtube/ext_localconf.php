<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');

t3lib_extMgm::addUserTSConfig(' options.saveDocNew.tx_tendyoutube_video=1 ');
t3lib_extMgm::addUserTSConfig(' options.saveDocNew.tx_tendyoutube_category=1 ');

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_tendyoutube_pi1.php', '_pi1', 'list_type', 1);
t3lib_extMgm::addPItoST43($_EXTKEY, 'pi2/class.tx_tendyoutube_pi2.php', '_pi2', 'list_type', 0);

// EID
$TYPO3_CONF_VARS['FE']['eID_include'][$_EXTKEY] = sprintf('EXT:%s/%s',$_EXTKEY,"class.tx_tendyoutube_eid.php");

// Hooks for back-end
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] =
        'EXT:tend_youtube/class.tx_tendyoutube_tcemainprocdm.php:tx_tendyoutube_tcemainprocdm';
?>