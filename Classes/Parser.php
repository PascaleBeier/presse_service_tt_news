<?php
/**
 * This reads a remote Feed. Supported Formats: ATOM, RSS, RSS 2.0.
 */

Namespace RuhrConnect\Rss2Import;

use Zend\Feed\Reader\Entry\EntryInterface;
use Zend\Feed\Reader\Feed\FeedInterface;
use Zend\Feed\Reader\Reader;
use Zend\Feed\Exception\RuntimeException;

/**
 * Class Parser
 * @package RuhrConnect\Rss2Import
 */
class Parser
{
    /** @var FeedInterface|array */
    private $parseResult = [];
    /** @var array */
    private $errors = [];

    /**
     * Parse an RSS Feed.
     *
     * @param $url
     */
    public function parse($url)
    {
        try {
            /** @var $importedFeed FeedInterface */
            $importedFeed = Reader::import($url);
            // Initialize the channel/feed data array
            $this->parseResult = [
                'title'       => $importedFeed->getTitle(),
                'link'        => $importedFeed->getLink(),
                'description' => $importedFeed->getDescription(),
                'items'       => [],
            ];

            // Loop over each channel item/entry and store relevant data for each   
            foreach ($importedFeed as $item) {
                /** @var $item EntryInterface */
                $this->parseResult['items'][] = [
                    'title'       => $item->getTitle(),
                    'link'        => $item->getLink(),
                    'description' => $item->getDescription(),
                    'enclosure'   => $item->getEnclosure(),
                ];
            }
        } catch (RuntimeException $e) {
            // feed import failed
            $this->errors[] = "Exception caught importing feed: {$e->getMessage()}\n";
        }
    }

    /**
     * Return any errors currently registered in parser.
     *
     * @return array
     */
    public function get_errors()
    {
        return $this->errors;
    }

    /**
     * Get the <channel>-Element
     *
     * @return mixed|null
     */
    public function get_channel()
    {
        return $this->parseResult;
    }

    /**
     * Get all <item>s
     *
     * @return mixed|null
     */
    public function get_items()
    {
        return isset($this->parseResult['items']) ? $this->parseResult['items'] : null;
    }
}
