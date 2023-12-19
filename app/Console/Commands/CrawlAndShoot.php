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
    protected $signature = 'web:crawl {--screenshot=}';
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
//        $url = 'https://www2.clarku.edu/difficultdialogues';
        $url = 'https://www2.clarku.edu/research/kaspersonlibrary/mtafund';

        $this->saveAsScreenshots = (bool)$this->option('screenshot')
            ? $this->option('screenshot')
            : true;

        (new WebCrawlerService())->crawl($url, $this->saveAsScreenshots);
    }
}
