<?php

namespace App\Console\Commands;

use App\Services\WebCrawlerService;
use Illuminate\Console\Command;

class CrawlAndShoot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'web:crawl {--url=} {--screenshot=}';
    protected $saveAsScreenshots = true;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->option('url') ?? null;

        $this->saveAsScreenshots = (bool)$this->option('screenshot')
            ? $this->option('screenshot')
            : true;

        if ($url) {
            (new WebCrawlerService())->crawl($url, $this->saveAsScreenshots);
        }
    }
}
