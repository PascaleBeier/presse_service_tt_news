<?php

/******************************************************************************************************************
 *Date: July 11, 2006
 *Author: Mads Kirkedal Henriksen
 *
 *Description: A helper class containing the basic functionality of the RSS2 importer.
 ******************************************************************************************************************/

require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'class.tx_rss2import_rssparser.php';
require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('core') . 'Classes/TypoScript/Parser/TypoScriptParser.php';

if (!is_object($GLOBALS['LANG'])) {
	require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('lang').'lang.php';
	$GLOBALS['LANG'] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('language');
	$GLOBALS['LANG']->init('default');
}
$GLOBALS['LANG']->includeLLFile('EXT:'.$_EXTKEY.'/mod1/locallang.xml');

/*
 * Define constants that match the values in 'type' column in the tt_news table schema.
 *
 * The first three constants are constants for the values in default tt_news. The rest
 * are our additions ('event' and 'blog' will definately be used; 'grant' might also be).
 *
 * FIXME: Copy-pasted from EXT:au_news_config.
 */
define('INFO_TYPE_NEWS', 0);
define('INFO_TYPE_ARTICLE', 1);
define('INFO_TYPE_NEWS_EXTERNAL', 2);
define('INFO_TYPE_BLOG', 3);
define('INFO_TYPE_EVENT', 4);
define('INFO_TYPE_EVENT_EXTERNAL', 7);

class tx_rss2import_helper {
	private $maxTitleLength; //Only used for shortening the displayed title when importing, full title will be used when imported.
	private $cObj;

	public function __construct () {
		mb_internal_encoding('UTF-8');
	}

