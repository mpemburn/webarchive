<?php

namespace App\Observers;

use App\Services\BrowserScreenShotService;
use App\Services\DownloadService;
use App\Services\SaveToHtmlService;
use DOMDocument;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlObservers\CrawlObserver;

class WebObserver extends CrawlObserver
{
    protected string $baseUrl;
    protected string $rootUrl;
    protected bool $saveAsScreenshots;
    protected bool $echo;

    protected BrowserScreenShotService $screenshotService;
    protected SaveToHtmlService $htmlService;

    public function __construct(string $baseUrl, bool $saveAsScreenshots = true, $echo = true)
    {
        $this->baseUrl = $baseUrl;
        $this->rootUrl = $this->getRootUrl($baseUrl);
        $this->saveAsScreenshots = $saveAsScreenshots;
        $this->echo = $echo;
        $saveDirectory = $this->makeSaveDirectory($baseUrl);

        if ($saveAsScreenshots) {
            $this->screenshotService = new BrowserScreenShotService($saveDirectory);
        }

        $this->htmlService = new SaveToHtmlService($this->rootUrl, $saveDirectory);
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
        if (!$linkText || stripos($url, $this->baseUrl) === false) {
            //return;
        }

        echo "Crawling... " . PHP_EOL;

        $saveDirectory = $this->makeSaveDirectory($this->baseUrl);

        $urlParts = explode('.', $url);
        $ext = strtolower(end($urlParts));

        if (in_array($ext, DownloadService::VALID_FILE_EXTENSIONS)) {
            echo "Downloading .{$ext} :" . $url . PHP_EOL;
            // Download .pdf
            (new DownloadService($saveDirectory))->save($url);

            return;
        }

        if ($this->echo) {
            echo 'Title: ' . $linkText . PHP_EOL;
            echo 'Scanning: ' . $url . PHP_EOL;
        }

        if ($this->saveAsScreenshots && $url && $linkText)  {
            (new BrowserScreenShotService($saveDirectory))->screenshot($url, $linkText);
        }

        $doc = new DOMDocument();
        $body = $response->getBody();

        if (strlen($body) < 1) {
            return;
        }

        @$doc->loadHTML($body);
        //# save HTML
        $content = $doc->saveHTML();
        $this->htmlService->makeHtml($linkText, $content);
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
        UriInterface $url, RequestException $requestException, ?UriInterface $foundOnUrl = null, ?string $linkText = null): void
    {
        //echo 'crawlFailed: ' . $url . PHP_EOL;
    }

    /**
     * Called when the crawl has ended.
     */
    public function finishedCrawling(): void
    {
    }

    protected function getRootUrl(string $baseUrl): string
    {
        $parsed = parse_url($baseUrl);

        return $parsed['scheme'] . '://' . $parsed['host'];
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
}
