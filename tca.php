<?php

defined('TYPO3_MODE') || die ('Access denied.');

global $TCA;

$TCA['tx_rss2import_feeds'] = Array(
    'ctrl' => $TCA['tx_rss2import_feeds']['ctrl'],
    'interface' => Array(
        'showRecordFieldList' => 'hidden,starttime,endtime,title,url,errors,errors_count,target'
    ),
    'feInterface' => $TCA['tx_rss2import_feeds']['feInterface'],
    'columns' => Array(
        'hidden' => Array(
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
            'config' => Array(
                'type' => 'check',
                'default' => '0'
            )
        ),
        'starttime' => Array(
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
            'config' => Array(
                'type' => 'input',
                'size' => '8',
                'max' => '20',
                'eval' => 'date',
                'default' => '0',
                'checkbox' => '0'
            )
        ),
        'endtime' => Array(
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.php:LGL.endtime',
            'config' => Array(
                'type' => 'input',
                'size' => '8',
                'max' => '20',
                'eval' => 'date',
                'checkbox' => '0',
                'default' => '0',
                'range' => Array(
                    'upper' => mktime(0, 0, 0, 12, 31, 2020),
                    'lower' => mktime(0, 0, 0, date('m') - 1, date('d'), date('Y'))
                )
            )
        ),
        'title' => Array(
            'exclude' => 1,
            'label' => 'LLL:EXT:rss2_import/locallang_db.xml:tx_rss2import_feeds.title',
            'config' => Array(
                'type' => 'input',
                'size' => '45',
                'eval' => 'required',
            )
        ),
        'url' => Array(
            'exclude' => 1,
            'label' => 'LLL:EXT:rss2_import/locallang_db.xml:tx_rss2import_feeds.url',
            'config' => Array(
                'type' => 'input',
                'size' => '45',
                'max' => '255',
                'checkbox' => '',
                'eval' => 'trim',
                'wizards' => Array(
                    '_PADDING' => 2,
                    'link' => Array(
                        'type' => 'popup',
                        'title' => 'Link',
                        'icon' => 'link_popup.gif',
                        'script' => 'browse_links.php?mode=wizard',
                        'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
                    )
                )
            )
        ),
        'errors' => Array(
            'exclude' => 1,
            'label' => 'LLL:EXT:rss2_import/locallang_db.xml:tx_rss2import_feeds.errors',
            'config' => Array(
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
            )
        ),
        'errors_count' => Array(
            'exclude' => 1,
            'label' => 'LLL:EXT:rss2_import/locallang_db.xml:tx_rss2import_feeds.errors_count',
            'config' => Array(
                'type' => 'input',
                'size' => '4',
                'max' => '4',
                'eval' => 'int',
                'checkbox' => '0',
                'range' => Array(
                    'upper' => '1000',
                    'lower' => '10'
                ),
                'default' => 0
            )
        ),
        'target' => Array(
            'exclude' => 1,
            'label' => 'LLL:EXT:rss2_import/locallang_db.xml:tx_rss2import_feeds.target',
            'config' => Array(
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
                'wizards' => array(
                    'suggest' => array(
                        'type' => 'suggest',
                    ),
                ),
            )
        ),
        'override_edited' => Array(
            'exclude' => 1,
            'label' => 'LLL:EXT:rss2_import/locallang_db.xml:tx_rss2import_feeds.override_edited',
            'config' => Array(
                'type' => 'check',
                'default' => '0'
            )
        ),
        'import_images' => Array(
            'exclude' => 1,
            'label' => 'LLL:EXT:rss2_import/locallang_db.xml:tx_rss2import_feeds.import_images',
            'config' => Array(
                'type' => 'check',
                'default' => '1'
            )
        ),
        'auto_update_gabriel' => Array(
            'exclude' => 1,
            'label' => 'LLL:EXT:rss2_import/locallang_db.xml:tx_rss2import_feeds.auto_update_gabriel',
            'config' => Array(
                'type' => 'check',
                'default' => '0'
            )
        ),
        'guid_prefix' => Array(
            'exclude' => 1,
            'label' => 'LLL:EXT:rss2_import/locallang_db.xml:tx_rss2import_feeds.guid_prefix',
            'config' => Array(
                'type' => 'input',
                'size' => '40',
            )
        ),
        'typoscript_config' => Array(
            'exclude' => 1,
            'label' => 'LLL:EXT:rss2_import/locallang_db.xml:tx_rss2import_feeds.typoscript_config',
            'config' => Array(
                'type' => 'text',
                'cols' => '55',
                'rows' => '15',
            )
        ),
        'default_hidden' => Array(
            'exclude' => 1,
            'label' => 'LLL:EXT:rss2_import/locallang_db.xml:tx_rss2import_feeds.default_hidden',
            'config' => Array(
                'type' => 'check',
                'default' => '0'
            )
        ),
        'default_type' => Array(
            'exclude' => 1,
            'label' => 'LLL:EXT:rss2_import/locallang_db.xml:tx_rss2import_feeds.default_type',
            'config' => Array(
                'type' => 'select',
                'items' => Array(
                    Array('LLL:EXT:tt_news/locallang_tca.php:tt_news.type.I.0', 0),
                    Array('LLL:EXT:tt_news/locallang_tca.php:tt_news.type.I.2', 2),
                    Array('LLL:EXT:au_tsconfig/locallang.xml:tt_news.type.event', 4)
                ),
                'default' => 0
            )
        ),
        'default_categories' => Array(
            'exclude' => 1,
            // 'l10n_mode' => 'exclude', // the localizalion mode will be handled by the userfunction
            'label' => 'LLL:EXT:rss2_import/locallang_db.xml:tx_rss2import_feeds.default_category',
            'config' => Array(
                'type' => 'select',
                'form_type' => 'user',
                // 'userFunc' => 'tx_ttnews_treeview->displayCategoryTree', // Function in tt_news less than 3.x
                'userFunc' => 'tx_ttnews_TCAform_selectTree->renderCategoryFields', // Function in tt_news from 3.x and onwards
                'treeView' => 1,
                'foreign_table' => 'tt_news_cat',
                // 'foreign_table_where' => $fTableWhere.'ORDER BY tt_news_cat.'.$confArr['category_OrderBy'],
                'size' => 3,
                'autoSizeMax' => 25,
                'minitems' => 0,
                'maxitems' => 500,
                // 'MM' => 'tt_news_cat_mm',
            )
        ),
        'default_author' => Array(
            'exclude' => 1,
            'label' => 'LLL:EXT:rss2_import/locallang_db.xml:tx_rss2import_feeds.default_author',
            'config' => Array(
                'type' => 'input',
                'size' => '28'
            )
        ),
        'default_authoremail' => Array(
            'exclude' => 1,
            'label' => 'LLL:EXT:rss2_import/locallang_db.xml:tx_rss2import_feeds.default_authoremail',
            'config' => Array(
                'type' => 'input',
                'size' => '15'
            )
        ),
    ),
    'types' => Array(
        '0' => Array('showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, url;;;;3-3-3, ' ./*errors, errors_count, */
            'target, override_edited, import_images, ' . (($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rss2_import']['enable_gabriel'] && \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('gabriel')) ? 'auto_update_gabriel, ' : '') . 'default_hidden, guid_prefix;;;;4-4-4, default_type, default_categories, --palette--;Standard forfatteroplysninger;2;;4-4-4, typoscript_config')
    ),
    'palettes' => Array(
        '1' => Array('showitem' => 'starttime, endtime'),
        '2' => Array(
            'showitem' => 'default_author, default_authoremail',
            'canNotCollapse' => 1
        )
    )
);
