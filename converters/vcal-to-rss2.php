<?php

!date_default_timezone_get() ? date_default_timezone_set('Europe/Berlin') : null;

require "utf8.inc";

class AU_Vcal_To_RSS2 {
	public static function convert ($calUrl) {
		/*
		 *  TODO: RFC 2445 actually states that only CRLF can be used as line-ending. Here
		 *  we are satisfied with just removing Unix-style newline line-ending characters.
		 */
		$vcalFolded = file($calUrl, FILE_IGNORE_NEW_LINES);
		# RFC 2445 says that long lines can be folded and
		# that we must unfold them before doing anything else.
		$vcal = array();
		$eventLine = "";
		foreach ($vcalFolded as $line) {
			if (substr($line, 0, 1) === chr(32) || substr($line, 0, 1) === chr(9)) {
				$eventLine .= substr($line, 1);
			} else {
				$vcal[] = $eventLine;
				$eventLine = $line;
			}
		}
		$vcal = self::washUTF8ToXML($vcal);

		# Build array of events.
		$events = array();
		$eventId = 0;
		foreach ($vcal as $calLine) {
			if (preg_match('/^BEGIN:VCALENDAR/u', $calLine)) {
				// $eventId++;
				//$events[$eventId] = array();
				continue;
			} else if (preg_match('/^BEGIN:VEVENT/u', $calLine)) {
				$events[$eventId] = array();
			} else if (preg_match('/^X-ORACLE-EVENTINSTANCE-GUID[^:]*:/u', $calLine)) {
				$calLine = preg_replace('/^X-ORACLE-EVENTINSTANCE-GUID[^:]*:(.*)$/u','$1', $calLine);
				$events[$eventId]['guid'] = $calLine;
			} else if (preg_match('/^CREATED[^:]*:(.*)$/u', $calLine)) {
				$calLine = preg_replace('/^CREATED[^:]*:(.*)$/u','$1', $calLine);
				$timestamp = strtotime($calLine);
				$events[$eventId]['pubDate'] = date(DATE_RSS, $timestamp);
			} else if (preg_match('/^DTSTART[^:]*:(.*)$/u', $calLine)) {
				$calLine = preg_replace('/^DTSTART[^:]*:(.*)$/u','$1', $calLine);
				$timestamp = strtotime($calLine);
				$events[$eventId]['startDate'] = date('D, j M Y 00:00:00 O', $timestamp);
				$events[$eventId]['startTime'] = date('H:i:s', $timestamp);
				$events[$eventId]['start'] = $timestamp;
			} else if (preg_match('/^DTEND[^:]*:(.*)$/u', $calLine)) {
				$calLine = preg_replace('/^DTEND[^:]*:(.*)$/u','$1', $calLine);
				$timestamp = strtotime($calLine);
				$events[$eventId]['endDate'] = date('D, j M Y 00:00:00 O', $timestamp);
				$events[$eventId]['endTime'] = date('H:i:s', $timestamp);
				$events[$eventId]['end'] = $timestamp;
			} else if (preg_match('/^LOCATION[^:]*:(.*)$/u', $calLine)) {
				$calLine = preg_replace('/^LOCATION[^:]*:(.*)$/u','$1', $calLine);
				$calLine = self::substFormatting($calLine);
				$events[$eventId]['LOCATION'] = $calLine;
			} else if (preg_match('/^DESCRIPTION[^:]*:(.*)$/u', $calLine)) {
				$calLine = preg_replace('/^DESCRIPTION[^:]*:(.*)$/u','$1', $calLine);
				$calLine = self::substFormatting($calLine);
				$events[$eventId]['DESCRIPTION'] = $calLine;
			} else if (preg_match('/^SUMMARY[^:]*:/u', $calLine)) {
				$calLine = preg_replace('/^SUMMARY[^:]*:(.*)$/u','$1', $calLine);
				$calLine = self::substFormatting($calLine);
				$events[$eventId]['SUMMARY'] = $calLine;
			} else if (preg_match('/^ORGANIZER.*;CN=(.*)$/u', $calLine)) {
				$calLine = preg_replace('/^ORGANIZER.*;CN=(.*)$/u','$1', $calLine);
				$events[$eventId]['ORGANIZER'] = preg_replace('/^([^:]+):.*$/u','$1', $calLine);
				$events[$eventId]['ORGANIZER']= trim($events[$eventId]['ORGANIZER'], '"');
				$events[$eventId]['ORGANIZEREMAIL'] = preg_replace('/.*:mailto:(.*)$/u', '$1', $calLine);
			} else if (preg_match('/^END:VEVENT/u', $calLine)) {
				// $events[$eventId]['VEVENT'] = $calLine;
				$eventId++;
			} else if (preg_match('/^END:VCALENDAR/u', $calLine)) {
				// $events[$eventId]['VCALENDAR'] = $calLine;
			}
		}

		//Newest events first, please (sorting)
		$sortedEvents = array ();
		foreach ($events as $event) {
			$sortedEvents[] = array (strtotime($event['pubDate']),$event);
		}
		arsort($sortedEvents);
		$events = array ();
		foreach($sortedEvents as $event) {
			$events[] = $event[1];
		}

		$xml = self::feedHeader($calUrl);

		$xml .= self::feedContents($events);

		$xml .= self::feedFooter();

		$dom = new DOMDocument();
		$dom->loadXML($xml);
		$dom->formatOutput = true;

		header('Content-type: application/xml; charset=UTF-8');
		print $dom->saveXML();
	}

