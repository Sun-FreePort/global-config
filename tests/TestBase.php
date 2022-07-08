<?php

namespace Yggdrasill\GlobalConfig\Tests;

use Illuminate\Support\Facades\Redis;
use Orchestra\Testbench\TestCase;
use function Composer\Autoload\includeFile;

abstract class TestBase extends TestCase
{
    protected $group = 'testGroup';
    protected $prefix = 'testPrefix';

    protected function setUp(): void
    {
        parent::setUp();

        // TODO 预填充数据
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('global-config', include __DIR__ . '/../config/global-config.php');
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
//        $this->artisan('clear-compiled')->run();
//        $this->artisan('optimize:clear')->run();
//        $this->artisan('cache:clear')->run();
//        $this->artisan('optimize')->run();
        $this->artisan('migrate')->run();
    }

    /**
     * Fill your provider before test
     * @param \Illuminate\Foundation\Application $app
     * @return string[]
     */
    protected function getPackageProviders($app): array
    {
        return [
            'Yggdrasill\GlobalConfig\GlobalConfigServiceProvider',
        ];
    }
}
