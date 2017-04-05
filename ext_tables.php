<?php defined ("TYPO3_MODE") || die ("Access denied.");

if (TYPO3_MODE === "BE")	{
	t3lib_extMgm::addModule("tools", "txrss2importM1", "", t3lib_extMgm::extPath($_EXTKEY)."mod1/");
}

$TCA["tx_rss2import_feeds"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:rss2_import/locallang_db.xml:tx_rss2import_feeds",		
		"label" => "title",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY title",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",	
			"starttime" => "starttime",	
			"endtime" => "endtime",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY) . "tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY) . "icon_tx_rss2import_feeds.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, title, url, errors, errors_count, target",
	)
);

$tempColumns = Array (
	"tx_rss2import_uid" => Array (		
		"exclude" => 1,
		"label" => "LLL:EXT:rss2_import/locallang_db.xml:tt_news.uid",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",
		)
	),
	"tx_rss2import_edited" => Array (		
		"exclude" => 1,	
		"label" => "LLL:EXT:rss2_import/locallang_db.xml:tt_news.edited",
		"config" => Array (
			"type" => "check",
			"default" => "0"
		)
	),
);

t3lib_div::loadTCA("tt_news");
t3lib_extMgm::addTCAcolumns("tt_news",$tempColumns,1);
#t3lib_extMgm::addToAllTCAtypes("tt_news","tx_rss2import_uid;;;;1-1-1, tx_rss2import_edited");

// initalize "context sensitive help" (csh)
t3lib_extMgm::addLLrefForTCAdescr('tx_rss2import_feeds','EXT:rss2_import/locallang_csh_feeds.xml');
?>