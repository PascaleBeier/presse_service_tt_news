<?php
/**
 * Use hook to observe if updates are hapening to a tt_news content element.
 * When that happens, check if the content element is changed in any of
 * the fields set in the Extension Manager for RSS2 Import.
 */

namespace RuhrConnect\Rss2Import;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Class TCE
 * @package RuhrConnect\Rss2Import
 */
class TCE
{
    /** @var DataHandler */
    protected $dataHandler;

    /**
     * TCE constructor.
     */
    public function __construct()
    {
        // Dependency Injection
        $this->dataHandler = GeneralUtility::makeInstance(DataHandler::class);
    }

    /**
     * @param $status
     * @param $table
     * @param $id
     * @param $fieldArray
     * @param $reference
     */
    public function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, &$reference)
    {
		if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rss2_import']['import_in_progress'])) {
		 	return;
		 }
		
		// If update of tt_news record, mark it as edited, unless RSS2 Import is not the one doing the update.
		if($table === 'tt_news' && $status === 'update') {
			$oldData = BackendUtility::getRecord('tt_news', $id);

			// Get list of fields to check for modification. These are set in the Extension Manager.
			$compare = GeneralUtility::trimExplode(
			    ',',
                $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rss2_import']['edited_fields']
            );

			foreach($compare as $field) {
				if (isset($fieldArray[$field]) && $fieldArray[$field] !== $oldData[$field]) {
					// Build datamap
					$data = [];
					$data[$table][$id]['tx_rss2import_edited'] = 1;

					// The next few lines are described in Typo3 Core API, section "Using t3lib_TCEmain in scripts".
					$tce = GeneralUtility::makeInstance('t3lib_TCEmain');
					$tce->stripslashes_values = 0;
					$tce->start($data, array());
					$tce->process_datamap();
					break;
				}
			}
		}
	}
}