<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 stefan busemann (info@in2code.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('rss2_import') . 'mod1/class.tx_rss2import_helper.php';

/**
 * Class "tx_rss2import_scheduler" provides (..?)
 *
 * @author		Stefan Busemann (in2code.de)
 * @package		TYPO3
 * @subpackage	rss2_import
 *
 */
class tx_rss2import_scheduler extends \TYPO3\CMS\Extbase\Scheduler\Task {

	/**
	 * uid of the feed Record
	 *
	 * @var	integer		$feed
	 */
	public $feed = 0;
	
	/**
	 * Function executed from the Scheduler.
	 *
	 * @return bool
	 */
	public function execute() {
		$helper = new tx_rss2import_helper();
		$result = $helper->importFeeds(array($this->feed), TRUE);

		return strpos($result, 'Zero rows error: 0') && strpos($result, 'Several rows error: 0');
	}

	/**
	 * This method returns additional information metadata about the feed setting.
	 *
	 * @return	string	Information to display
	 */
	public function getAdditionalInformation() {
		return $GLOBALS['LANG']->sL('LLL:EXT:rss2_import/locallang_db.xml:tx_rss2import_scheduler.record1') . ': ' . $this->feed;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rss2_import/class.tx_rss2import_scheduler.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/scheduler/rss2_import/class.tx_rss2import_scheduler.php']);
}
