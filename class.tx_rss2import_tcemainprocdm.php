<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2005 Morten Tranberg Hansen (mth@daimi.au.dk)
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
/**
 * Use hook to observe if updates are hapening to a tt_news content element. When that happens,
 * check if the content element is changed in any of the fields set in the Extension Manager 
 * for RSS2 Import.
 *
 * @author	Morten Tranberg Hansen <mth@cs.au.dk>
 * @author	Kasper Ligaard <kasperl@cs.au.dk>
 */

class tx_rss2import_tcemainprocdm {
	public function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, &$reference) {
		// First 'listen' if it is RSS2 Import itself that are doing the update.
		 if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rss2_import']['import_in_progress'])) {
		 	// t3lib_div::devLog('RSS2 Import is doing the import: Do not set edited flag', 'rss2_import', 1);
		 	return;
		 }
		
		// If update of tt_news record, mark it as edited, unless RSS2 Import is not the one doing the update.
		if($table === 'tt_news' && $status === 'update') {
			$oldData = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecord('tt_news', $id);

			// Get list of fields to check for modification. These are set in the Extension Manager.
			$compare = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rss2_import']['edited_fields']);

			foreach($compare as $field) {
				if (isset($fieldArray[$field]) && $fieldArray[$field] !== $oldData[$field]) {
					// Build datamap
					$data = array();
					$data[$table][$id]['tx_rss2import_edited'] = 1;

					// The next few lines are described in Typo3 Core API, section "Using t3lib_TCEmain in scripts".
					$tce = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_TCEmain');
					$tce->stripslashes_values = 0;
					$tce->start($data, array());
					$tce->process_datamap();
					break;
				}
			}
		}
	}
}

if (defined("TYPO3_MODE") && isset($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/rss2_import/class.tx_rss2import_tcemainprocdm.php"])) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/rss2_import/class.tx_rss2import_tcemainprocdm.php"]);
}
