<?php defined('TYPO3_MODE') || die('Access denied.');

// Registers hook in TCEMain
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:rss2_import/class.tx_rss2import_tcemainprocdm.php:tx_rss2import_tcemainprocdm';

/** Initialize vars from extension conf */
$_EXTCONF = unserialize($_EXTCONF);    // unserializing the configuration so we can use it here:
$initVars = array('page_for_feeds','edited_fields', 'image_max_width', 'image_max_height', 'enable_gabriel', 'typoscript_config', 'formatters');
foreach($initVars as $var) {
  $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY][$var] = isset($_EXTCONF[$var]) ? trim($_EXTCONF[$var]) : '';
}

//Check if gabriel needs to be aware of us
if(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('gabriel')) {
  $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['gabriel']['include'][$_EXTKEY] = 'mod1/class.tx_rss2import_notification.php';
}

//Check if scheduler is available
if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('scheduler')) {
	/*
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_rss2import_import'] = array(
		'extension'        => $_EXTKEY,
		'title'            => 'LLL:EXT:' . $_EXTKEY.'/locallang_db.xml:tx_rss2import_feeds.scheduler.name',
		'description'      => 'LLL:EXT:' . $_EXTKEY.'/locallang_db.xml:tx_rss2import_feeds.scheduler.description',
		'additionalFields' => ''
	);
	*/

	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_rss2import_scheduler'] = array(
		'extension'        => 'rss2_import',
		'title'            => 'RSS2 feed importer',
		'description'      => 'Automates the Import of the RSS2 Feeds',
		'additionalFields' => 'tx_rss2import_scheduler_additionalfieldprovider'
);
}

?>