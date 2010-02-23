<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_tendyoutube_video'] = array (
	'ctrl' => $TCA['tx_tendyoutube_video']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,is_error,title,cat,keywords,description,video_private,developer_tab,video_location,file_name,file_mime,file_md5,raw_video,author,youtube_id,youtube_user'
	),
	'feInterface' => $TCA['tx_tendyoutube_video']['feInterface'],
	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
            	'is_error' => array (
			'exclude' => 0,
			'label'   => 'Error while uploading',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'title' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tend_youtube/locallang_db.xml:tx_tendyoutube_video.title',		
			'config' => array (
				'type' => 'input',	
				'size' => '48',	
				'max' => '150',	
				'eval' => 'required,trim',
			)
		),
		'cat' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tend_youtube/locallang_db.xml:tx_tendyoutube_video.cat',		
			'config' => array (
				'type' => 'select',	
				'foreign_table' => 'tx_tendyoutube_category',	
				'foreign_table_where' => 'ORDER BY tx_tendyoutube_category.uid',	
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,	
				'wizards' => array(
					'_PADDING'  => 2,
					'_VERTICAL' => 1,
					'add' => array(
						'type'   => 'script',
						'title'  => 'Create new record',
						'icon'   => 'add.gif',
						'params' => array(
							'table'    => 'tx_tendyoutube_category',
							'pid'      => '###CURRENT_PID###',
							'setValue' => 'prepend'
						),
						'script' => 'wizard_add.php',
					),
					'list' => array(
						'type'   => 'script',
						'title'  => 'List',
						'icon'   => 'list.gif',
						'params' => array(
							'table' => 'tx_tendyoutube_category',
							'pid'   => '###CURRENT_PID###',
						),
						'script' => 'wizard_list.php',
					),
				),
			)
		),
		'keywords' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tend_youtube/locallang_db.xml:tx_tendyoutube_video.keywords',		
			'config' => array (
				'type' => 'input',	
				'size' => '48',	
				'max' => '200',	
				'eval' => 'trim',
			)
		),
		'description' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tend_youtube/locallang_db.xml:tx_tendyoutube_video.description',		
			'config' => array (
				'type' => 'text',
				'cols' => '30',	
				'rows' => '5',
			)
		),
		'video_private' => array (		
			'exclude' => 0,
			'label' => 'LLL:EXT:tend_youtube/locallang_db.xml:tx_tendyoutube_video.video_private',		
			'config' => array (
				'type' => 'check',
			)
		),
		'developer_tab' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tend_youtube/locallang_db.xml:tx_tendyoutube_video.developer_tab',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'trim',
			)
		),
		'video_location' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tend_youtube/locallang_db.xml:tx_tendyoutube_video.video_location',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'trim',
			)
		),
		'file_name' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tend_youtube/locallang_db.xml:tx_tendyoutube_video.file_name',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'trim',
			)
		),
		'file_mime' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tend_youtube/locallang_db.xml:tx_tendyoutube_video.file_mime',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'trim',
			)
		),
            	'file_md5' => array (
			'exclude' => 0,
			'label' => 'File MD5',
			'config' => array (
				'type' => 'input',
				'size' => '32',
				'eval' => 'trim',
			)
		),
		'raw_video' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tend_youtube/locallang_db.xml:tx_tendyoutube_video.raw_video',		
			'config' => array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => '',	
				'disallowed' => 'php,php3',	
				'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],	
				'uploadfolder' => 'uploads/tx_tendyoutube',
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'author' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tend_youtube/locallang_db.xml:tx_tendyoutube_video.author',		
			'config' => array (
				'type' => 'select',	
				'foreign_table' => 'fe_users',	
				'foreign_table_where' => 'ORDER BY fe_users.uid',	
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
                'youtube_id' => array(
                    'exclude' => 0,
                    'label' => 'youtube_id',
                    'config' => array (
                            'type' => 'input',
                            'size' => '30',
                            'eval' => 'trim',
                    )
                ),
                'youtube_user' => array(
                    'exclude' => 0,
                    'label' => 'youtube_user',
                    'config' => array (
                            'type' => 'input',
                            'size' => '50',
                            'eval' => 'trim',
                    )
                )
	),
	'types' => array (
		'0' => array('showitem' => 'hidden;;1;;1-1-1, is_error, title;;;;2-2-2, cat;;;;3-3-3, keywords, description, video_private, developer_tab, video_location, file_name, file_mime, file_md5, raw_video, author, youtube_id, youtube_user')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);



$TCA['tx_tendyoutube_category'] = array (
	'ctrl' => $TCA['tx_tendyoutube_category']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,cat_title'
	),
	'feInterface' => $TCA['tx_tendyoutube_category']['feInterface'],
	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'cat_title' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tend_youtube/locallang_db.xml:tx_tendyoutube_category.cat_title',		
			'config' => array (
				'type' => 'input',	
				'size' => '40',	
				'max' => '40',	
				'eval' => 'required,trim',
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'hidden;;1;;1-1-1, cat_title')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);
?>