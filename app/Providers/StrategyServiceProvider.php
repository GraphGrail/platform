<?php

namespace App\Providers;

use App\Domain\Strategy\Provider;
use App\Domain\Strategy\StrategyProvider;
use Illuminate\Support\ServiceProvider;

class StrategyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(StrategyProvider::class, function ($app) {
            return new Provider(config('strategies'));
        });
    }
}
