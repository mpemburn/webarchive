<?php

namespace App\Providers;

use App\Helpers\Curl;
use App\Helpers\Database;
use Illuminate\Support\ServiceProvider;
use Livewire\Component;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('curl',function() {
            return new Curl();
        });
        $this->app->bind('database',function(){
            return new Database();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Component::macro('notify', function ($message) {
            $this->dispatch('notify', $message);
        });
    }
}
