#
# Table structure for table 'tx_tendyoutube_video'
#
CREATE TABLE tx_tendyoutube_video (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
        is_error tinyint(4) DEFAULT '0' NOT NULL,
	title varchar(150) DEFAULT '' NOT NULL,
	cat int(11) DEFAULT '0' NOT NULL,
	keywords varchar(200) DEFAULT '' NOT NULL,
	description text,
	video_private tinyint(3) DEFAULT '0' NOT NULL,
	developer_tab varchar(255) DEFAULT '' NOT NULL,
	video_location varchar(255) DEFAULT '' NOT NULL,
	file_name varchar(255) DEFAULT '' NOT NULL,
	file_mime varchar(255) DEFAULT '' NOT NULL,
        file_md5 varchar(32) DEFAULT '' NOT NULL,
	raw_video text,
	author int(11) DEFAULT '0' NOT NULL,
	youtube_id varchar(30) DEFAULT '' NOT NULL,
        youtube_user varchar(255) DEFAULT '' NOT NULL,
        youtube_cat varchar(40) DEFAULT '' NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_tendyoutube_category'
#
CREATE TABLE tx_tendyoutube_category (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	cat_title varchar(40) DEFAULT '' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);