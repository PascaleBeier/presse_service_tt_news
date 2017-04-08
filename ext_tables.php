<?php

defined("TYPO3_MODE") || die ("Access denied.");

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (TYPO3_MODE === "BE") {
    ExtensionManagementUtility::addModule(
        "tools",
        "txrss2importM1",
        "",
        ExtensionManagementUtility::extPath('rss2_import') . "mod1/");
}

$TCA["tx_rss2import_feeds"] = [
    "ctrl"        => [
        "title"             => "LLL:EXT:rss2_import/locallang_db.xml:tx_rss2import_feeds",
        "label"             => "title",
        "tstamp"            => "tstamp",
        "crdate"            => "crdate",
        "cruser_id"         => "cruser_id",
        "default_sortby"    => "ORDER BY title",
        "delete"            => "deleted",
        "enablecolumns"     => [
            "disabled"  => "hidden",
            "starttime" => "starttime",
            "endtime"   => "endtime",
        ],
        "dynamicConfigFile" => ExtensionManagementUtility::extPath('rss2_import') . "tca.php",
        "iconfile"          => ExtensionManagementUtility::extRelPath('rss2_import') . "icon_tx_rss2import_feeds.gif",
    ],
    "feInterface" => [
        "fe_admin_fieldList" => "hidden, starttime, endtime, title, url, errors, errors_count, target",
    ]
];

$tempColumns = [
    "tx_rss2import_uid"    => [
        "exclude" => 1,
        "label"   => "LLL:EXT:rss2_import/locallang_db.xml:tt_news.uid",
        "config"  => [
            "type" => "input",
            "size" => "30",
        ]
    ],
    "tx_rss2import_edited" => [
        "exclude" => 1,
        "label"   => "LLL:EXT:rss2_import/locallang_db.xml:tt_news.edited",
        "config"  => [
            "type"    => "check",
            "default" => "0"
        ]
    ]
];

ExtensionManagementUtility::addTCAcolumns("tt_news", $tempColumns, 1);
ExtensionManagementUtility::addLLrefForTCAdescr(
    'tx_rss2import_feeds',
    'EXT:rss2_import/locallang_csh_feeds.xml'
);
