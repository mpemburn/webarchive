<?php

namespace App\Observers;

use App\Facades\Curl;
use App\Services\BrowserScreenShotService;
use App\Services\DocumentService;
use App\Services\DownloadService;
use App\Services\SaveToHtmlService;
use DOMDocument;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlObservers\CrawlObserver;

class WebObserver extends CrawlObserver
{
    protected string $baseUrl;
    protected string $rootUrl;
    protected string $saveDirectory;
    protected bool $saveAsScreenshots;
    protected bool $echo;
    protected Collection $titles;

    protected BrowserScreenShotService $screenshotService;
    protected DocumentService $document;
    protected DownloadService $download;
    protected SaveToHtmlService $htmlService;

    public function __construct(string $baseUrl, bool $saveAsScreenshots = true, $echo = true)
    {
        $this->document = new DocumentService();
        $this->download = new DownloadService($baseUrl);
        $this->baseUrl = $baseUrl;
        $this->rootUrl = $this->document->getRootUrl($baseUrl);
        $this->saveAsScreenshots = $saveAsScreenshots;
        $this->echo = $echo;
        $this->titles = collect();
        $this->saveDirectory = $this->makeSaveDirectory($baseUrl);

        if ($saveAsScreenshots) {
            $this->screenshotService = new BrowserScreenShotService($this->saveDirectory);
        }

        $this->htmlService = new SaveToHtmlService($this->rootUrl, $this->saveDirectory);
    }

    /**
     * Called when the crawler will crawl the url.
     *
     * @param UriInterface $url
     * @param string|null $linkText
     */
    public function willCrawl(UriInterface $url, ?string $linkText): void
    {
        if ($this->echo) {
            echo 'Testing: ' . $url . PHP_EOL;
        }

        // Download $url if it's a document
        $ext = $this->document->getExtension($url);
        $this->download->downloadDocument($url, $ext, $this->saveDirectory);
    }

    /**
     * Called when the crawler has crawled the given url successfully.
     *
     * @param UriInterface $url
     * @param ResponseInterface $response
     * @param UriInterface|null $foundOnUrl
     * @param string|null $linkText
     */
    public function crawled(
        UriInterface      $url,
        ResponseInterface $response,
        ?UriInterface     $foundOnUrl = null,
        ?string           $linkText = null): void
    {
        // Make sure that this isn't an external URL
        if (stripos($url, $this->rootUrl) === false) {
            return;
        }

        $doc = new DOMDocument();
        $body = $response->getBody();
        if (strlen($body) === 0) {
            return;
        }

        @$doc->loadHTML($body);
        $content = $doc->saveHTML();
        $ext = $this->document->getExtension($url);
        $title = $this->document->getDocumentTitle($body, $url, $this->titles);

        echo "Crawling... " . PHP_EOL;

        $this->process($url, $content, $title, $ext);
    }

    /**
     * Called when the crawler had a problem crawling the given url.
     *
     * @param UriInterface $url
     * @param RequestException $requestException
     * @param UriInterface|null $foundOnUrl
     * @param string|null $linkText
     */
    public function crawlFailed(
        UriInterface $url,
        RequestException $requestException,
        ?UriInterface $foundOnUrl = null,
        ?string $linkText = null): void
    {
        $newUrl = $url;

        if (! Curl::testUrl($url)) {
            $newUrl = $this->baseUrl . '/' . $this->document->getPageName($url);
        }

        echo 'Crawl failed. Retrying with: ' . $newUrl . PHP_EOL;

        if (! Curl::testUrl($newUrl)) {
            echo $newUrl . ' not found.' . PHP_EOL;
            return;
        }

        $content = file_get_contents($newUrl);
        $ext = $this->document->getExtension($newUrl);
        $title = $this->document->getDocumentTitle($content, $newUrl, $this->titles);

        $this->process($newUrl, $content, $title, $ext);
    }

    /**
     * Called when the crawl has ended.
     */
    public function finishedCrawling(): void
    {
    }

    protected function makeSaveDirectory(string $url): string
    {
        $parsed = parse_url($url);

        if (isset($parsed['path'])) {
            $parts = array_filter(explode('/', $parsed['path']));
            return implode('_', $parts) . PHP_EOL;
        }

        return str_replace('.', '_', $parsed['host']);
    }

    protected function process(string $url, string $content, string $title, string $ext): void
    {
        if ($this->download->downloadDocument($url, $ext, $this->saveDirectory)) {
            return;
        }

        if ($this->echo) {
            echo 'Title: ' . $title . PHP_EOL;
            echo 'Scanning: ' . $url . PHP_EOL;
        }

        if ($this->saveAsScreenshots && $url && $title) {
            (new BrowserScreenShotService($this->saveDirectory))->screenshot($url, $title);
        }

        if (strlen($content) < 1) {
            return;
        }

        $this->htmlService->makeHtml($title, $content);
    }
}
