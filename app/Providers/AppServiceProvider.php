<?php
declare(strict_types=1);

namespace App\Providers;

use App\Helpers\ArrayHelper;
use App\Services\GithubUserService;
use App\Services\ApiService;
use App\Services\RedisService;
use App\Services\StorageService;
use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redis;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(GithubUserService::class, function () {
            $storage = new StorageService(
                new ApiService(new Client()),
                new ArrayHelper(),
                new RedisService(new Redis())
            );

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
