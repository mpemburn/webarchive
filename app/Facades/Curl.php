<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
* @method static bool testUrl(string $url);
* @method static function isRedirected(string $url): bool
* @method static string getContents(string $url, bool $noFollow = true);
* @method static array getContentsAsArray(string $url);

 */
class Curl extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'curl';
    }

}
