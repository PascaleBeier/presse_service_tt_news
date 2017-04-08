<?php
/**
 * Register necessary class names with autoloader
 */

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3_MODE') || die('Access denied.');

$extensionPath = ExtensionManagementUtility::extPath('rss2_import');
return [
	'tx_rss2import_import' => $extensionPath . 'scheduler/class.tx_rss2import_import.php',
	'tx_rss2import_scheduler' => $extensionPath . 'class.tx_rss2import_scheduler.php',
	'tx_rss2import_scheduler_additionalfieldprovider'	=> $extensionPath . 'class.tx_rss2import_scheduler_additionalfieldprovider.php',	
];