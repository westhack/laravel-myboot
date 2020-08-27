<?php

namespace App\Providers;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class GuzzleProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('guzzle', function () {
            $config = isset($this->app['config']['guzzle']) ? $this->app['config']['guzzle'] : [];
            return new Client($config);
        });
    }
}
