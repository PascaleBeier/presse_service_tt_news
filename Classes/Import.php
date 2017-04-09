<?php

Namespace RuhrConnect\Rss2Import;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Scheduler\Task;

/**
 * Class Import
 * @package RuhrConnect\Rss2Import
 */
class Import extends Task
{
    /** @var Helper */
    protected $helper;

    /**
     * Import constructor.
     */
    public function __construct()
    {
        // Dependency Injection
        $this->helper = GeneralUtility::makeInstance(Helper::class);

        parent::__construct();
    }

    /** @inheritdoc */
    public function execute()
    {
		$feeds = $this->helper->getFeeds();

        $uids = [];
		foreach($feeds as $feed) {
			$uids[] = $feed['uid'];
		}

		$this->helper->importFeeds($uids, true);

		return true;
	}
	
}
