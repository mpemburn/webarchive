<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

class DownloadService
{
    public const VALID_FILE_EXTENSIONS = [
        'avchd',
        'avi',
        'doc',
        'docx',
        'flv',
        'm4p',
        'm4v',
        'mov',
        'mp2',
        'mp3',
        'mp4',
        'mpe',
        'mpeg',
        'mpg',
        'mpv',
        'ogg',
        'pdf',
        'ppt',
        'pptx',
        'pub',
        'qt',
        'swf',
        'webm',
        'wmv',
    ];

    protected string $saveDirectory;
    protected Client $httpClient;

    public function __construct(string $saveDirectory)
    {
        $this->saveDirectory = trim($saveDirectory);

        $this->httpClient = new Client();
    }

    public function save(string $documentUrl): bool
    {
        $response = $this->httpClient->get($documentUrl);

        $contents = $response->getBody()->getContents();

        $filename = $this->getFilenameFromUrl($documentUrl);
        $filePath = Storage::path('/documents/' . $this->saveDirectory) . '/' . $filename;

        Storage::disk('local')->put($this->saveDirectory . '/documents/' . $filename, $contents);

        return file_exists($filePath);
    }

    protected function getFilenameFromUrl(string $url): string
    {
        $parsed = parse_url($url);
        $parts = explode('/', $parsed['path']);

        return array_pop($parts);
    }
}
