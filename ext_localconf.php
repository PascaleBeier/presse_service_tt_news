<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3_MODE') || die('Access denied.');

// Registers hook in TCEMain
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] =
    'EXT:rss2_import/class.tx_rss2import_tcemainprocdm.php:tx_rss2import_tcemainprocdm';

// Initialize vars from extension conf
$_EXTCONF = unserialize($_EXTCONF);
$initVars = [
    'page_for_feeds',
    'edited_fields',
    'image_max_width',
    'image_max_height',
    'enable_gabriel',
    'typoscript_config',
    'formatters'
];

foreach ($initVars as $var) {
    if (isset($_EXTCONF[$var])) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rss2_import'][$var] = trim($_EXTCONF[$var]);
    }
}

// Check if Scheduler is available - If so, include our Scheduler Task
if (ExtensionManagementUtility::isLoaded('scheduler')) {

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_rss2import_scheduler'] = [
        'extension'        => 'rss2_import',
        'title'            => 'RSS2 feed importer',
        'description'      => 'Automates the Import of the RSS2 Feeds',
        'additionalFields' => 'tx_rss2import_scheduler_additionalfieldprovider'
    ];
}
