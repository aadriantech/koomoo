<?php

namespace App\Providers;

use App\Helpers\ArrayHelper;
use App\Services\GithubUserService;
use App\Services\ApiService;
use App\Services\StorageService;
use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
//        $this->app->singleton(GithubUserService::class, function () {
        $this->app->bind(GithubUserService::class, function () {
            $storage = new StorageService(new ApiService(new Client()), new ArrayHelper());

            return new GithubUserService($storage);
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
