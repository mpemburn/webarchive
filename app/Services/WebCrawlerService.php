<?php

namespace App\Services;

use GuzzleHttp\RequestOptions;
use Spatie\Crawler\Crawler;
use App\Observers\WebObserver;
class WebCrawlerService
{
    public function crawl(string $url, bool $saveAsScreenshots = true)
    {
        $options = [RequestOptions::ALLOW_REDIRECTS => true, RequestOptions::TIMEOUT => 30];
        //# initiate crawler
        Crawler::create($options)
            ->acceptNofollowLinks()
            ->ignoreRobots()
            ->setCrawlObserver(new WebObserver($url, $saveAsScreenshots))
            ->setMaximumResponseSize(1024 * 1024 * 2) // 2 MB maximum
            ->setDelayBetweenRequests(500)
            ->startCrawling($url);

        return true;
    }
}
