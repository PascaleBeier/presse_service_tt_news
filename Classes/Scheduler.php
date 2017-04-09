<?php

namespace RuhrConnect\Rss2Import;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Scheduler\Task;

/**
 * Class Scheduler
 * @package RuhrConnect\Rss2Import
 */
class Scheduler extends Task
{
    /** @var int uid of the feed Record */
	public $feed = 0;

	/** @var Helper */
	protected $helper;

    /**
     * Scheduler constructor.
     */
	public function __construct()
    {
        // Dependency Injection
        $this->helper = GeneralUtility::makeInstance(Helper::class);

        parent::__construct();
    }

    /**
	 * Function executed from the Scheduler.
	 *
	 * @return bool
	 */
	public function execute()
    {
		$result = $this->helper->importFeeds([$this->feed], true);

		return strpos($result, 'Zero rows error: 0') && strpos($result, 'Several rows error: 0');
	}

	/**
	 * This method returns additional information metadata about the feed setting.
	 *
	 * @return string Information to display
	 */
	public function getAdditionalInformation() {
		return $GLOBALS['LANG']->sL('LLL:EXT:rss2_import/locallang_db.xml:tx_rss2import_scheduler.record1') . ': ' . $this->feed;
	}
}