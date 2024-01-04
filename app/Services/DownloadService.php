<?php

namespace App\Services;

use App\Facades\Curl;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

class DownloadService
{
    public const VALID_FILE_EXTENSIONS = [
        'avchd',
        'avi',
        'bin',
        'doc',
        'docx',
        'flv',
        'gif',
        'jpg',
        'jpeg',
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
        'png',
        'ppt',
        'pptx',
        'pub',
        'qt',
        'swf',
        'txt',
        'wav',
        'webm',
        'wmv',
        'xls',
        'xlsx',
    ];

    protected string $saveDirectory;
    protected Client $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client();
    }
    public function downloadDocument(string $url, string $ext, string $saveDirectory): bool
    {
        $this->saveDirectory = trim($saveDirectory);
        $filename = $this->getFilenameFromUrl($url);

        if ($this->fileExists($filename)) {
            return true;
        }

        if (in_array(strtolower($ext), self::VALID_FILE_EXTENSIONS)) {
            echo 'Preparing to download ' . $url . PHP_EOL;
            if (! Curl::testUrl($url)) {
                echo 'Not found.'. PHP_EOL;
                return false;
            }
            echo "Downloading .{$ext} :" . $url . PHP_EOL;
            // Download file
            $this->save($url, $filename);

            return true;
        }

        return false;
    }

    public function save(string $documentUrl, string $filename): bool
    {
        $response = $this->httpClient->get($documentUrl);

        $contents = $response->getBody()->getContents();

        $filePath = $this->getFilePath($filename);

        Storage::disk('local')->put($this->saveDirectory . '/documents/' . $filename, $contents);

        return file_exists($filePath);
    }

    protected function fileExists(string $filename): bool
    {
        $filePath = $this->getFilePath($filename);

        return file_exists($filePath);
    }

    protected function getFilePath(string $filename): string
    {
        return Storage::path('/documents/' . $this->saveDirectory) . '/' . $filename;
    }


    public function getFilenameFromUrl(string $url): string
    {
        $parsed = parse_url($url);

        if (! isset($parsed['path'])) {
            return '';
        }

        $parts = pathinfo($parsed['path']);

        $filename = urldecode($parts['filename']);
        $ext = $parts['extension'] ?? '';

        return preg_replace('/[^\w]+/', '_', $filename) . '.' . $ext;
    }
}
