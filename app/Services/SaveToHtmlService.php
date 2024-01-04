<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class SaveToHtmlService
{
    protected string $saveDirectory;
    protected string $rootUrl;

    public function __construct(string $rootUrl, string $saveDirectory)
    {
        $this->rootUrl = $rootUrl;
        $this->saveDirectory = trim($saveDirectory);
    }

    public function makeHtml(?string $title, string $content): void
    {
        $page = $title
            ? preg_replace('/[^\w]+/', '_', $title) . '.html'
            : 'Untitled.html';

        if (str_starts_with($page, '_')) {
            $page = substr($page, 1);
        }

        $filePath = $this->saveDirectory . '/'. $page;

        if (file_exists($filePath)) {
            return;
        }

        Storage::put($filePath, $content);
    }
}
