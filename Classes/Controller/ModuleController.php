<?php
/**
 * RSS Import Backend Module
 */

namespace RuhrConnect\Rss2Import\Controller;

use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Backend\Module\BaseScriptClass;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Template\DocumentTemplate;
use RuhrConnect\Rss2Import\Helper;
use TYPO3\CMS\Core\Imaging\IconFactory;

/**
 * Class ModuleController
 * @package RuhrConnect\Rss2Import
 */
class ModuleController extends BaseScriptClass
{
    /**
     * @var string the relative path to this extension
     */
    protected static $extensionPath;
    private $pageinfo;
    private $page_for_feeds;
    private $image_max_width;
    private $image_max_height;

    /** @var LanguageService */
    protected $languageService;
    /** @var BackendUserAuthentication */
    protected $backendUserAuthentication;
    /** @var Helper */
    protected $helper;
    /** @var DocumentTemplate */
    protected $documentTemplate;
    /** @var IconFactory */
    protected $iconFactory;

    /**
     * Module constructor.
     */
    public function __construct()
    {
        // Dependency Injection
        $this->languageService = GeneralUtility::makeInstance(LanguageService::class);
        $this->backendUserAuthentication = GeneralUtility::makeInstance(BackendUserAuthentication::class);
        $this->helper = GeneralUtility::makeInstance(Helper::class);
        $this->documentTemplate = GeneralUtility::makeInstance(DocumentTemplate::class);
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);