	/**
	 * Creates the feed header.
	 *
	 * @param	string		$link: A URL
	 * @return	string		The header as a string
	 */
	private static function feedHeader($link) {
		$feedHeader = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$feedHeader .= '<rss version="2.0" xmlns="http://www.cs.au.dk/" xmlns:daimical="http://www.cs.au.dk/~mkh/typo3/test/daimi-calendar-namespace.php">' . "\n";
		$feedHeader .= '  <channel>' . "\n";
		$feedHeader .= '    <title>Recent and upcoming events</title>' . "\n";
		$feedHeader .= '    <link>' . $link . '</link>' . "\n";
		$feedHeader .= '    <description>Export of calendar entries</description>' . "\n";
		$feedHeader .= '    <language>en</language>' . "\n";
		$feedHeader .= '    <image>' . "\n";
		$feedHeader .= '      <title>Recent and upcoming events</title>' . "\n";
		$feedHeader .= '      <url>http://www.cs.au.dk/</url>' . "\n";
		$feedHeader .= '      <link>' . $link . '</link>' . "\n";
		$feedHeader .= '      <width></width>' . "\n";
		$feedHeader .= '      <height></height>' . "\n";
		$feedHeader .= '      <description>News from calendar</description>' . "\n";
		$feedHeader .= '    </image>' . "\n";
		$feedHeader .= '    <generator>AU Oracle Calendar to RSS2</generator>' . "\n";
		$feedHeader .= '    <docs>http://blogs.law.harvard.edu/tech/rss</docs>' . "\n";
		$feedHeader .= '    <lastBuildDate>' . date(DATE_RSS) . '</lastBuildDate>' . "\n\n";
		return $feedHeader;
	}

	/**
	 * Creates a string representing RSS item elements.
	 *
	 * @param	array		$events: Array of event information. Each element is an array of item information.
	 * @return	string		String representation of items.
	 */
	private static function feedContents (array $events) {
		$category = ""; // Placeholder.
		$emptyEvent = array(
			'SUMMARY' => '',
			'ORGANIZER' => '',
			'ORGANIZEREMAIL' => '',
			'pubDate' => '',
			'startDate' => '',
			'endDate' => '',
			'startTime' => '',
			'endTime' => '',
			'LOCATION' => '',
			'DESCRIPTION' => '',
			'guid' => ''
		);
		$contents = '';
		foreach ($events as $event) {
			$event = array_merge($emptyEvent, $event);
			$item = '    <item>' . "\n";
			$item .= '      <title><![CDATA[' . $event['SUMMARY'] . ']]></title>' . "\n";
			$item .= '      <link><![CDATA[' . ']]></link>' . "\n";
			$item .= '      <author><![CDATA[' . $event['ORGANIZEREMAIL'] . ']]></author>' . "\n";
			$item .= '      <daimical:author-name><![CDATA[' . $event['ORGANIZER'] . ']]></daimical:author-name>' . "\n";
			$item .= '      <daimical:start-date><![CDATA[' . $event['startDate'] . ']]></daimical:start-date>' . "\n";
			$item .= '      <daimical:start-time><![CDATA[' . $event['startTime'] . ']]></daimical:start-time>' . "\n";
			$item .= '      <daimical:end-date><![CDATA[' . $event['endDate'] . ']]></daimical:end-date>' . "\n";
			$item .= '      <daimical:end-time><![CDATA[' . $event['endTime'] . ']]></daimical:end-time>' . "\n";
			$item .= '      <daimical:start><![CDATA[' . $event['start'] . ']]></daimical:start>' . "\n";
			$item .= '      <daimical:end><![CDATA[' . $event['end'] . ']]></daimical:end>' . "\n";
			$item .= '      <daimical:location><![CDATA[' . $event['LOCATION'] . ']]></daimical:location>' . "\n";
			$item .= '      <description><![CDATA[' . $event['DESCRIPTION'] . ']]></description>' . "\n";
			$item .= '      <category><![CDATA[' . $category . ']]></category>' . "\n";
			$item .= '      <guid isPermaLink="false"><![CDATA[' . $event['guid'] . ']]></guid>' . "\n";
			$item .= '      <pubDate><![CDATA[' . $event['pubDate'] . ']]></pubDate>' . "\n";
			$item .= '    </item>' . "\n";
			$contents .= $item . "\n";
		}
		return $contents;
	}

