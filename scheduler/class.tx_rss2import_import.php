<?php

global $_EXTKEY;

require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('rss2_import').'mod1/class.tx_rss2import_helper.php';
require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('rss2_import').'class.tx_rss2import_rssparser.php';

class tx_rss2import_import extends \TYPO3\CMS\Extbase\Scheduler\Task {
	
	public function execute() {
		
		$helper = new tx_rss2import_helper();

		$feeds = $helper->getFeeds();
        $uids = [];
		foreach($feeds as $feed) {
			$uids[] = $feed['uid'];
		}

		$res = $helper->importFeeds($uids,true);

		return true;
	}
	
}
