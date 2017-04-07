<?php

defined("TYPO3_MODE") || die ("Access denied.");

if (TYPO3_MODE === "BE") {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
        "tools",
        "txrss2importM1",
        "",
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . "mod1/");
}

$TCA["tx_rss2import_feeds"] = [
    "ctrl" => [
        "title" => "LLL:EXT:'.$_EXTKEY.'/locallang_db.xml:tx_rss2import_feeds",
        "label" => "title",
        "tstamp" => "tstamp",
        "crdate" => "crdate",
        "cruser_id" => "cruser_id",
        "default_sortby" => "ORDER BY title",
        "delete" => "deleted",
        "enablecolumns" => [
            "disabled" => "hidden",
            "starttime" => "starttime",
            "endtime" => "endtime",
        ],
        "dynamicConfigFile" => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . "tca.php",
        "iconfile" => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . "icon_tx_rss2import_feeds.gif",
    ],
    "feInterface" => [
        "fe_admin_fieldList" => "hidden, starttime, endtime, title, url, errors, errors_count, target",
    ]
];

$tempColumns = [
    "tx_rss2import_uid" => [
        "exclude" => 1,
        "label" => "LLL:EXT:'.$_EXTKEY.'/locallang_db.xml:tt_news.uid",
        "config" => [
            "type" => "input",
            "size" => "30",
        ]
    ],
    "tx_rss2import_edited" => [
        "exclude" => 1,
        "label" => "LLL:EXT:'.$_EXTKEY.'/locallang_db.xml:tt_news.edited",
        "config" => [
            "type" => "check",
            "default" => "0"
        ]
    ]
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns("tt_news", $tempColumns, 1);
#t3lib_extMgm::addToAllTCAtypes("tt_news","tx_rss2import_uid;;;;1-1-1, tx_rss2import_edited");

// initalize "context sensitive help" (csh)
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tx_rss2import_feeds',
    'EXT:' . $_EXTKEY . '/locallang_csh_feeds.xml'
);
