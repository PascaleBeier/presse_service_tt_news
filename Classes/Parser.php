<?php
/**
 * This will gather all basic information of an RSS 2 Feed.
 * Along with basic functionality, the Parser will
 * also parse feeds extended by namespaces.
 */

Namespace RuhrConnect\Rss2Import;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Parser
 * @package RuhrConnect\Rss2Import
 */
class Parser
{
	private $parseResult = [];
	private $insideitem = false;
	private $insideimage = false;
	private $insidetext = false;
	private $insideNS = [];
	private $url = '';
	private $enclosure = '';

	//channel vars
	private $cTitle, $cLink, $cDesc, $cLanguage, $cCopyright, $cManageEditor, $cWebmaster, $cLastBuild, $cRating, $cDocs, $cCategory,  $cGenerator, $cPubDate;

	//image vars
	private $imTitle, $imUrl, $imLink, $imWidth, $imHeight;

	//item vars
	private $iTitle, $iLink, $iDesc, $iAuthor, $iComments, $iEnclosure, $iGuid, $iPubDate, $iSource, $iCloud;
	private $iCategory = array();
	private $iExtrasNS = array(); //The extra tags extended by namespaces

	//textinput elements
	private $tTitle, $tLink, $tDesc, $tName;

	private $result;

	private $errors = array();

	private $namespaces = array();

	private $xml_parser = null;

	public function __construct() {
		$this->parseResult = array();
		$this->xml_parser = xml_parser_create();
		xml_set_object($this->xml_parser, $this);
		xml_set_element_handler($this->xml_parser, "startElement", "endElement");
		xml_set_character_data_handler($this->xml_parser, "characterData");
	}

    /**
     * Parse an RSS Feed.
     *
     * @param $url
     */
	public function parse($url) {
		$xml = GeneralUtility::getURL($url);
		$status = xml_parse($this->xml_parser, $xml);
		if (!$status) {
			$this->errors[] = 'XML error: ' . xml_error_string(xml_get_error_code($this->xml_parser)) . ' at line ' . xml_get_current_line_number($this->xml_parser);
		}
	}

    /**
     * Return any errors currently registered in parser.
     *
     * @return array
     */
	public function get_errors() {
		return $this->errors;
	}

    /**
     * Get the <channel>-Element
     *
     * @return mixed|null
     */
	public function get_channel()
    {
        return isset($this->parseResult['channel']) ? $this->parseResult['channel'] : null;
	}

	/**
	 * Get RSS2 item elements
	 *
	 * @return	[type]		...
	 */
	public function get_items(){
		if(isset($this->parseResult['items'])){
			return($this->parseResult['items']);
		}
	}

	/**
	 * Get RSS2 image element
	 *
	 * @return	[type]		...
	 */
	public function get_image(){
		if(isset($this->parseResult['image'])){
			return($this->parseResult['image']);
		}
	}

	/**
	 * Get RSS2 rating element
	 *
	 * @return	[type]		...
	 */
	public function get_rating(){
		if(isset($this->parseResult['rating'])){
			return($this->parseResult['rating']);
		}
	}

	/**
	 * Get RSS2 textinput element
	 *
	 * @return	[type]		...
	 */
	public function get_textinput(){
		if(isset($this->parseResult['textinput'])){
			return($this->parseResult['textinput']);
		}
	}

