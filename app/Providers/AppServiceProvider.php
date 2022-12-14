<?php

namespace App\Providers;

use App\Contracts\DigitalTV;
use App\Services\Iptv\Megogo;
use App\Services\Iptv\Trinity;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //App\Providers\TelescopeServiceProvider::class;
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        $this->app->bind(DigitalTV::class, function ($app){
            return new Megogo();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
