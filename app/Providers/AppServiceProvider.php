<?php

namespace App\Providers;

use App\Services\Anthropic\AnthropicClient;
use App\Services\TravelConnection\TravelConnectionClient;
use GuzzleHttp\Client;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AnthropicClient::class, function (Application $app) {
            return new AnthropicClient(
                $app->make('config')->get('services.anthropic.api_key'),
            );
        });

        $this->app->singleton(TravelConnectionClient::class, function(Application $app) {
            $config = $app->make('config')->get('services.tc');

            $client = new TravelConnectionClient(new Client($config['client']));
            $client->setToken($config['api_key']);

            return $client;
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