        $this->page_for_feeds = intval($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rss2_import']['page_for_feeds']);
        $this->image_max_width = intval($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rss2_import']['image_max_width']);
        $this->image_max_height = intval($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rss2_import']['image_max_height']);

        self::$extensionPath = ExtensionManagementUtility::extRelPath('rss2_import');


        $this->languageService->includeLLFile('EXT:rss2_import/Resources/Private/Language/locallang.xml');

    }

    /**
     * Adds items to the ->MOD_MENU array. Used for the function menu selector.
     *
     * @return void
     */
    public function menuConfig()
    {
        $this->MOD_MENU = Array(
            "function" => Array(
                "1" => $this->languageService->getLL("function1"),
            )
        );

        parent::menuConfig();
    }

    /**
     * Main function of the module. Write the content to $this->content
     *
     * @return void
     */
    public function main()
    {
        $this->documentTemplate->backPath = $GLOBALS['BACK_PATH'];
        $this->documentTemplate->bodyTagAdditions = 'class="module-body"';
        $this->documentTemplate->form = '<form action="" method="POST">';

        // JavaScript
        $this->documentTemplate->JScode =


            "<script>
                script_ended = 0;
                function jumpToUrl(URL)	{
                    document.location = URL;
                }

                function checkUncheckAll(theElement) {
                      var theForm = theElement.form, z = 0;
                      for(var i = 0; i < theForm.length; i++) {
                        if(theForm[i].type === 'checkbox' && theForm[i].name !== 'checkall') {
                          theForm[i].checked = theElement.checked;
                        }
                      }
                    }
            </script>";

        $recentId = intval($this->id);

        $this->documentTemplate->postCode =
            "<script>
                script_ended = 1;
                if (top.fsMod) top.fsMod.recentIds['web'] = $recentId;
            </script>";

        $headerSection = $this->documentTemplate->getHeader(
            "pages",
            $this->pageinfo,
            $this->pageinfo["_thePath"]) . "<br>" . $this->languageService->sL("LLL:EXT:lang/locallang_core.php:labels.path") . ": " . \TYPO3\CMS\Core\Utility\GeneralUtility::fixed_lgd_cs($this->pageinfo["_thePath"],
            50
        );

        $this->content .= $this->documentTemplate->startPage($this->languageService->getLL("title"));
        $this->content .= $this->documentTemplate->header($this->languageService->getLL("title"));
        $this->content .= $this->documentTemplate->spacer(5);

        $this->content .= $this->documentTemplate->section(
            "",
            $this->documentTemplate->funcMenu(
                $headerSection,
                BackendUtility::getFuncMenu(
                    $this->id, "SET[function]",
                    $this->MOD_SETTINGS["function"],
                    $this->MOD_MENU["function"]
                )
            )
        );
        $this->content .= $this->documentTemplate->divider(5);

        // Render content:
        $this->moduleContent();

        // ShortCut
        if ($this->backendUserAuthentication->mayMakeShortcut()) {
            $this->content .= $this->documentTemplate->spacer(20) . $this->documentTemplate->section("",
                    $this->documentTemplate->makeShortcutIcon("id", implode(",", array_keys($this->MOD_MENU)),
                        $this->MCONF["name"]));
        }

        $this->content .= $this->documentTemplate->spacer(10);
        $this->content .= $this->documentTemplate->endPage();
    }

    /**
     * Generates the module content.
     */
    public function moduleContent()
    {
        switch ((string)$this->MOD_SETTINGS["function"]) {
            case 1:

                $feedsToImport = GeneralUtility::_POST('import');

                if (empty($feedsToImport)) {
                    $content = $this->getAvailableFeeds();
                } else {
                    if (!is_array($feedsToImport)) {
                        $feedsToImport = array($feedsToImport);
                    }
                    $content = '<table class="table table-striped table-hover">'.
                        '<thead>'.
                        '<th>' . $this->languageService->getLL('feeds.title') . '</th>' .
                        '<th>' . $this->languageService->getLL('feeds.errors_count') . '</th>' .
                        '<th>' . $this->languageService->getLL('feeds.errors') . '</th>' .
                        '<th>' . $this->languageService->getLL('feeds.status') . '</th>' .
                        '</thead>';

                    $content .= $this->helper->importFeeds($feedsToImport, 0, $this->documentTemplate);
                    $content .= $this->documentTemplate->t3Button('this.form.submit()', $this->languageService->getLL('back_label'));
                }

                $this->content .= $this->documentTemplate->section($this->languageService->getLL("function1") . ':', $content, 0, 1);
                break;
        }
    }

    /**
     * List available Feeds.
     *
     * @return string
     */
    private function getAvailableFeeds()
    {
        $content = '<table class="table table-striped table-hover">
                        <thead>
                            <tr>
                            <th>
                                ' . $this->languageService->getLL('feeds.title') . '
                            </th>
                            <th>
                                ' . $this->languageService->getLL('feeds.url') . '
                            </th>
                            <th>
                                ' . $this->languageService->getLL('feeds.update') . '
                            </th>
                            <th>
                                ' . $this->languageService->getLL('feeds.edit') . '
                            </th>
                            </tr>
                        </th>
                    </thead>
                <tbody>';

        $feeds = $this->helper->getFeeds();
        foreach ($feeds as $feed) {

            $editLinkOnClick = htmlspecialchars(
                BackendUtility::editOnClick(
                    '&edit[tx_rss2import_feeds][' . $feed['uid'] . ']=edit',
                    $this->documentTemplate->backPath
                )
            );

            $editLink = '<a href="#" class="btn btn-default" onclick="'.$editLinkOnClick.'"
            title="'.$this->languageService->getLL('editrecord').'">
            '.$this->iconFactory->getIcon('actions-document-open', ICON::SIZE_SMALL)->render().'
            </a>';

            $content .=
                '<tr>' .
                '<td>' . $feed['title'] . '</td>' .
                '<td>' . $feed['url'] . '</td>' .
                '<td><input type="checkbox" name="import[]" value="' . $feed['uid'] . '" /></td>' .
                '<td>'.$editLink.'</td>'.
                '</tr>';

        }

        $newLinkOnClick = htmlspecialchars(
            BackendUtility::editOnClick(
                '&edit[tx_rss2import_feeds][' . $this->page_for_feeds . ']=new',
                $this->documentTemplate->backPath
            )
        );

        $newLink = '<a href="#" class="btn btn-default" onclick="'.$newLinkOnClick.'"> 
            '.$this->iconFactory->getIcon('actions-document-new', Icon::SIZE_SMALL)->render().'
            </a>';

        $content .= '</tbody></table>';
        $content .= '<div class="btn-group">';
        $content .= $newLink;
        $content .= '<button class="btn btn-default" onclick="this.form.submit()" 
        title="'.$this->languageService->getLL('submit_label').'">
        '.$this->iconFactory->getIcon('actions-system-refresh', Icon::SIZE_SMALL)->render().'
        </button>';
        $content .= '</div>';

        return $content;
    }

    /**
     * Print Module Html
     *
     * @return void
     */
    public function printContent()
    {
        echo $this->content;
    }
}
