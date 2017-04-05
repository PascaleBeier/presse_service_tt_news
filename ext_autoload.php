<?php
/*
 * Register necessary class names with autoloader
 *
 */
// TODO: document necessity of providing autoloader information
$extensionPath = t3lib_extMgm::extPath('rss2_import');
return array(
	'tx_rss2import_import' => $extensionPath . 'scheduler/class.tx_rss2import_import.php',
	'tx_rss2import_scheduler' => $extensionPath . 'class.tx_rss2import_scheduler.php',
	'tx_rss2import_scheduler_additionalfieldprovider'	=> $extensionPath . 'class.tx_rss2import_scheduler_additionalfieldprovider.php',	
);

?>