<?php
/*
 * Character references can be found here:
 * - http://www.w3schools.com/tags/ref_symbols.asp
 * - http://www.w3schools.com/tags/ref_entities.asp
 * - http://www.w3schools.com/HTML/html_entities.asp
 * Unicode characters can be found here as PDFs:
 * - http://www.unicode.org/charts/
 * Testing/checking:
 * - http://www.alanwood.net/unicode/index.html#links
 * - http://www.alanwood.net/unicode/fonts.html (Windows fonts)
 *
 * TODO: Replace Unicode numbers with HTML entities in the cases where HTML have an
 * equivalent glyph defined. This will make the HTML code more legible and provide
 * for the browser to choose an appropriate codepoint in the font being used.
 */
class user_AU_FixDescriptionField {
	public function __construct () {
		mb_internal_encoding('UTF-8');
	}

	/**
	 * Accepts a string and replaces a lot of characters that are outside the UTF-8 Basic Multilingual Plane
	 * with characters that are inside. It also replaces some strings with nicer/more appropriate characters.
	 * Lastly, some characters are substituted with characters that are more common in the fonts that peoples
	 * web browsers use. 
	 *
	 * @param	string		$description: ...
	 * @return	string		The processed string.
	 */
	public function fix ($description) {
		// These lines sanitize text that has line-breaks in paragraphs.
		// (usually because the text was cut'n'pasted from an editor or terminal window).
		$description = trim($description);

		// Remove trailing 'stedkode' (SK), which are appended by the system because of some bureaucracy!
		$description = preg_replace('/^(.*)SK: ?[0-9]{4}$/su', '$1', $description);
		// Trim again to remove any lingering newline(s) that might have become exposed after removing 'stedkode'
		$description = rtrim($description);
		$description = preg_replace('/^(.*)SK\s*[0-9]{4,5}/u', '$1', $description);
		$description = trim($description);

		$description = preg_replace('/(\p{Zp}){2,}/u', '<p>', $description);
		$description = preg_replace('/(\p{Zl}){2,}/u', '<br>', $description);
		$description = preg_replace('/(\\n){2,}/u', '<p>', $description);
		$description = preg_replace('/\\n/u', '<br>', $description);
		$description = preg_replace('/([^-])---([^-])/u', '$1&#x2014;$2', $description);
		$description = preg_replace('/(\pZ)--(\pZ)/u', '$1&#x2013;$2', $description);
		$description = preg_replace('/(\pZ)̸=(\pZ)/u', '$1&#x2260;$2', $description);

		// Unicode name: 'GREATER-THAN OR SLANTED EQUAL TO'. From 10FC00 to 2A7D.
		$description = preg_replace('/􏰁/u', '&#x2A7D;', $description);

		// Unicode name: 'GREATER-THAN OR SLANTED EQUAL TO'. From 10FC01 to 2A7E.
		$description = preg_replace('/􏰀/u', '&#x2A7E;', $description);

		// Unicode name: 'N-ARY SUMMATION'. From 10FC02 to 2211.
		$description = preg_replace('/􏰂/u', '&#x2211;', $description);

		// Unicode name: 'SQUARE ROOT'. From 10FCFE to 221A.
		$description = preg_replace('/􏳾/u', '&#x221A;', $description);

		// Unicode name: 'N-ARY SUMMATION'. From 10FCFA to 2211.
		$description = preg_replace('/􏳺/u', '&#x2211;', $description);

		// Unicode name: 'INTEGRAL'. From 10FCF8 to 222B.
		$description = preg_replace('/􏳸/u', '&#x222B;', $description);

		// Unicode name: 'N-ARY PRODUCT'. From 10FCF9 to 2220F.
		$description = preg_replace('/􏳹/u', '&#x220F;', $description);

		// Unicode name: 'SQUARE ORIGINAL OF'. 10FCB5 From  to 2290.
		$description = preg_replace('/􏲵/u', '&#2290;', $description);

		// Unicode name: 'SQUARE IMAGE OF'. From 10FCB4 to 228F.
		$description = preg_replace('/􏲴/u', '&#x228F;', $description);

		// Unicode name: 'APPROACHES THE LIMIT'. From =. to 2250.
		$description = preg_replace('/=\./u', '&#x2250;', $description);

		// Unicode name: 'APPROXIMATELY EQUAL TO'. From ∼= to 2245.
		$description = preg_replace('/∼=/u', '&#x2245;', $description);

		// Unicode name: 'BOWTIE'. From ◃▹ to 22C8.
		$description = preg_replace('/◃▹/u', '&#x22C8;', $description);

		// Unicode name: 'WHITE LEFT-POINTING TRIANGLE'. From 10FC0F to 25C1.
		$description = preg_replace('/􏰏/u', '◁', $description);

		// Unicode name: 'WHITE RIGHT-POINTING TRIANGLE'. From 10FC11 to 25B7.
		$description = preg_replace('/􏰑/u', '▷', $description);

		// Unicode name: 'TRUE'. From |= to 22A8.
		$description = preg_replace('/\|=/u', '&#x22A8;', $description);

		// Unicode name: 'NOT AN ELEMENT OF'. From ∈/ to 2209.
		$description = preg_replace('/∈\//u', '&#x2209;', $description);

		// Unicode name: 'RIGHTWARDS ARROW FROM BAR'. From 10FC2C→ to 21A6.
		$description = preg_replace('/􏰬→/u', '&#x21A6;', $description);

		// Unicode name: 'LEFTWARDS ARROW WITH HOOK'. From ← 10FC0C to 21A9.
		$description = preg_replace('/←􏰌/u', '&#x21A9;', $description);

		// Unicode name: 'RIGHTWARDS HARPOON OVER LEFTWARDS HARPOON'. From 10FC89 to 21CC.
		$description = preg_replace('/􏲉/u', '&#x21CC;', $description);

		// Unicode name: 'LONG LEFTWARDS ARROW'. From  to 27F5.
		$description = preg_replace('/←−/u', '&#x27F5;', $description);

		// Unicode name: 'RIGHTWARDS ARROW WITH HOOK'. From 10FC2C -→ to 21AA.
		$description = preg_replace('/􏰬−→/u', '&#x21AA;', $description);

		// Unicode name: 'LONG RIGHTWARDS ARROW'. From  to 27F6.
		$description = preg_replace('/−→/u', '&#x27F6;', $description);

		// Unicode name: 'LONG LEFT RIGHT ARROW'. From  to 2194.
		$description = preg_replace('/←→/u', '&#x27F7;', $description);

		// Unicode name: 'LONG LEFTWARDS DOUBLE ARROW'. From  to 27F8.
		$description = preg_replace('/⇐=/u', '&#x27F8;', $description);

		// Unicode name: 'LONG RIGHTWARDS DOUBLE ARROW'. From  to 27F9.
		$description = preg_replace('/=⇒/u', '&#x27F9;', $description);

		// Unicode name: 'LONG LEFT RIGHT DOUBLE ARROW'. From  to 27FA.
		$description = preg_replace('/⇐⇒/u', '&#x27FA;', $description);
		//t3lib_div::devLog('description parameter in fix method ' . __METHOD__, __FILE__, 1, array('Str' => $description));
		return $description;
	}

