<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Fix for cURL error 60: SSL certificate problem
        $this->app->singleton('guzzle.client', function ($app) {
            $config = [
                'verify' => base_path('cacert.pem'),
            ];

            return new Client($config);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
