<?php

use App\Events\WebEntityProcessed;
use App\Facades\Curl;
use App\Facades\Database;
use App\Models\WebArchiveTest;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/dev', function () {
//    $directories = Storage::allDirectories('difficultdialogues');
//    collect($directories)->each(function ($dir) {
//        !d(Storage::disk('local')->files($dir));
//    });
    $tree = collect();
    $paths = collect();
    $root = Storage::path('difficultdialogues');
    collect(File::allFiles($root))->each(function ($file) use (&$tree, &$paths) {
        $path = $file->getRelativePath() ?: '/';
        $paths->push($path);
        if (! $tree->has($path)) {
            $tree->put($path, collect());
        }
        $tree->get($path)->push($file);
    });

    !d($paths->unique()->toArray());
    // Do what thou wilt
});

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('archiver', 'archiver')
    ->middleware(['auth'])
    ->name('archiver');

require __DIR__.'/auth.php';
