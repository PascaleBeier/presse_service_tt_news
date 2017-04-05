<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Kasper Ligaard <kasperl@cs.au.dk>
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
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

/**
 * This file adds an 'UPDATE' entry in the Extension Manager for the rss2_import
 * extension. The button is visible if the access() method below returns true.
 * 
 * ext_update class used to schedule events to gabriel ext
 *
 * @author	Kasper Ligaard <kasperl@cs.au.dk>
 * @version	0.1 - 1. Oct., 2009
 */

class ext_update  {

 	/**
	 * Called by Typo3 when a user clicks the 'UPDATE' entry in the drop-down box
	 * for this extension in the Extension Manager. Schedules this extensinInstalls gabriel events
	 *
	 * @return	string	HTML message
	 * @access	public
	 *
	 * @author	Kasper Ligaard
	 * @version 0.1 - 1. Oct., 2009
	 */	
    public function main()	{
    	// Make sure we have the class
		require_once(t3lib_extMgm::extPath('rss2_import', 'mod1/class.tx_rss2import_notification.php'));
		
		// Create an instance of our event (which is derived from tx_gabriel_event).
		$notification = t3lib_div::makeInstance('tx_rss2import_notification');
		
		// Register a execution every 10th minute (10*60 seconds), starting now and lasting 5 years 
		$notification->registerRecurringExecution(strtotime('now'), (10*60), strtotime('+5 years'));
		//$notification->registerSingleExecution(strtotime('december 24 2005'));
        
        $gabriel = t3lib_div::getUserObj('EXT:gabriel/class.tx_gabriel.php:&tx_gabriel');
        $gabriel->addEvent($notification, 'tx_rss2import');

        return '<b>Gabriel event has been initiated for RSS2 Import<b/>';
    } //end of main()

 	/**
	 * Checks if any gabriel entries do already exists and returns false then to prevent double inserts
	 *
	 * @return	boolean		false if any record exists in 'tx_gabriel' table
	 * @access	public
	 *
	 * @author	Kasper Ligaard
	 * @version 0.1 - 1. Oct., 2009
	 */	
    public function access()	{
		$gabrielQuery = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'count(*) as count',
			'tx_gabriel',
			'crid="tx_rss2import"'
		);
		$gabrielRecord = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($gabrielQuery);

		$returnValue = true;
		if ($gabrielRecord['count'] > 0) {
			$returnValue = false;
		}

		return $returnValue;
    } //end of access()

}

?>