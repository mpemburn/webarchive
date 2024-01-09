<?php

namespace App\Services;

use Illuminate\Support\Collection;

class DocumentService
{
    const HTML_EXTENSIONS = [
        '.asp',
        '.aspx',
        '.cfm',
        '.html',
        '.htm',
        '.php',
    ];

    public function getRootUrl(string $baseUrl): string
    {
        $parsed = parse_url($baseUrl);

        return $parsed['scheme'] . '://' . $parsed['host'];
    }

    public function getPageName(string $url): string
    {
        $parsed = parse_url($url);

        if (! isset($parsed['path'])) {
            return '';
        }

        return $parsed['path'] ? pathinfo($parsed['path'])['basename'] : '';
    }

    public function getDocumentTitle(string $documentBody, string $url, Collection $titles): string
    {
        $title = str_replace(self::HTML_EXTENSIONS, '', $this->getPageName($url));

        collect(explode("\n", $documentBody))
            ->each(function ($line) use (&$titles) {
                if (stripos($line, '<title>') !== false) {
                    $foundTitle = preg_replace('/.*?<title>(.*?)<\/title>.*/', '$1', $line);
                    $titles->push($foundTitle);
                    if (! $titles->contains($foundTitle)) {
                        $title = $foundTitle;
                    }
                }
            });

        return preg_replace('/[^\w]+/', '_', $title);
    }

    public function getExtension(string $url): string
    {
        $urlParts = explode('.', $url);

        return strtolower(end($urlParts));
    }
}
