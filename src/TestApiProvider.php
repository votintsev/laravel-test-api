<?php

namespace Votintsev\PublicSeeding;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;


class PublicSeedingServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (! $this->app->environment('production')) {

            $this->publishes([
                __DIR__.'/config/public-seeding.php' => config_path('public-seeding.php'),
            ]);

            $this->defineRoutes();

//            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/public-seeding.php', 'public-seeding'
        );
    }

    protected function defineRoutes()
    {
        // TODO route cache case
        // TODO test it

        // TODO why config not apply there?
        // dump(config('public-seeding.prefix'));

        Route::group([
            'prefix' => config('public-seeding.prefix', '_public-seeding'),
            'middleware' => config('public-seeding.middleware', ['guet:api', 'api']),
            'namespace' => 'Votintsev\PublicSeeding\Http\Controllers',
        ], function () {
            Route::post('/call-artisan', 'PublicSeedingController@callArtisan');
            Route::post('/call-factory', 'PublicSeedingController@callFactory');
        });
    }
}