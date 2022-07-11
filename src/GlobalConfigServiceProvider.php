<?php

namespace Yggdrasill\GlobalConfig;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Yggdrasill\GlobalConfig\Cache\GlobalConfigCacheManager;

class GlobalConfigServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
//        $this->registerRoutes();
//        $this->registerResources();

        $this->registerMigrateV1();
    }

    /**
     * Register the 1 version set all migrate table.
     *
     * @return void
     */
    protected function registerMigrateV1()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Register the GlobalConfigManager routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        Route::group([
            'namespace' => 'Yggdrasill\GlobalConfig\Http\Controllers',
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    /**
     * Register the GlobalConfigManager routes.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__ . '/../config/global-config.php' => config_path('global-config.php'),
        ]);
    }

    /**
     * Register the GlobalConfigManager resources.
     *
     * @return void
     */
    protected function registerResources()
    {
        // FIXME: fill something.
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        app()->singleton('globalConfig', function ($app) {
            return new GlobalConfigManager(new GlobalConfigCacheManager($app));
        });
    }
}
