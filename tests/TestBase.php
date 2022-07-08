<?php

namespace Yggdrasill\GlobalConfig\Tests;

use Illuminate\Support\Facades\Redis;
use Orchestra\Testbench\TestCase;
use Yggdrasill\GlobalConfig\Models\ConfigPrefix;
use function Composer\Autoload\includeFile;

abstract class TestBase extends TestCase
{
    protected $group = 'testGroup';
    protected $prefix = 'testPrefix';

    protected function setUp(): void
    {
        parent::setUp();

        Redis::flushdb();
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('global-config', include __DIR__ . '/../config/global-config.php');
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

    protected function databaseFactory()
    {
        ConfigPrefix::insert([[
            'key' => $this->group,
            'name' => '测试分组1',
            'type' => ConfigPrefix::TYPE_GROUP,
        ], [
            'key' => 'group2',
            'name' => '测试分组2',
            'type' => ConfigPrefix::TYPE_GROUP,
        ], [
            'key' => 'group3',
            'name' => '测试分组3',
            'type' => ConfigPrefix::TYPE_GROUP,
        ]]);
    }
}
