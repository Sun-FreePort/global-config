<?php

namespace Yggdrasill\GlobalConfig;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class GlobalConfigServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
//        $this->registerRoutes();
//        $this->registerConfig();
//        $this->registerResources();
    }

    /**
     * Register the GlobalConfig routes.
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
     * Register the GlobalConfig routes.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('config.php'),
        ]);
    }

    /**
     * Register the GlobalConfig resources.
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
//        $this->configure();
//        $this->registerServices();
    }

    // TODO
    /**
     * Setup the configuration for GlobalConfig.
     *
     * @return void
     */
    protected function configure()
    {
//        $this->mergeConfigFrom(
//            __DIR__ . '/../config/horizon.php', 'horizon'
//        );
//
//        GlobalConfig::use(config('horizon.use', 'default'));
    }

    // TODO
    /**
     * Register GlobalConfig's services in the container.
     *
     * @return void
     */
    protected function registerServices()
    {
//        foreach ($this->serviceBindings as $key => $value) {
//            is_numeric($key)
//                ? $this->app->singleton($value)
//                : $this->app->singleton($key, $value);
//        }
    }
}
