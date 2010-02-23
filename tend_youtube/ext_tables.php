<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');

require_once(t3lib_extMgm::extPath('tend_youtube').'class.tx_tendyoutube.php');
require_once(t3lib_extMgm::extPath('tend_youtube').'class.tx_tendyoutube_tcemainprocdm.php');

t3lib_extMgm::allowTableOnStandardPages('tx_tendyoutube_video');
t3lib_extMgm::addToInsertRecords('tx_tendyoutube_video');

$TCA['tx_tendyoutube_video'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:tend_youtube/locallang_db.xml:tx_tendyoutube_video',		
		'label'     =>  "title", // CONCAT(title,' - ',youtube_user) as title ",
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'film.png',
	),
);

t3lib_extMgm::allowTableOnStandardPages('tx_tendyoutube_category');

t3lib_extMgm::addToInsertRecords('tx_tendyoutube_category');

$TCA['tx_tendyoutube_category'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:tend_youtube/locallang_db.xml:tx_tendyoutube_category',		
		'label'     => 'cat_title',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'film_key.png',
	),
);

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key';

t3lib_extMgm::addPlugin(array(
	'LLL:EXT:tend_youtube/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'film.png'
),'list_type');


/* pi2 */

t3lib_extMgm::addPlugin(array(
	'LLL:EXT:tend_youtube/locallang_db.xml:tt_content.list_type_pi2',
	$_EXTKEY . '_pi2',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'film.png'
),'list_type');

/*
t3lib_extMgm::addStaticFile(
	$_EXTKEY,
	'pi1/static/', 'tend_youtube_settings'
);
*/

?>