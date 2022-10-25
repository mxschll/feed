<?php

namespace App\Libraries;

use Carbon\Carbon;
use Exception;
use SimpleXMLElement;
use Illuminate\Support\Facades\Cache;
use League\HTMLToMarkdown\HtmlConverter;

enum FeedTypes
{
    case RSS;
    case ATOM;
}

class Entry
{
    public $url;
    public $title;
    public $domain;
    public $content;
    public $published;
}

class Feed
{
    public string $url;
    public string $title;
    public string $domain;
    public FeedTypes $type;
    public array $entries = [];
    public SimpleXMLElement $xml;

    private $converter;

    public function __construct($url = null)
    {
        $this->url = $url;
        $this->domain = parse_url($url, PHP_URL_HOST);

        $this->converter = new HtmlConverter(array('strip_tags' => true));
    }

    private function htmlToMarkdown($html)
    {
        return $this->converter->convert(preg_replace('/<img(.*?)>/', '', $html));
    }

    private static function truncate($string, $length = 100, $append = "...")
    {
        $string = trim($string);

        if (strlen($string) > $length) {
            $string = wordwrap($string, $length);
            $string = explode("\n", $string, 2);
            $string = $string[0] . $append;
        }

        return $string;
    }

    public function parse()
    {
        $this->xml = $this->getXml();

        if ($this->xml->channel) {
            $this->type = FeedTypes::RSS;
            $this->parseRss();
        } elseif ($this->xml->entry) {
            $this->type = FeedTypes::ATOM;
            $this->parseAtom();
        } else {
            throw new Exception('Invalid feed URL');
        }
    }

    private function getXml()
    {
        $cacheFile = 'feed:' . $this->url;

        if (Cache::has($cacheFile)) {
            $data = Cache::get($cacheFile);
        } elseif ($data = trim(self::httpRequest($this->url))) {
            Cache::put($cacheFile, $data, now()->addMinutes(30));
        } else {
            throw new Exception('Cannot load feed.');
        }

        return new SimpleXMLElement($data, LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_NOCDATA);
    }

    private static function httpRequest($url)
    {
        if (extension_loaded('curl')) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_USERAGENT, 'FeedFetcher-Google'); // some feeds require a user agent
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_TIMEOUT, 3);
            curl_setopt($curl, CURLOPT_ENCODING, '');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // no echo, just return result
            curl_setopt($curl, CURLOPT_USERAGENT, '');
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // sometime is useful :)

            $result = curl_exec($curl);
            
            return curl_errno($curl) === 0 && curl_getinfo($curl, CURLINFO_HTTP_CODE) === 200
                ? $result
                : false;
        }
    }

    public function parseRss()
    {
        $this->title = (string) $this->xml->channel->title;

        foreach ($this->xml->channel->item as $item) {
            $entry = new Entry();
            $entry->url = (string) $item->link;
            $entry->title = $this->truncate((string) $item->title);
            $entry->domain = $this->domain;
            $entry->content = $this->htmlToMarkdown((string) $item->description);
            $entry->published = Carbon::parse($item->pubDate);

            $this->entries[] = $entry;
        }
    }

    public function parseAtom()
    {
        $this->title = (string) $this->xml->title;

        foreach ($this->xml->entry as $item) {
            $entry = new Entry();
            $entry->url = (string) $item->link['href'];
            $entry->title = $this->truncate((string) $item->title);
            $entry->domain = $this->domain;
            $entry->published = Carbon::parse($item->published);

            if ($item->content) {
                $entry->content = $this->htmlToMarkdown((string) $item->content);
            } elseif ($item->summary) {
                $entry->content = $this->htmlToMarkdown((string) $item->summary);
            }
            
            $this->entries[] = $entry;
        }
    }

    public function getEntries()
    {
        return $this->entries;
    }
}







