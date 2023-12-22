<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebArchiveTest extends ArchiveTest
{
    use HasFactory;

    public $table = 'web_archive_test';
}
