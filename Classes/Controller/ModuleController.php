<?php
/**
 * RSS Import Backend Module
 */

namespace RuhrConnect\Rss2Import\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Backend\Module\BaseScriptClass;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Template\DocumentTemplate;
use RuhrConnect\Rss2Import\Helper;

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

        $this->page_for_feeds = intval($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rss2_import']['page_for_feeds']);
        $this->image_max_width = intval($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rss2_import']['image_max_width']);
        $this->image_max_height = intval($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rss2_import']['image_max_height']);

        self::$extensionPath = ExtensionManagementUtility::extRelPath('rss2_import');


        $this->languageService->includeLLFile('EXT:rss2_import/mod1/locallang.xml');

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
        global $BACK_PATH;

        $this->documentTemplate->backPath = $BACK_PATH;
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
                    $content = $this->documentTemplate->table_TABLE.
                        $this->documentTemplate->table_TR.
                        '<td>' . $this->languageService->getLL("feeds.title") . '</td>' .
                        '<td>' . $this->languageService->getLL("feeds.errors_count") . '</td>' .
                        '<td>' . $this->languageService->getLL("feeds.errors") . '</td>' .
                        '<td>' . $this->languageService->getLL("feeds.status") . '</td>' .
                        '</tr>';

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
        $content =
            '<div id="t3-generated-1">
                <table class="t3-table" width="100%">
                    <thead>
                        <tr class="t3-row-header">
                            <th class="t3-col-header t3-cell">
                                <div class="t3-cell-inner">
                                ' . $this->languageService->getLL("feeds.edit") . '
                                </div>
                            </th>
                            <th class="t3-col-header">
                                <div class="t3-cell-inner">
                                ' . $this->languageService->getLL("feeds.title") . '
                                </div>
                            </th>
                            <th class="t3-col-header">
                                <div class="t3-cell-inner">
                                ' . $this->languageService->getLL("feeds.url") . '
                                </div>
                            </th>
                            <th class="t3-col-header">
                                <div class="t3-cell-inner">
                                ' . $this->languageService->getLL("feeds.update") . '
                                </div>
                            </th>
                        </tr>
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

            $editLink = $this->documentTemplate->t3Button($editLinkOnClick, $this->languageService->getLL('editrecord'));

            $content .=
                '<tr class="t3-row">' .
                '<td class="t3-cell"><div class="t3-cell-inner">' . $editLink . '</div></td>' .
                '<td class="t3-cell"><div class="t3-cell-inner">' . $feed['title'] . '</div></td>' .
                '<td class="t3-cell"><div class="t3-cell-inner">' . $feed['url'] . '</div></td>' .
                '<td class="t3-cell"><div class="t3-cell-inner"><input class="t3-form-checkbox t3-form-field" type="checkbox" name="import[]" value="' . $feed['uid'] . '" /></div></td>' .
                '</tr>';

        }

        $newLinkOnClick = htmlspecialchars(
            BackendUtility::editOnClick(
                '&edit[tx_rss2import_feeds][' . $this->page_for_feeds . ']=new',
                $this->documentTemplate->backPath
            )
        );

        $newLink = $this->documentTemplate->t3Button($newLinkOnClick, $this->languageService->getLL('newrecord'));

        $content .=
            '<tr><td align="center">' . ($this->page_for_feeds ? $newLink : '') . '</td><td colspan="2"></td><td align="center"><a title="' . $this->languageService->getLL('select_all_label') . '"><input type="checkbox" name="checkall" onclick="checkUncheckAll(this);" /></a></td></tr>' .
            '</tbody></table>';

        $content .= $this->documentTemplate->t3Button('this.form.submit()', $this->languageService->getLL('submit_label'));

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

    public function route()
    {
        // Make instance of Script Object Back-End (SOBE):
        $GLOBALS['SOBE'] = GeneralUtility::makeInstance(ModuleController::class);
        $GLOBALS['SOBE']->init();
        $GLOBALS['SOBE']->checkExtObj();
        $GLOBALS['SOBE']->main();
        $GLOBALS['SOBE']->printContent();
    }
}