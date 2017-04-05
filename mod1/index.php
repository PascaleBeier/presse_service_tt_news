<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2004 Morten Tranberg Hansen (mth@daimi.au.dk)
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
 * Module 'RSS Import' for the 'rss2_import' extension.
 *
 * @author	Morten Tranberg Hansen <mth@daimi.au.dk>
 */

$LANG->includeLLFile('EXT:rss2_import/mod1/locallang.xml');
require_once PATH_t3lib . 'class.t3lib_scbase.php';
$BE_USER->modAccess($MCONF, 1);	// This checks permissions and exits if the users has no permission for entry.

require_once t3lib_extMgm::extPath('rss2_import').'mod1/class.tx_rss2import_helper.php';
require_once t3lib_extMgm::extPath('rss2_import').'class.tx_rss2import_rssparser.php';

class tx_rss2import_module1 extends t3lib_SCbase {
	private $pageinfo;
	private $page_for_feeds;
	private $image_max_width;
	private $image_max_height;

	private $helper;

	/**
	 * @var string the extension key
	 */
	const EXTENSION_KEY = 'rss2_import';
	/**
	 * @var string the relative path to this extension
	 */
	protected static $extensionPath;


	public function __construct() {
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		parent::init();

		$this->page_for_feeds = intval($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rss2_import']['page_for_feeds']);
		$this->image_max_width = intval($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rss2_import']['image_max_width']);
		$this->image_max_height = intval($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rss2_import']['image_max_height']);

		self::$extensionPath = t3lib_extMgm::extRelPath(self::EXTENSION_KEY);

		//Create instance of the helper (the class with the actual magic)
		$this->helper = new tx_rss2import_helper();
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	[type]		...
	 */
	public function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			     "function" => Array (
						  "1" => $LANG->getLL("function1"),
		)
		);
		parent::menuConfig();
	}

	// If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	/**
	 * Main function of the module. Write the content to $this->content
	 *
	 * @return	[type]		...
	 */
	public function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access) || ($BE_USER->user["admin"] && !$this->id))	{

			// Draw the header.
			$this->doc = t3lib_div::makeInstance("bigDoc");
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="" method="POST">';

			// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$theElement: ...
	 * @return	[type]		...
	 */
 	                                function checkUncheckAll(theElement) {
                                          var theForm = theElement.form, z = 0;
                                          for(i=0;i<theForm.length;i++) {
                                            if(theForm[i].type == \'checkbox\' && theForm[i].name != \'checkall\') {
                                              theForm[i].checked = theElement.checked;
                                            }
                                          }
                                        }
				</script>
			';
			$this->doc->JScode .= '<link rel="stylesheet" type="text/css" href="'.self::$extensionPath.'mod1/rss2import-be.css" />';
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
				</script>
			';

			$headerSection = $this->doc->getHeader("pages",$this->pageinfo,$this->pageinfo["_thePath"])."<br>".$LANG->sL("LLL:EXT:lang/locallang_core.php:labels.path").": ".t3lib_div::fixed_lgd_pre($this->pageinfo["_thePath"],50);

			$this->content.=$this->doc->startPage($LANG->getLL("title"));
			$this->content.=$this->doc->header($LANG->getLL("title"));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section("",$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,"SET[function]",$this->MOD_SETTINGS["function"],$this->MOD_MENU["function"])));
			$this->content.=$this->doc->divider(5);


			// Render content:
			$this->moduleContent();


			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section("",$this->doc->makeShortcutIcon("id",implode(",",array_keys($this->MOD_MENU)),$this->MCONF["name"]));
			}

			$this->content.=$this->doc->spacer(10);
		} else {
			// If no access or if ID == zero

			$this->doc = t3lib_div::makeInstance("mediumDoc");
			$this->doc->backPath = $BACK_PATH;

			$this->content.=$this->doc->startPage($LANG->getLL("title"));
			$this->content.=$this->doc->header($LANG->getLL("title"));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	string		The contents
	 */
	public function printContent()	{

		$this->content .= $this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 *
	 * @return	[type]		...
	 */
	private function moduleContent()	{
		global $LANG;
		switch((string)$this->MOD_SETTINGS["function"])	{
			case 1:

				$feedsToImport = t3lib_div::_POST('import');

				if(empty( $feedsToImport )) {
					$content = $this->getAvailableFeeds();
				} else {
					if(!is_array($feedsToImport)) $feedsToImport = array($feedsToImport);
					$content = '<table>'.
                   '<tr>'.
                   '<td class="bgColor2">'.$LANG->getLL("feeds.title").'</td>'.
                   '<td class="bgColor2">'.$LANG->getLL("feeds.errors_count").'</td>'.
                   '<td class="bgColor2">'.$LANG->getLL("feeds.errors").'</td>'.
                   '<td class="bgColor2">'.$LANG->getLL("feeds.status").'</td>'.
                   '</tr>';

					$content .= $this->helper->importFeeds($feedsToImport,0,$this->doc);
					$content .=$this->doc->t3Button('this.form.submit()',$LANG->getLL('back_label'));
				}

				$this->content.=$this->doc->section($GLOBALS['LANG']->getLL("function1").':',$content,0,1);
				break;
		}
	}


	/**
	 * List Feeds
	 *
	 * @return	[type]		...
	 */
	private function getAvailableFeeds() {
		global $LANG;

		$content =
      '<table>'.
      '<tr>'.
      '<td class="bgColor2">'.$LANG->getLL("feeds.edit").'</td>'.
      '<td class="bgColor2">'.$LANG->getLL("feeds.title").'</td>'.
      '<td class="bgColor2">'.$LANG->getLL("feeds.url").'</td>'.
      '<td class="bgColor2">'.$LANG->getLL("feeds.update").'</td>'.
      '</tr>';

		$feeds = $this->helper->getFeeds();
		foreach($feeds as $feed) {

			$editIcon = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','').' title="'.$GLOBALS['LANG']->getLL ('editrecord').'" border="0" alt="" style="vertical-align:middle;" />';
			$editLink = '<a style="text-decoration: none;" href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick('&edit[tx_rss2import_feeds]['.$feed['uid'].']=edit',$this->doc->backPath)).'">'.$editIcon.'</a>';

			$content .=
	'<tr>'.
	'<td align="center">'.$editLink.'</td>'.
	'<td>'.$feed['title'].'</td>'.
	'<td>'.$feed['url'].'</td>'.
	'<td align="center"> <input type="checkbox" name="import[]" value="'.$feed['uid'].'" /></td>'.
	'</tr>';

		}
		$newIcon = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/new_el.gif','').' title="'.$GLOBALS['LANG']->getLL ('newrecord').'" border="0" alt="" style="vertical-align:middle;" />';
		$newLink = '<a style="text-decoration: none;" href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick('&edit[tx_rss2import_feeds]['.$this->page_for_feeds.']=new',$this->doc->backPath)).'">'.$newIcon.'</a>';

		$content .=
      '<tr><td align="center">'.($this->page_for_feeds ? $newLink : '').'</td><td colspan="2"></td><td align="center"><a title="'.$LANG->getLL('select_all_label').'"><input type="checkbox" name="checkall" onclick="checkUncheckAll(this);" /></a></td></tr>'.
      '</table>';

		$content .=
		$this->doc->t3Button('this.form.submit()',$LANG->getLL('submit_label'));

		return $content;
	}
}

if (defined("TYPO3_MODE") && isset($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/rss2_import/mod1/index.php"])) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/rss2_import/mod1/index.php"]);
}


// Make instance of Script Object Back-End (SOBE):
$SOBE = t3lib_div::makeInstance("tx_rss2import_module1");

// Include files?
foreach($SOBE->include_once as $INC_FILE) {
	include_once($INC_FILE);
}

$SOBE->main();
$SOBE->printContent();

?>