	/**
	 * Get the starting tag of a given element.
	 *
	 * @param	object		$parser: ...
	 * @param	string		$tagName: ...
	 * @param	array		$attrs: ...
	 * @return	[type]		...
	 */
	private function startElement($parser, $tagName, $attrs) {

		$this->tag = $tagName;

		if ($tagName == "RSS") {
			//NameSpace information gathering
			if (isset($attrs['XMLNS'])) {
				$this->namespaces['DEFAULT'] = $attrs['XMLNS'];
			}
			foreach ($attrs as $key => $value) {
				if (substr($key,0,6) == 'XMLNS:') {
					$namespace = explode(':', $key, 2);
					$this->namespaces[$namespace[1]] = $value;
				}
			}
		}

		if($tagName == "ITEM") {
			$this->insideitem = true;
		}elseif($tagName == "IMAGE") {
			$this->insideimage = true;
		}elseif($tagName == 'TEXTINPUT'){
			$this->insidetext = true;
		}

		if($tagName == "ENCLOSURE") {
			$this->enclosure = $attrs;
		}

		//Check for namespace extension
		$tagNameNS = explode(':', $tagName, 2);
		if(count($tagNameNS) == 2) {
			if($this->namespaces[$tagNameNS[0]]) { //We're entering a valid namespace
				$this->insideNS[] = strtolower($tagName);
				$extraTag = array();
				$extraTag['attrs'] = $attrs;
				$this->iExtrasNS[strtolower($tagNameNS[0])][strtolower($tagNameNS[1])] = $extraTag;
			} else {
				//invalid tag (ignoring)
			}
		}
	}

	/**
	 * Work on the raw text string
	 *
	 * @param	object		$parser: ...
	 * @param	[type]		$data: ...
	 * @return	[type]		...
	 */
	private function characterData($parser, $data) {
		if ($this->insideitem) {
			switch ($this->tag) {
				case "TITLE":
					$this->iTitle .= $data;
					$this->i++;
					break;
				case "DESCRIPTION":
					$this->iDesc .= $data;
					break;
				case "LINK":
					$this->iLink .= $data;
					break;
				case "AUTHOR":
					$this->iAuthor .= $data;
					break;
				case "CATEGORY":
					$this->iCategory[] .= $data;
					break;
				case "COMMENTS":
					$this->iComments .= $data;
					break;
				case "PUBDATE":
					$this->iPubDate .= $data;
					break;
				case "SOURCE":
					$this->iSource .= $data;
					break;
				case "GUID":
					$this->iGuid .= $data;
					break;
				default:
					//if valid NS, get data
					if(count($this->insideNS)>0) {
						$tagNameNS = explode(':', $this->insideNS[count($this->insideNS)-1], 2);
						$this->iExtrasNS[$tagNameNS[0]][$tagNameNS[1]]['data'] = $data;
					} else {
						//Invalid tagName (ignored)
					}
			}

		}
		if($this->insideimage){
			switch ($this->tag) {
				case "TITLE":
					$this->imTitle .= $data;
					break;
				case "URL":
					$this->imUrl .= $data;
					break;
				case "LINK":
					$this->imLink .= $data;
					break;
				case "WIDTH":
					$this->imWidth .= $data;
					break;
				case "HEIGHT":
					$this->imHeight .= $data;
					break;
			}
		}
		if($this->insidetext){
			switch ($this->tag) {
				case "TITLE":
					$this->tTitle .= $data;
					break;
				case "DESCRIPTION":
					$this->tDesc .= $data;
					break;
				case "LINK":
					$this->tLink .= $data;
					break;
				case "NAME":
					$this->tName .= $data;
					break;
			}
		}
		if(!$this->insideitem && !$this->insidetext && !$this->insideimage){
			//Assuming this means that we're in a channel
			switch ($this->tag) {
				case "TITLE":
					$this->cTitle .= $data;
					break;
				case "LINK":
					$this->cLink .= $data;
					break;
				case "DESCRIPTION":
					$this->cDesc .= $data;
					break;
				case "LANGUAGE":
					$this->cLanguage .= $data;
					break;
				case "COPYRIGHT":
					$this->cCopyright .= $data;
					break;
				case "MANAGINGEDITOR":
					$this->cManageEditor .= $data;
					break;
				case "WEBMASTER":
					$this->cWebmaster .= $data;
					break;
				case "LASTBUILDDATE":
					$this->cLastBuild .= $data;
					break;
				case "GENERATOR":
					$this->cGenerator .= $data;
					break;
				case "RATING":
					$this->cRating .= $data;
					break;
				case "DOCS":
					$this->cDocs .= $data;
					break;
				case "CATEGORY":
					$this->cCategory .= $data;
					break;
				case "PUBDATE":
					$this->cPubDate .= $data;
			}

		}
	}

