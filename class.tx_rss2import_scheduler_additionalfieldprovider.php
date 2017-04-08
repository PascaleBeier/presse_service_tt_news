<?php
/**
 * Provide Additional Fields for the Scheduler
 */

use TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Class tx_rss2import_scheduler_additionalfieldprovider
 */
class tx_rss2import_scheduler_additionalfieldprovider implements AdditionalFieldProviderInterface
{
    /** @var DatabaseConnection */
    protected $databaseConnection;

    /** @var LanguageService */
    protected $languageService;

    /**
     * tx_rss2import_scheduler_additionalfieldprovider constructor.
     */
    public function __construct()
    {
        // Dependency Injection
        $this->databaseConnection = GeneralUtility::makeInstance(DatabaseConnection::class);
        $this->languageService = GeneralUtility::makeInstance(LanguageService::class);
    }

    /** @inheritdoc */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $parentObject)
    {
        // Initialize extra field value
        if (empty($taskInfo['feed'])) {
            if ($parentObject->CMD == 'add') {
                // In case of new task and if field is empty, set default sleep time
                $taskInfo['feed'] = 0;
            } else if ($parentObject->CMD == 'edit') {
                // In case of edit, set to internal value if no data was submitted already
                $taskInfo['feed'] = $task->feed;
            } else {
                // Otherwise set an empty value, as it will not be used anyway
                $taskInfo['feed'] = '';
            }
        }

        // Write the code for the field
        $fieldID = 'task_feed';
        $res = $this->databaseConnection->exec_SELECTquery('*',
            'tx_rss2import_feeds',
            '1=1 AND deleted = 0'
        );

        $options = '';

        if ($res) {
            while ($row = $this->databaseConnection->sql_fetch_assoc($res)) {
                $options .= "<option ";
                if ($row['uid'] == $taskInfo['feed']) {
                    $options .= ' selected ';
                }
                $options .= ' value="' . $row['uid'] . '">' . $row['title'] . '</option>';

            }
        } else {
            // db problem
        }


        $additionalFields = [];
        if (empty($options)) {
            $parentObject->addMessage(
                $this->languageService->sL('LLL:EXT:rss2_import/locallang_db.xml:tx_rss2import_scheduler.norecord'),
                FlashMessage::ERROR
            );
        } else {
            $fieldCode = '<select name="tx_scheduler[feed]" size="1" id="' . $fieldID . '" >' . $options . '</select>';
            $additionalFields[$fieldID] = [
                'code' => $fieldCode,
                'label' => 'LLL:EXT:rss2_import/locallang_db.xml:tx_rss2import_scheduler.record',
                'cshKey' => '_MOD_tools_txschedulerM1',
                'cshLabel' => $fieldID
            ];
        }

        return $additionalFields;
    }

    /** @inheritdoc */
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $parentObject)
    {
        $submittedData['feed'] = trim($submittedData['feed']);

        $emptyFeed = empty($submittedData['feed']);

        if ($emptyFeed) {
            $parentObject->addMessage(
                $this->languageService->sL('LLL:EXT:rss2_import/locallang.xml:tx_rss2import_scheduler.norecord'),
                FlashMessage::ERROR
            );
        }

        return $emptyFeed;
    }

    /** @inheritdoc */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        $task->feed = $submittedData['feed'];
    }
}

global $TYPO3_CONF_VARS;

if (defined('TYPO3_MODE') &&
    $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rss2_import/class.tx_rss2import_scheduler_additionalfieldprovider.php']) {
    include_once(
        $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rss2_import/class.tx_rss2import_scheduler_additionalfieldprovider.php']
    );
}
