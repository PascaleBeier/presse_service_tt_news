<?php

defined("TYPO3_MODE") || die ("Access denied.");

$composerAutoloadFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('rss2_import')
                        . 'vendor/autoload.php';

require_once($composerAutoloadFile);

if (TYPO3_MODE === "BE") {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
        'tools',
        'txrss2importM1',
        '',
        TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('rss2_import').'mod1/',
        [
            'access' => 'user,group',
            'script' => '_DISPATCH',
            'name' => 'tools_txrss2importM1',
            'vendorName' => 'RuhrConnect\Rss2Import',
            'labels' => [
                'tabs_images' => [
                    'tab' => 'EXT:rss2_import/mod1/moduleicon.gif'
                ],
                'll_ref' => 'LLL:EXT:rss2_import/mod1/locallang_mod.xml'
            ]
        ]
    );
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
        "dynamicConfigFile" => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('rss2_import') . "tca.php",
        "iconfile"          => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('rss2_import') . "icon_tx_rss2import_feeds.gif",
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

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns("tt_news", $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tx_rss2import_feeds',
    'EXT:rss2_import/locallang_csh_feeds.xml'
);
