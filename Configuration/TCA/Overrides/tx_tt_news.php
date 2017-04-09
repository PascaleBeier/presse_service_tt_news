<?php

$tempColumns = [
    'tx_rss2import_uid'    => [
        'exclude' => 1,
        'label'   => 'LLL:EXT:rss2_import/Resources/Private/Language/locallang_db.xml:tt_news.uid',
        'config'  => [
            'type' => 'input',
            'size' => '30',
        ]
    ],
    'tx_rss2import_edited' => [
        'exclude' => 1,
        'label'   => 'LLL:EXT:rss2_import/Resources/Private/Language/locallang_db.xml:tt_news.edited',
        'config'  => [
            'type'    => 'check',
            'default' => '0'
        ]
    ]
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_news', $tempColumns, 1);