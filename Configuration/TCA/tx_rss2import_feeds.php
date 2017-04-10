<?php

return [
    "ctrl"      => [
        "title"          => "LLL:EXT:rss2_import/Resources/Private/Language/locallang_db.xml:tx_rss2import_feeds",
        "label"          => "title",
        "tstamp"         => "tstamp",
        "crdate"         => "crdate",
        "cruser_id"      => "cruser_id",
        "default_sortby" => "ORDER BY title",
        "delete"         => "deleted",
        "enablecolumns"  => [
            "disabled"  => "hidden",
            "starttime" => "starttime",
            "endtime"   => "endtime",
        ],
        "iconfile"       => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('rss2_import', 'Resources/Public/Images/icon_tx_rss2import_feeds.gif'),
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden,starttime,endtime,title,url,errors,errors_count,target'
    ],
    'columns'   => [
        'hidden'              => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
            'config'  => [
                'type'    => 'check',
                'default' => '0'
            ]
        ],
        'starttime'           => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
            'config'  => [
                'type'     => 'input',
                'size'     => '8',
                'max'      => '20',
                'eval'     => 'date',
                'default'  => '0',
                'checkbox' => '0'
            ]
        ],
        'endtime'             => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/locallang_general.php:LGL.endtime',
            'config'  => [
                'type'     => 'input',
                'size'     => '8',
                'max'      => '20',
                'eval'     => 'date',
                'checkbox' => '0',
                'default'  => '0',
                'range'    => [
                    'upper' => mktime(0, 0, 0, 12, 31, 2020),
                    'lower' => mktime(0, 0, 0, date('m') - 1, date('d'), date('Y'))
                ]
            ]
        ],
        'title'               => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:rss2_import/Resources/Private/Language/locallang_db.xml:tx_rss2import_feeds.title',
            'config'  => [
                'type' => 'input',
                'size' => '45',
                'eval' => 'required',
            ]
        ],
        'url'                 => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:rss2_import/Resources/Private/Language/locallang_db.xml:tx_rss2import_feeds.url',
            'config'  => [
                'type'     => 'input',
                'size'     => '45',
                'max'      => '255',
                'checkbox' => '',
                'eval'     => 'trim',
                'wizards'  => [
                    '_PADDING' => 2,
                    'link'     => [
                        'type'         => 'popup',
                        'title'        => 'Link',
                        'icon'         => 'link_popup.gif',
                        'module'       => [
                            'name'          => 'wizard_element_browser',
                            'urlParameters' => [
                                'mode' => 'wizard',
                            ]
                        ],
                        'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
                    ]
                ]
            ]
        ],
        'errors'              => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:rss2_import/Resources/Private/Language/locallang_db.xml:tx_rss2import_feeds.errors',
            'config'  => [
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
            ]
        ],
        'errors_count'        => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:rss2_import/Resources/Private/Language/locallang_db.xml:tx_rss2import_feeds.errors_count',
            'config'  => [
                'type'     => 'input',
                'size'     => '4',
                'max'      => '4',
                'eval'     => 'int',
                'checkbox' => '0',
                'range'    => [
                    'upper' => '1000',
                    'lower' => '10'
                ],
                'default'  => 0
            ]
        ],
        'target'              => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:rss2_import/Resources/Private/Language/locallang_db.xml:tx_rss2import_feeds.target',
            'config'  => [
                'type'          => 'group',
                'internal_type' => 'db',
                'allowed'       => 'pages',
                'size'          => 1,
                'minitems'      => 0,
                'maxitems'      => 1,
                'wizards'       => [
                    'suggest' => [
                        'type' => 'suggest',
                    ],
                ],
            ]
        ],
        'override_edited'     => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:rss2_import/Resources/Private/Language/locallang_db.xml:tx_rss2import_feeds.override_edited',
            'config'  => [
                'type'    => 'check',
                'default' => '0'
            ]
        ],
        'import_images'       => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:rss2_import/Resources/Private/Language/locallang_db.xml:tx_rss2import_feeds.import_images',
            'config'  => [
                'type'    => 'check',
                'default' => '1'
            ]
        ],
        'auto_update_gabriel' => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:rss2_import/Resources/Private/Language/locallang_db.xml:tx_rss2import_feeds.auto_update_gabriel',
            'config'  => [
                'type'    => 'check',
                'default' => '0'
            ]
        ],
        'guid_prefix'         => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:rss2_import/Resources/Private/Language/locallang_db.xml:tx_rss2import_feeds.guid_prefix',
            'config'  => [
                'type' => 'input',
                'size' => '40',
            ]
        ],
        'typoscript_config'   => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:rss2_import/Resources/Private/Language/locallang_db.xml:tx_rss2import_feeds.typoscript_config',
            'config'  => [
                'type' => 'text',
                'cols' => '55',
                'rows' => '15',
            ]
        ],
        'default_hidden'      => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:rss2_import/Resources/Private/Language/locallang_db.xml:tx_rss2import_feeds.default_hidden',
            'config'  => [
                'type'    => 'check',
                'default' => '0'
            ]
        ],
        'default_type'        => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:rss2_import/Resources/Private/Language/locallang_db.xml:tx_rss2import_feeds.default_type',
            'config'  => [
                'type'       => 'select',
                'renderType' => 'selectSingleBox',
                'items'      => [
                    ['LLL:EXT:tt_news/Resources/Private/Language/locallang_tca.php:tt_news.type.I.0', 0],
                    ['LLL:EXT:tt_news/Resources/Private/Language/locallang_tca.php:tt_news.type.I.2', 2],
                ],
                'default'    => 0
            ]
        ],
        'default_categories'  => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:rss2_import/Resources/Private/Language/locallang_db.xml:tx_rss2import_feeds.default_category',
            'config'  => [
                'type'          => 'select',
                'renderType'    => 'selectMultipleSideBySide',
                'form_type'     => 'user',
                'userFunc'      => 'tx_ttnews_TCAform_selectTree->renderCategoryFields',
                'treeView'      => 1,
                'foreign_table' => 'tt_news_cat',
                'size'          => 3,
                'autoSizeMax'   => 25,
                'minitems'      => 0,
                'maxitems'      => 500,
            ]
        ],
        'default_author'      => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:rss2_import/Resources/Private/Language/locallang_db.xml:tx_rss2import_feeds.default_author',
            'config'  => [
                'type' => 'input',
                'size' => '28'
            ]
        ],
        'default_authoremail' => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:rss2_import/Resources/Private/Language/locallang_db.xml:tx_rss2import_feeds.default_authoremail',
            'config'  => [
                'type' => 'input',
                'size' => '15'
            ]
        ],
    ],
    'types'     => [
        '0' => [
            'showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, url;;;;3-3-3, ' ./*errors, errors_count, */
                          'target, override_edited, import_images, default_hidden, guid_prefix;;;;4-4-4, default_type, default_categories, --palette--;Standard forfatteroplysninger;2;;4-4-4, typoscript_config'
        ]
    ],
    'palettes'  => [
        '1' => ['showitem' => 'starttime, endtime'],
        '2' => [
            'showitem'       => 'default_author, default_authoremail',
            'canNotCollapse' => 1
        ]
    ]
];
