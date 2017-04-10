#
# Table structure for table 'tx_rss2import_feeds'
#
CREATE TABLE tx_rss2import_feeds (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	url tinytext NOT NULL,
	errors VARCHAR(255) NOT NULL DEFAULT '',
	errors_count int(11) DEFAULT '0' NOT NULL,
	target blob NOT NULL,
	override_edited tinyint(4) unsigned DEFAULT '0' NOT NULL,
	import_images tinyint(4) unsigned DEFAULT '0' NOT NULL,
	auto_update_gabriel tinyint(4) unsigned DEFAULT '0' NOT NULL,
	guid_prefix tinytext NOT NULL,
	typoscript_config text NOT NULL,
	default_hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	default_type tinyint(4) DEFAULT '0' NOT NULL,
	default_categories blob NOT NULL,
	default_author tinytext NOT NULL,
	default_authoremail tinytext NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tt_news'
#
CREATE TABLE tt_news (
	tx_rss2import_uid tinytext NOT NULL,
	tx_rss2import_edited tinyint(4) unsigned DEFAULT '0' NOT NULL
);
