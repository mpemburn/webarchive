<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ArchiveTest extends Model
{
    use HasFactory;

    public const ERROR_CODES = [
        0 => 'n/a',
        301 => '301 Moved',
        302 => '302 Gone ',
        401 => '401 Unauthorized',
        404 => '404 Not Found',
        410 => 'Gone',
        500 => '500 Fatal Error',
    ];

    protected $fillable = [
        'server',
        'category',
        'web_root',
        'index_url',
        'page_title',
        'redirect_url',
        'error_code',
    ];

    public function getErrorCodeAttribute($code)
    {
        return self::ERROR_CODES[$code] ?? 'okay';
    }

    public function getRedirectUrlAttribute($url)
    {
        return $url ?? 'n/a';
    }

    public function getPageTitleAttribute($title)
    {
        return $title && $title !== 'Untitled Document'
            ? $title
            : $this->buildTitleFromIndexPath();
    }

    protected function buildTitleFromIndexPath()
    {
        $parsed = pathinfo(parse_url($this->web_root)['path'])['dirname'];

        return ucwords(str_replace('/', ' ', $parsed));
    }
}