	/**
	 * Main function: Takes an array of feed UIDs to import, and imports them.
	 *
	 * @param	array		$feedsToGet: List of integers that match a uid from the table tx_rss2import_feeds
	 * @param	boolean		$outputPlainText: Output either plaintext or HTML
	 * @return	string		Statistics data of the import (HTML or plaintext)
	 */
	public function importFeeds(array $feedsToGet, $outputPlainText = true, $args = null) {
		global $LANG;
		$content = '';
		$this->cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tslib_cObj');
		$this->maxTitleLength = 40;

		// Load the formatter classes. These are set in the Extension Manager and resides in
		// the directory EXT:rss2_import/formatters/
		$formatters = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rss2_import']['formatters'], true);
		$extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('rss2_import');
		foreach ($formatters as $key => $fileName) {
			$filePath = $extPath . 'formatters' . DIRECTORY_SEPARATOR . $fileName . '.php';
			if (file_exists($filePath)) {
				require_once $filePath;
			}
		}

		// Create parser instance.
		$feeds = $this->getFeeds($feedsToGet);
		foreach($feeds as $feed) {
			$rs = new tx_rss2import_RSSParser(); //create a new instance
			if (mb_substr(mb_strtolower($feed['url']), 0, 7) !=='http://') {
				$feed['url']='http://'.$feed['url'];
			}
			$rs->parse($feed['url']);

			$rss = array();
			$rss['errors'] = $rs->get_errors();
			// If error, write to Typo3s Log-module
			if (!empty($rss['errors'])) {
				$GLOBALS['BE_USER']->simplelog('There were import problems with the feed titled: ' . $feed['title'] . '. Errors reported from parser was: ' . join('. ', $rss['errors']), 'rss2_import', 1);
			}
			$rss['channel'] = $rs->get_channel();
			$rss['items'] = $rs->get_items();
			$rss['image'] = $rs->get_image();
			$rss['textinput'] = $rs->get_textinput();
			$feed['title'] = trim($feed['title']);

			if ($outputPlainText) {
				$content .= "\n".mb_substr($feed['title'],0,$this->maxTitleLength-3).(mb_strlen($feed['title'])>$this->maxTitleLength-3?'...':'').': '.$feed['url'].' ('.($rss['errors'] ? count($rss['errors']) : 0). " errors)\n";
			} else {
				$content .=
				'<tr>'.
				'<td class="bgColor5"><a href="http://'.$feed['url'].'" target="_blank">'.mb_substr($feed['title'],0,$this->maxTitleLength).(mb_strlen($feed['title'])>$this->maxTitleLength?'...':'').'</a></td>'.
				'<td class="bgColor5" align="center">'.($rss['errors'] ? count($rss['errors']) : 0).'</td>'.
				'<td class="bgColor5">'.(is_array($rss['errors']) ? implode('',$rss['errors']) : (!empty($rss['errors']) ? $rss['errors'] : '')).'</td>'.
				'<td class="bgColor5"></td>'.
				'</tr>';
			}

			if (empty($rss['errors'])) { // NO ERRORS OCCURED
				// Signal to anyone 'listening' that this insert/update is done by RSS2 Import
				$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rss2_import']['import_in_progress'] = true;

				//Parse extra TS mappings from feed to tt_news element, and check for missing fields.
				$typoscript = $this->parseFeedTSConfig($feed);

				$extraMappings = array();
				if (isset($typoscript['extraMappings.'])) {
					$extraMappings = $typoscript['extraMappings.'];
				}

				$missingFields = '';
				if(is_array($extraMappings)) {
					$fields = $GLOBALS['TYPO3_DB']->admin_get_fields('tt_news');
					foreach($extraMappings as $extraMappingNS) {
						if(is_array($extraMappingNS)) {
							foreach($extraMappingNS as $extraMapping) {
								if(is_string($extraMapping)) {
									foreach (\TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $extraMapping, true) as $extraField) {
										if (!isset($fields[$extraField])) {
											$missingFields .= $extraField.', ';
										}
									}
								}
							}
						}
					}
				}

				if (mb_strlen($missingFields)>0) {
					$error = '<p><strong>'.$LANG->getLL('missingfields').' <em>' . $missingFields . '</em><br />'.$LANG->getLL('checkts').' "' . $feed['title'] . '"</strong></p>';
					$content .= $error;
					$extraMappings = Array();
				}

                $statistics = [];
                //Insert or update items.
                if (is_array($rss['items'])) {
					if (count($rss['items']) === 0) {
						\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('The feed titled "' . $feed['title'] . '" returned no items.', 'rss2_import', 1);
					}
					$statistics = array('inserted' => 0, 'updated' => 0, 'zerorowserror' => 0, 'severalrowserror' => 0);
					foreach($rss['items'] as $item) {
						$item['pubdate'] = strtotime($item['pubdate']);
						$item['title'] = trim($item['title']);

						$uid = $this->getUid($item, $feed);
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',
						 'tt_news',
						 "tx_rss2import_uid LIKE '".$uid."' and t3_origuid = 0");
						$num_rows = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
						$record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
						$status = '';
						if ($num_rows === 1) {
							$status = $this->updateItem($item, $record, $feed, $outputPlainText, $extraMappings);
							$statistics['updated']++;
						} else if ($num_rows === 0) {
							$status = $this->insertItem($item, $feed, $outputPlainText, $extraMappings);
							$statistics['inserted']++;
						} else if ($num_rows === false) {
							\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('ERROR: There was an error when trying to fetch the number of rows.', 'RSS Import', 1);
							$status = 'Error while fecthing number of rows.';
							$statistics['zerorowserror']++;
						} else {
							\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('ERROR: More than one record with uid '.$uid.' found in tt_news table.', 'RSS Import', 1);
							$status = 'Error: More than one record with uid ' . $uid . ' found in tt_news table.';
							$statistics['severalrowserror']++;
						}

						if ($outputPlainText) {
							$content .= mb_substr($item['title'], 0, $this->maxTitleLength-3).(mb_strlen($item['title'])>$this->maxTitleLength-3?'...':'');
							$i = mb_strlen($item['title']);
							if ($i > $this->maxTitleLength-3) {
								$i = $this->maxTitleLength;
							}
							for (;$i<$this->maxTitleLength;$i++) {
								$content .= ' ';
							}
							$content .= ': ' . $status . ' ' . trim($item['link'])."\n";
						} else {
							$content .=
					        	'<tr>'.
	        					'<td>'.(trim($item['link'])?'<a href="'.trim($item['link']).'" target="_blank">':'').mb_substr($item['title'],0,$this->maxTitleLength).(mb_strlen($item['title'])>$this->maxTitleLength?'...':'').(trim($item['link'])?'</a>':'').'</td>'.
	        					'<td></td>'.
	        					'<td></td>'.
	        					'<td>'.$status.'</td>'.
	        					'</tr>';
						}
					}
				}
				$content .= 'Inserted: ' . $statistics['inserted'] . '. Updated: ' . $statistics['updated'] .'. Zero rows error: ' . $statistics['zerorowserror'] . '. Several rows error: ' . $statistics['severalrowserror'];
				// Clear errors field on feed record.
				$data = array();
				$data['tx_rss2import_feeds'][$feed['uid']] = array(
						'errors' => '',
						'errors_count' => 0
				);
			} else {
				// SOME ERRORS OCCURED
				$data = array();
				$data['tx_rss2import_feeds'][$feed['uid']] = array(
						'errors' => join("\n", $rss['errors']),
						'errors_count' => count($rss['errors'])
				);
			}
			$this->processDataMap($data);
		}

		if (!$outputPlainText) $content .= '</table>';

		return $content;
	}

	/**
	 * Updates existing news record.
	 *
	 * @param	array		$item 
	 * @param	array		$record
	 * @param	array		$feed: ...
	 * @param	boolean		$outputPlainText: ...
	 * @param	array		$extraMappings
	 * @return	string		Status (either as HTML or plaintext)
	 */
	private function updateItem(array $item, array $record, array $feed, $outputPlainText = false, array $extraMappings = array()) {
		$status = 'N/A';
		//If we may update the element, do it.
		if (!empty($record['tx_rss2import_edited']) && empty($feed['override_edited'])) {
			$status = '<span class="tx-rss2-import-status-not-overridden" title="Override not allowed for this feed, when entry has been modified locally">Modified locally, not overridden.</span>';
			if ($outputPlainText) {
				$status = 'Override not allowed for this feed, when entry has been modified locally';
			}
		} else {
			//Get associated image from enclosure, if any.
			$image='';
			if($feed['import_images']) {
				if(count($item['enclosure'])>1 && is_numeric($item['enclosure']['LENGTH'])) {
					$image = $this->getImage($item['enclosure']['TYPE'], $item['enclosure']['URL'], $item['enclosure']['LENGTH']);
				}
					// Added Support for media tag - 2010-08-07 Stefan Busemann / in2code.de
				if ($item['media']) {
					$image = $this->getImage($this->getFileType($item['media']['thumbnail']['attrs']['URL']), $item['media']['thumbnail']['attrs']['URL'], 0);
				}
			}

			//Build up the basic news item, with extra mappings.
			$fields_values = array(
                             'datetime' => $item['pubdate'],
                             'title' => $item['title'],
                             'image' => $image,
                             'author_email' => $item['author'], // The author in RSS2 is an email address
                             'links' => $item['link'],
                             'bodytext' => $item['description'],
                             'tx_rss2import_edited' => 0,
                             'tx_rss2import_uid' => $this->getUid($item,$feed));

			$fields_values = $this->mapInternalOrExternal($fields_values, $item, $feed['default_type']);

			$fields_values = $this->mergeExtraMappings($fields_values, $item, $extraMappings);

			// TODO: Is this correct? Shouldn't this be handled in Extension setup combined with TypoScript setup on a feed record?
			$this->fixAuthorAndEmail($fields_values, $feed);

			$data = array();
			$data['tt_news'][$record['uid']] = $fields_values;
			$this->processDataMap($data);

			if ($outputPlainText) {
				$status = 'Updated';
			} else {
				$status = '<span class="tx-rss2-import-status-updated">Updated</span>';
			}
		}
		return $status;
	}

	/**
	 * Inserts new news element from feed.
	 *
	 * @param	array		$item
	 * @param	array		$feed: 
	 * @param	boolean		$outputPlainText: ...
	 * @param	array		$extraMappings
	 * @return	string		Status (either as HTML or plaintext)
	 */
	private function insertItem(array $item, array $feed, $outputPlainText = false, array $extraMappings = array()) {
		//Get associated image from enclosure, if any.
		$image='';
		if ($feed['import_images']) {
			if(count($item['enclosure']) > 1 && is_numeric($item['enclosure']['LENGTH'])) {
				$image = $this->getImage($item['enclosure']['TYPE'], $item['enclosure']['URL'], $item['enclosure']['LENGTH']);
			}
			// Added Support for media tag - 2010-08-07 Stefan Busemann / in2code.de
			if ($item['media']) {
				$image = $this->getImage($this->getFileType($item['media']['thumbnail']['attrs']['URL']), $item['media']['thumbnail']['attrs']['URL'], 0);
			}
		}

		//Build up the basic news item, with extra mappings.
		$fields_values = array(
			'hidden' => $feed['default_hidden'],
      		'datetime' => $item['pubdate'],
      		'pid' => $feed['target'],
	      	'type' => $feed['default_type'],
	      	'title' => $item['title'],
	      	'author_email' => $item['author'], // The author in RSS2 is an email address
            'image' => $image,
	      	'category' => $feed['default_categories'],
			'tx_rss2import_uid' => $this->getUid($item, $feed)
		);

		$fields_values = $this->mapInternalOrExternal($fields_values, $item, $feed['default_type']);

		$fields_values = $this->mergeExtraMappings($fields_values, $item, $extraMappings);

		// TODO: Is this correct? Shouldn't this be handled in Extension setup combined with TypoScript setup on a feed record?
		$this->fixAuthorAndEmail($fields_values, $feed);

		$data = array();
		$newsId = uniqid('NEW');
		$data['tt_news'][$newsId] = $fields_values;
		$this->processDataMap($data);

		$status = '<span class="tx-rss2-import-status-imported">Inserted</span>';
		if ($outputPlainText) {
			$status = 'Inserted';
		}
		return $status;
	}

	/*
	 * This will check if author name or email is set. If they are, they will just
	 * be returned; if none are set, the values of default_author and
	 * default_authoremail for the feed will be returned.
	 */
	private function fixAuthorAndEmail (&$fields_values, $feed) {
		if (empty($fields_values['author']) && empty($fields_values['author_email'])) {
			$fields_values['author'] = $feed['default_author'];
			$fields_values['author_email'] = $feed['default_authoremail'];
		}
	}

	/*
	 * NOTE: Using constants from EXT:au_newsevent and EXT:au_news_config.
	 */
	private function mapInternalOrExternal (array $fields_values, array $item, $type) {
		// Insert the element, either internal or external.
		switch(intval($type)) {
			case INFO_TYPE_NEWS:
			case INFO_TYPE_EVENT:
			case INFO_TYPE_BLOG:
				$fields_values['links'] = $item['link'];
				$fields_values['bodytext'] = $item['description'];
				break;
			case INFO_TYPE_NEWS_EXTERNAL:
			case INFO_TYPE_EVENT_EXTERNAL:
				$fields_values['ext_url'] = $item['link'];
				$fields_values['short'] = $item['description'];
				break;
			case INFO_TYPE_ARTICLE:
			default:
				\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('ERROR: Not valid news type','RSS Import', 1, array('Fieldvalues' => $fields_values, 'Item' => $item, 'Type' => $type));
				exit();
				break;
		}
		return $fields_values;
	}

	// There is a special namespace "xmlns", which is the default namespace in an XML
	// document, cf. http://www.w3.org/TR/REC-xml-names/#NT-DefaultAttName
	private function mergeExtraMappings (array $fields_values, array $item, array $extraMappings) {
		foreach ($extraMappings as $namespace => $extraMappingNS) {
			$namespace = substr($namespace,0,-1); //remove '.'
			if (is_array($extraMappingNS)) {
				foreach($extraMappingNS as $entryName => $fieldName) { // e.g. start-date => tx_aunewsevent_from
					if (is_string($fieldName)) {
						if (isset($item[$namespace][$entryName]['data']) ||
							($namespace === 'xmlns' && isset($item[$entryName]))
							) {
							$realNames = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $fieldName, true);
							foreach ($realNames as $realName) {
								if ($namespace === 'xmlns') {
									$fields_values[$realName] = $item[$entryName];
								} else {
									$fields_values[$realName] = $item[$namespace][$entryName]['data'];
								}
							}
						}
					} else if (is_array($fieldName)) {
						$realNames = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $extraMappingNS[substr($entryName,0,-1)], true);
						foreach ($realNames as $realName) {
							// isevent is a reserved word used to integrate with an events extension like mbl_newsevent.
							if ($entryName === 'isevent.') {
								// We add it to the fields values (this avoids an E_NOTICE warning from PHP)
								$fields_values[$realName] = '';
							}
							if (isset($fieldName['strtotime']) && ($fieldName['strtotime'])) {
								$fields_values[$realName] = strtotime($fields_values[$realName]);
							}
							$fields_values[$realName] = $this->cObj->stdWrap($fields_values[$realName], $fieldName);
						}
					} else {
						\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Not valid type for fieldName: ' . gettype($fieldName), 'rss2_import', 1, $extraMappingNS);
					}
				}
			}
		}
		return $fields_values;
	}


	/**
	 * Get feed info from database, either all feeds or from an array of uids.
	 *
	 * @param	array		$uids: List of uids matching feed records in table tx_rss2import_feeds
	 * @return	array		List of feed records as associative arrays.
	 */
	public function getFeeds(array $uids = array()) {
		$uidWhere = '';
		foreach ($uids as $uid) {
			$uidWhere .= $uidWhere ? ' OR uid LIKE \''.$uid.'\'' : 'uid LIKE \''.$uid.'\'';
		}
		$uidWhere = $uidWhere ? '('.$uidWhere.')' : '1=1';
		// Get availible feed records.
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tx_rss2import_feeds',
		$uidWhere .
		\TYPO3\CMS\Backend\Utility\BackendUtility::BEenableFields('tx_rss2import_feeds') .
		\TYPO3\CMS\Backend\Utility\BackendUtility::deleteClause('tx_rss2import_feeds')
		);
		$feeds = array();
		while($feed = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$feeds[] = $feed;
		}
		return $feeds;
	}

	/**
	 * Generate a valid RSS2 Import uid.
	 *
	 * @param	array		$item: An item from the RSS2 feed (as an associative array).
	 * @param	array		$feed: A feed record (as an associative array)
	 * @return	string		A uid prefix.
	 */
	private function getUid(array $item, array $feed) {
		$result = '';

		$prefix = !empty($feed['guid_prefix']) ? $feed['guid_prefix'] : $feed['url'];

		if($item['guid']) {                                      //uid from guid
			$result = 'guid:' . $prefix . $item['guid'];
		} else if ($item['link']) {                               //uid from link
			$result = 'link:' . $prefix . $item['link'];
		} else {
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Unable to generate valid uid.','RSS Import', 1, array('item' => $item, 'feed' => $feed));
		}
		return trim($result);
	}

	/**
	 * Get the uids from the feeds that are allowed to automatically update (Used in gabriel)
	 *
	 * @return	array		List of feed records.
	 */
	public function getAutoUpdateUids() {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid',
                                                 'tx_rss2import_feeds',
                                                 'auto_update_gabriel=1' .
		\TYPO3\CMS\Backend\Utility\BackendUtility::BEenableFields('tx_rss2import_feeds') .
		\TYPO3\CMS\Backend\Utility\BackendUtility::deleteClause('tx_rss2import_feeds'));
		$uids = array();
		while($feed = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$uids[] = $feed['uid'];
		}
		return $uids;
	}

	/**
	 * Function to extract info, download and resize images from feed items.
	 *
	 * @param	string		$type: ...
	 * @param	string		$url: ...
	 * @param	integer		$length: ...
	 * @return	string		Filename of the image file.
	 */
	private function getImage($type, $url, $length) {
		$filename='';
		$type = explode('/', mb_strtolower($type));
		//Only continue if one of following file extensions
		if($type[0]=='image' && ($type[1]=='gif' || $type[1]=='jpeg' || $type[1]=='png')) {
			$filename = $url;
			if (mb_substr(mb_strtolower($filename),0,7) === 'http://') {
				$filename = mb_substr($filename,7);
			}
			$filename = str_replace('/','_',$filename).'.'.$type[1];
			$path = PATH_site . $GLOBALS['TCA']['tt_news']['columns']['image']['config']['uploadfolder'].'/';
			//Only get image if one with the same name doesn't exist
			if(!file_exists($path.$filename)) {
				$image = imagecreatefromstring(file_get_contents($url));
				if($image) {
					//Resize image to the maximum configured in ext mgr.
					$width = imagesx($image);
					$height = imagesy($image);
					if(($width>$this->image_max_width || $height>$this->image_max_height) && ($this->image_max_width>0 && $this->image_max_height>0)) {
						if($width > $height) {
							$newwidth = $this->image_max_width;
							$newheight = ($newwidth/$width)*$height;
						} else {
							$newheight = $this->image_max_height;
							$newwidth = ($newheight/$height)*$width;
						}
						$newimage = imagecreatetruecolor($newwidth, $newheight);
						imagecopyresampled($newimage, $image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
						$image = $newimage;
					}

					//Save image
					switch($type[1]) {
						case 'gif':
							imagegif($image, $path.$filename);
							break;
						case 'jpeg':
							imagejpeg($image, $path.$filename);
							break;
						case 'png':
							imagepng($image, $path.$filename);
							break;
						default:
							return '';
					}
				} else return '';
			}
		}
		return $filename;
	}

	/**
	 * Parse the extra TS config (eg. extraMappings).
	 *
	 * @param	array		$feed: RSS2 import feed record
	 * @return	array		The parsed structure
	 */
	private function parseFeedTSConfig(array $feed) {
		$tsString = $feed['typoscript_config'];
		$TSparserObject = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_tsparser');
		$TSparserObject->parse($tsString);
		return $TSparserObject->setup;
	}

	/*
	 * Convenience function
	 *
	 * returns the mapping of 'NEW' to the uid's they ended up having (it does not telle which table it was inserted into: You need to know that).
	 */
	private function processDataMap (array $data) {
		// The next few lines are described in Typo3 Core API, section "Using t3lib_TCEmain in scripts".
		$tce = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_TCEmain');
		$tce->stripslashes_values = 0;
		$tce->dontProcessTransformations = 1;
		$tce->start($data, array());
		$tce->process_datamap();
		return $tce->substNEWwithIDs;
	}

	/*
	 * detect the filetype
	 * @author Stefan Busemann / svogal
	 * @company in2code.de
	 * @return string The filetype
	 */
	private function getFileType ($filename) {
		$parts = explode('.', $filename['media']['thumbnail']['attrs']['URL']);
		$type = end($parts);
		$mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
		else {
			return 'application/octet-stream';
		}
	}
}

if (defined("TYPO3_MODE") && isset($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/$EXTKEY/mod1/class.tx_rss2import_helper.php"])) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/$EXTKEY/mod1/class.tx_rss2import_helper.php"]);
}