	/**
	 * Footer of RSS2 feed
	 *
	 * @return	string		Footer of feed as string
	 */
	private static function feedFooter () {
		$feedFooter = '  </channel>' . "\n";
		$feedFooter .= '</rss>' . "\n";
		return $feedFooter;
	}
	// Function substitutes the backslash-quoted parts of the strings.
	private static function substFormatting ($str) {
		$str = str_replace("\\n\\n","\n\n",$str);
		$str = str_replace("\\n","\n",$str);
		$str = str_replace("\\,",",",$str);
		return $str;
	}

	/*
	 * 'Washes' the contents given, so that characters that are not allowed in XML
	 * are removed.
	 */
	private static function washUTF8ToXML (array $contents) {
		$washedContents = array();
		foreach ($contents as $line) {
			$codepoints = UnicodeAndUTF8StringConversion::utf8ToUnicode($line);
			if (is_array($codepoints)) {
				$codepoints = self::removeIllegalXMLCharacters($codepoints);
				$washedContents[] = UnicodeAndUTF8StringConversion::unicodeToUtf8($codepoints);
			}
		}
		return $washedContents;
	}

	/*
	 * In XML there are some characters that are not allowed, cf.
	 * http://www.w3.org/TR/xml/#charsets. This function removes these characters.
	 * The specification states that a character is:
	 *
	 * Char	::= #x9 | #xA | #xD | [#x20-#xD7FF] | [#xE000-#xFFFD] | [#x10000-#x10FFFF]
	 *
	 * This function removes any codepoint that is not a legal character. Furthermore,
	 * the characters that the XML specification encourages to avoid are also removed
	 * but can be turned off by passing false as the second parameter. Note that we
	 * do not try to filter compability characters, which is not required by the
	 * XML specification anyway (and we do not know how to recognize them).
	 */
	private static function removeIllegalXMLCharacters (array $codepoints, $removeDiscouragedChars = true) {
		$filteredCodepoints = array();
		foreach ($codepoints as $codepoint) {
			if ($removeDiscouragedChars) {
				if (($codepoint >= 0x7F && $codepoint <= 0x84) ||
					($codepoint >= 0x86 && $codepoint <= 0x9F) ||
					($codepoint >= 0xFDD0 && $codepoint <= 0xFDEF) ||
					$codepoint === 0x1FFFE || $codepoint === 0x1FFFF || // Top 'bits' in plane 1
					$codepoint === 0x2FFFE || $codepoint === 0x2FFFF || // Top 'bits' in plane 2
					$codepoint === 0x3FFFE || $codepoint === 0x3FFFF || // Top 'bits' in plane 3
					$codepoint === 0x4FFFE || $codepoint === 0x4FFFF || // Top 'bits' in plane 4
					$codepoint === 0x5FFFE || $codepoint === 0x5FFFF || // Top 'bits' in plane 5
					$codepoint === 0x6FFFE || $codepoint === 0x6FFFF || // Top 'bits' in plane 6
					$codepoint === 0x7FFFE || $codepoint === 0x7FFFF || // Top 'bits' in plane 7
					$codepoint === 0x8FFFE || $codepoint === 0x8FFFF || // Top 'bits' in plane 8
					$codepoint === 0x9FFFE || $codepoint === 0x9FFFF || // Top 'bits' in plane 9
					$codepoint === 0xAFFFE || $codepoint === 0xAFFFF || // Top 'bits' in plane 10
					$codepoint === 0xBFFFE || $codepoint === 0xBFFFF || // Top 'bits' in plane 11
					$codepoint === 0xCFFFE || $codepoint === 0xCFFFF || // Top 'bits' in plane 12
					$codepoint === 0xDFFFE || $codepoint === 0xDFFFF || // Top 'bits' in plane 13
					$codepoint === 0xEFFFE || $codepoint === 0xEFFFF || // Top 'bits' in plane 14
					$codepoint === 0xFFFFE || $codepoint === 0xFFFFF || // Top 'bits' in plane 15
					$codepoint === 0x10FFFE || $codepoint === 0x10FFFF) {  // Top 'bits' in plane 16
					$codepoint = null;
				}
			}

			// These are the Unicode character codepoints allowed by the XML specification.
			// Ref.: http://www.w3.org/TR/xml/#charsets
			if ($codepoint === 0x9 ||
				$codepoint === 0xA ||
				$codepoint === 0xD ||
				($codepoint >= 0x0020 && $codepoint <= 0xD7FF) ||
				($codepoint >= 0xE000 && $codepoint <= 0xFFFD) ||
				($codepoint >= 0x10000 && $codepoint <= 0x10FFFF)) {
				$filteredCodepoints[] = $codepoint;
			}
		}
		return $filteredCodepoints;
	}
}
AU_Vcal_To_RSS2::convert($_GET['vcalurl']);
?>
