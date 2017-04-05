<?php
require_once t3lib_extMgm::extPath('rss2_import').'mod1/class.tx_rss2import_helper.php';
require_once t3lib_extMgm::extPath('rss2_import').'class.tx_rss2import_rssparser.php';

class tx_rss2import_import extends tx_scheduler_Task {
	
	public function execute() {
		
		$helper = new tx_rss2import_helper();		
	
		$feeds = $helper->getFeeds();
		foreach($feeds as $feed) {
			$uids[] = $feed['uid'];
		}

		$res = $helper->importFeeds($uids,true);

		return true;
	}
	
}
?>