	/**
	 * Work on the ending tag of an element.
	 *
	 * @param	object		$parser: ...
	 * @param	string		$tagName: ...
	 * @return	[type]		...
	 */
	private function endElement($parser, $tagName) {

		if ($tagName == "ITEM") {

			$this->result['item']["title"] = $this->iTitle;
			$this->result['item']["description"] = $this->iDesc;
			$this->result['item']["link"] = $this->iLink;
			$this->result['item']["author"] = $this->iAuthor;
			$this->result['item']['category'] = $this->iCategory;
			$this->result['item']['comments'] = $this->iComments;
			$this->result['item']['pubdate'] = $this->iPubDate;
			$this->result['item']["source"] = $this->iSource;
			$this->result['item']['enclosure'] = $this->enclosure;
			$this->result['item']['guid'] = $this->iGuid;
			foreach($this->iExtrasNS as $key => $extrasNS) {
				$this->result['item'][$key] = $extrasNS;
			}
			$this->parseResult['items'][] = $this->result['item'];
			$this->iTitle = "";
			$this->iDesc = "";
			$this->iLink = "";
			$this->iAuthor = "";
			$this->iCategory = array();
			$this->iComments = '';
			$this->iPubDate = "";
			$this->iSource = "";
			$this->enclosure = "";
			$this->guid = "";
			$this->iGuid = '';
			$this->insideitem = false;
		}
		if ($tagName == 'IMAGE'){

			$this->result['image']['title'] = $this->imTitle;
			$this->result['image']['url'] = $this->imUrl;
			$this->result['image']['link'] = $this->imLink;
			$this->result['image']['width'] = $this->imWidth;
			$this->result['image']['height'] = $this->imHeight;


			$this->parseResult['image'] = $this->result['image'];
			$this->imTitle = "";
			$this->imUrl = "";
			$this->imLink = "";
			$this->imWidth = "";
			$this->imHeight = "";
			$this->insideimage = false;

		}
		if($tagName == 'CHANNEL'){
			$this->result['channel']['title'] = $this->cTitle;
			$this->result['channel']['link'] = $this->cLink;
			$this->result['channel']['desc'] = $this->cDesc;
			$this->result['channel']['lang'] = $this->cLanguage;
			$this->result['channel']['copy'] = $this->cCopyright;
			$this->result['channel']['editor'] = $this->cManageEditor;
			$this->result['channel']['webmaster'] = $this->cWebmaster;
			$this->result['channel']['lastbuild'] = $this->cLastBuild;
			$this->result['channel']['generator'] = $this->cGenerator;
			$this->result['channel']['rating'] = $this->cRating;
			$this->result['channel']['docs'] = $this->cDocs;
			$this->result['channel']['category'][] = $this->cCategory;
			$this->result['channel']['pubDate'] = $this->cPubDate;

			$this->parseResult['channel'] = $this->result['channel'];
			$this->cTitle = '';
			$this->cLink = '';
			$this->cDesc = '';
			$this->cLanguage = '';
			$this->cCopyright = '';
			$this->cManageEditor = '';
			$this->cWebmaster = '';
			$this->cLastBuild = '';
			$this->cGenerator = '';
			$this->cRating = '';
			$this->cDocs = '';
			$this->cPubDate = '';
		}
		if ($tagName == 'TEXTINPUT'){

			$this->result['textinput']['title'] = $this->tTitle;
			$this->result['textinput']['name'] = $this->tName;
			$this->result['textinput']['link'] = $this->tLink;
			$this->result['textinput']['description'] = $this->tDesc;

			$this->parseResult['textinput'] = $this->result['textinput'];
			$this->tTitle = "";
			$this->tDesc = "";
			$this->tLink = "";
			$this->tName = "";
			$this->insidetext = false;
		}

		$tagNameNS = explode(':', $tagName, 2);
		if(isset($this->namespaces[$tagNameNS[0]])) { //We're getting out of a valid namespace
			$NSkeys = array_keys($this->insideNS);
			$NSkey = $NSkeys[count($this->insideNS)-1];
			$NS = array_pop($this->insideNS);
			if($tagName!=$NSkey) echo('Something went wrong: '.$NSkey.' differs from '.$tagName.'!<br />');
		}
	}

}
