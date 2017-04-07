<?php
/*********************************************
 *Date: July 11, 2006
 *Author: Mads Kirkedal Henriksen
 *
 *Description: Notification class for gabriel execution. Automatically updates feeds where allowed.
 *********************************************/
require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('gabriel').'class.tx_gabriel_event.php');
require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'mod1/class.tx_rss2import_helper.php');
class tx_rss2import_notification extends tx_gabriel_event {
	function tx_rss2import_notification() {
		$this->__construct();
	}

	/**
	 * Method called by EXT:Gabriel.
	 *
	 * @return	mixed		Typically a string with a plaintext report on the result.
	 */
	function execute() {
		global $LANG;
		if($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rss2_import']['enable_gabriel']) {
			$helper = new tx_rss2import_helper();
			$uidsToImport = $helper->getAutoUpdateUids();
			if (count($uidsToImport) === 0) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('No feeds to autoupdate... Method: '.__METHOD__, $_EXTKEY);
			} else {
				$result = $helper->importFeeds($uidsToImport, true);
				// Insert below line for debug
				// t3lib_div::devLog('RSS2 Import processed ' . count($uidsToImport) . ' feeds', 'rss2_import', 1, array('File' => __LINE__, 'Line' => __LINE__, 'Result' => $result));
				return $result;
			}
		} else {
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('NOT starting tx_rss2import_notification! gabriel support disabled in Extension Manager.', "rss2_import", 1);
		}
	}
}

if (defined("TYPO3_MODE") && isset($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/rss2_import/mod1/class.tx_rss2import_notification.php"])) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/rss2_import/mod1/class.tx_rss2import_notification.php"]);
}
?>
