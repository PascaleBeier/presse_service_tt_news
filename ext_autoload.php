<?php
/*
 * Register necessary class names with autoloader
 *
 */

$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY);
return [
	'tx_rss2import_import' => $extensionPath . 'scheduler/class.tx_rss2import_import.php',
	'tx_rss2import_scheduler' => $extensionPath . 'class.tx_rss2import_scheduler.php',
	'tx_rss2import_scheduler_additionalfieldprovider'	=> $extensionPath . 'class.tx_rss2import_scheduler_additionalfieldprovider.php',	
];