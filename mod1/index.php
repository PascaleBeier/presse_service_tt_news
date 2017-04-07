<?php
/**
 * RSS Import Backend Module
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Backend\Module\BaseScriptClass;

global $_EXTKEY, $MCONF;

// Include Language Files
$languageService = GeneralUtility::makeInstance(LanguageService::class);
$languageService->includeLLFile('EXT:' . $_EXTKEY . '/mod1/locallang.xml');

// Check for Backend User Permissions
$backendUserAuthentication = GeneralUtility::makeInstance(BackendUserAuthentication::class);
$backendUserAuthentication->modAccess($MCONF, 1);

// Include Classes.
// @todo probably obsolete
require_once ExtensionManagementUtility::extPath($_EXTKEY) . 'mod1/class.tx_rss2import_helper.php';
require_once ExtensionManagementUtility::extPath($_EXTKEY) . 'class.tx_rss2import_rssparser.php';


class tx_rss2import_module1 extends BaseScriptClass
{
    /**
     * @var string the relative path to this extension
     */
    protected static $extensionPath;
    private $pageinfo;
    private $page_for_feeds;
    private $image_max_width;
    private $image_max_height;

    /** @var \TYPO3\CMS\Lang\LanguageService */
    protected $languageService;
    /** @var BackendUserAuthentication */
    protected $backendUserAuthentication;
    /** @var tx_rss2import_helper */
    protected $tx_rss2import_helper;

    public function __construct(LanguageService $languageService, BackendUserAuthentication $backendUserAuthentication,
        tx_rss2import_helper $tx_rss2import_helper)
    {
        $this->languageService = $languageService;
        $this->backendUserAuthentication = $backendUserAuthentication;
        $this->tx_rss2import_helper = $tx_rss2import_helper;

        global $_EXTKEY;

        parent::init();

        $this->page_for_feeds = intval($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['page_for_feeds']);
        $this->image_max_width = intval($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['image_max_width']);
        $this->image_max_height = intval($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['image_max_height']);

        self::$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY);
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
        global $BE_USER, $BACK_PATH;

        if (true) {

            // Draw the header.
            $this->doc = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("bigDoc");
            $this->doc->backPath = $BACK_PATH;
            $this->doc->form = '<form action="" method="POST">';

            // JavaScript
            $this->doc->JScode =
                '<script>
                    script_ended = 0;
                    function jumpToUrl(URL)	{
                        document.location = URL;
                    }
    
                    function checkUncheckAll(theElement) {
                          var theForm = theElement.form, z = 0;
                          for(i=0;i<theForm.length;i++) {
                            if(theForm[i].type == \'checkbox\' && theForm[i].name != \'checkall\') {
                              theForm[i].checked = theElement.checked;
                            }
                          }
                        }
                </script>';
            $this->doc->JScode .= '<link rel="stylesheet" type="text/css" href="' .
                self::$extensionPath . 'mod1/rss2import-be.css" />';

            $this->doc->postCode =
                '<script>
                    script_ended = 1;
                    if (top.fsMod) top.fsMod.recentIds["web"] = ' . intval($this->id) . ';
                </script>';

            $headerSection = $this->doc->getHeader(
                "pages",
                $this->pageinfo,
                $this->pageinfo["_thePath"]) . "<br>" . $this->languageService->sL("LLL:EXT:lang/locallang_core.php:labels.path") . ": " . \TYPO3\CMS\Core\Utility\GeneralUtility::fixed_lgd_cs($this->pageinfo["_thePath"],
                50
            );

            $this->content .= $this->doc->startPage($this->languageService->getLL("title"));
            $this->content .= $this->doc->header($this->languageService->getLL("title"));
            $this->content .= $this->doc->spacer(5);
            $this->content .= $this->doc->section("", $this->doc->funcMenu($headerSection,
                \TYPO3\CMS\Backend\Utility\BackendUtility::getFuncMenu($this->id, "SET[function]", $this->MOD_SETTINGS["function"],
                    $this->MOD_MENU["function"])));
            $this->content .= $this->doc->divider(5);

            // Render content:
            $this->moduleContent();

            // ShortCut
            if ($this->backendUserAuthentication->mayMakeShortcut()) {
                $this->content .= $this->doc->spacer(20) . $this->doc->section("",
                        $this->doc->makeShortcutIcon("id", implode(",", array_keys($this->MOD_MENU)),
                            $this->MCONF["name"]));
            }

            $this->content .= $this->doc->spacer(10);
        } else {
            // If no access or if ID == zero

            $this->doc = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("mediumDoc");
            $this->doc->backPath = $BACK_PATH;

            $this->content .= $this->doc->startPage($this->languageService->getLL("title"));
            $this->content .= $this->doc->header($this->languageService->getLL("title"));
            $this->content .= $this->doc->spacer(5);
            $this->content .= $this->doc->spacer(10);
        }
    }

    /**
     * Generates the module content
     *
     * @return    [type]        ...
     */
    public function moduleContent()
    {
        switch ((string)$this->MOD_SETTINGS["function"]) {
            case 1:

                $feedsToImport = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('import');

                if (empty($feedsToImport)) {
                    $content = $this->getAvailableFeeds();
                } else {
                    if (!is_array($feedsToImport)) {
                        $feedsToImport = array($feedsToImport);
                    }
                    $content = '<table>' .
                        '<tr>' .
                        '<td class="bgColor2">' . $this->languageService->getLL("feeds.title") . '</td>' .
                        '<td class="bgColor2">' . $this->languageService->getLL("feeds.errors_count") . '</td>' .
                        '<td class="bgColor2">' . $this->languageService->getLL("feeds.errors") . '</td>' .
                        '<td class="bgColor2">' . $this->languageService->getLL("feeds.status") . '</td>' .
                        '</tr>';

                    $content .= $this->tx_rss2import_helper->importFeeds($feedsToImport, 0, $this->doc);
                    $content .= $this->doc->t3Button('this.form.submit()', $this->languageService->getLL('back_label'));
                }

                $this->content .= $this->doc->section($GLOBALS['LANG']->getLL("function1") . ':', $content, 0, 1);
                break;
        }
    }

    /**
     * List Feeds
     *
     * @return    [type]        ...
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


        $feeds = $this->tx_rss2import_helper->getFeeds();
        foreach ($feeds as $feed) {

            $editIcon = '<img' . \TYPO3\CMS\Backend\Utility\IconUtility::skinImg($this->doc->backPath, 'gfx/edit2.gif',
                    '') . ' title="' . $GLOBALS['LANG']->getLL('editrecord') . '" border="0" alt="" style="vertical-align:middle;" />';
            $editLink = '<a style="text-decoration: none;" href="#" onclick="' . htmlspecialchars(\TYPO3\CMS\Backend\Utility\BackendUtility::editOnClick('&edit[tx_rss2import_feeds][' . $feed['uid'] . ']=edit',
                    $this->doc->backPath)) . '">' . $editIcon . '</a>';

            $content .=
                '<tr class="t3-row">' .
                '<td class="t3-cell"><div class="t3-cell-inner">' . $editLink . '</div></td>' .
                '<td class="t3-cell"><div class="t3-cell-inner">' . $feed['title'] . '</div></td>' .
                '<td class="t3-cell"><div class="t3-cell-inner">' . $feed['url'] . '</div></td>' .
                '<td class="t3-cell"><div class="t3-cell-inner"><input class="t3-form-checkbox t3-form-field" type="checkbox" name="import[]" value="' . $feed['uid'] . '" /></div></td>' .
                '</tr>';

        }
        $newIcon = '<img' . \TYPO3\CMS\Backend\Utility\IconUtility::skinImg($this->doc->backPath, 'gfx/new_el.gif',
                '') . ' title="' . $GLOBALS['LANG']->getLL('newrecord') . '" border="0" alt="" style="vertical-align:middle;" />';
        $newLink = '<a style="text-decoration: none;" href="#" onclick="' . htmlspecialchars(\TYPO3\CMS\Backend\Utility\BackendUtility::editOnClick('&edit[tx_rss2import_feeds][' . $this->page_for_feeds . ']=new',
                $this->doc->backPath)) . '">' . $newIcon . '</a>';

        $content .=
            '<tr><td align="center">' . ($this->page_for_feeds ? $newLink : '') . '</td><td colspan="2"></td><td align="center"><a title="' . $this->languageService->getLL('select_all_label') . '"><input type="checkbox" name="checkall" onclick="checkUncheckAll(this);" /></a></td></tr>' .
            '</tbody></table>';

        $content .=
            $this->doc->t3Button('this.form.submit()', $this->languageService->getLL('submit_label'));

        return $content;
    }

    /**
     * Prints out the module HTML
     *
     * @return    string        The contents
     */
    public function printContent()
    {

        $this->content .= $this->doc->endPage();
        echo $this->content;
    }
}

if (defined("TYPO3_MODE") && isset($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/rss2_import/mod1/index.php"])) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/rss2_import/mod1/index.php"]);
}


// Make instance of Script Object Back-End (SOBE):
$SOBE = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("tx_rss2import_module1");

// Include files?
foreach ($SOBE->include_once as $INC_FILE) {
    include_once($INC_FILE);
}

$SOBE->main();
$SOBE->printContent();