	/**
	 * Functions to find out if the imported news should be a external news link, this is
	 * the case if the description/bodytext only contains a valid url.
	 *
	 * @param string $str: The bodytext to work on
	 * @param array  $config: Should contain an entry 'type', which is the value to set when importing. This value must be a constant for the type field in a tt_news record.
	 */
	public function newsLinkType($str, array $config = array('type' => INFO_TYPE_NEWS_EXTERNAL, 'default' => INFO_TYPE_NEWS)) {
		$type = constant($config['default']);
		//check is the str is a vaild url and does not contains a " " space. If so they the news type should be changes to External Link.
		if (preg_match("#^http(s)?://[a-z0-9-_.]+\.[a-z]{2,4}#i", $str) && strpos($str, ' ') === false) {
			// Here we use the constants that are found in class tx_rss2import_helper{}; the constants match up with au_newsevent (and tt_news).
			$type = constant($config['type']);
		}
		return $type;
	}

	// Debug function that can be used for verifying parameters. Returns the input string unmodified.
	public function devLogParams ($str, $config) {
		//t3lib_div::devLog('Parameters recieved by method ' . __METHOD__, __FILE__, 1, array('Str' => $str, 'Config' => $config));
		return $str;
	}

	// Convert a string of the form 'hours:minutes:seconds' into seconds.
	public function timeToSecondsSinceMidnight ($str) {
		$parts = split(':', $str);
		$hours = intval($parts[0]);
		$hours = $hours >= 0 ? $hours : 0;
		$hours = $hours <= 23 ? $hours : 23;
		$minutes = intval($parts[1]);
		$minutes = $minutes >= 0 ? $minutes : 0;
		$minutes = $minutes <= 59 ? $minutes : 59;
		$seconds = intval($parts[2]);
		$seconds = $seconds >= 0 ? $seconds : 0;
		$seconds = $seconds <= 59 ? $seconds : 59;
		return $hours * 3600 + $minutes * 60 + $seconds;
	}
}