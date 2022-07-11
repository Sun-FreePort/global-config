<?php

namespace Yggdrasill\GlobalConfig\Tests;

use Illuminate\Support\Facades\Redis;
use League\Flysystem\Config;
use Orchestra\Testbench\TestCase;
use Yggdrasill\GlobalConfig\Models\ConfigKey;
use Yggdrasill\GlobalConfig\Models\ConfigPrefix;
use Yggdrasill\GlobalConfig\Models\ConfigValue;
use Yggdrasill\GlobalConfig\Support\Facades\GlobalConfig;
use function Composer\Autoload\includeFile;

abstract class TestBase extends TestCase
{
    protected $group = 'testGroup';
    protected $prefix = 'testPrefix';
    protected $authID = 42;

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
        $groups = [[
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
        ]];
        $prefixes = [[
            'key' => $this->prefix,
            'name' => '测试前缀1',
            'type' => ConfigPrefix::TYPE_PREFIX,
        ], [
            'key' => 'prefix2',
            'name' => '测试前缀2',
            'type' => ConfigPrefix::TYPE_PREFIX,
        ]];
        ConfigPrefix::insert(array_merge($groups, $prefixes));

        ConfigKey::insert([[
            'group_id' => 3,
            'prefix_id' => count($groups) + 1,
            'key' => 'TestVal',
            'key_full' => GlobalConfig::fillKeys($groups[2]['key'], $prefixes[0]['key'], 'TestVal')[0],
            'desc' => 'test desc 1',
            'type' => ConfigKey::TYPE_NUMBER,
            'rules' => '{}',
            'cache' => 1,
            'active' => 1,
            'visible' => 1,
        ], [
            'group_id' => 3,
            'prefix_id' => count($groups) + 2,
            'key' => 'TestBiu',
            'key_full' => GlobalConfig::fillKeys($groups[2]['key'], $prefixes[1]['key'], 'TestBiu')[0],
            'desc' => 'test desc 2',
            'type' => ConfigKey::TYPE_STRING_SHORT,
            'rules' => '{}',
            'cache' => 1,
            'active' => 1,
            'visible' => 1,
        ], [
            'group_id' => 2,
            'prefix_id' => count($groups) + 1,
            'key' => 'TestSharp',
            'key_full' => GlobalConfig::fillKeys($groups[1]['key'], $prefixes[0]['key'], 'TestSharp')[0],
            'desc' => 'test desc 3',
            'type' => ConfigKey::TYPE_STRING_LONG,
            'rules' => '{}',
            'cache' => 1,
            'active' => 1,
            'visible' => 1,
        ]]);

        ConfigValue::insert([[
            'key' => 'TestVal',
            'key_full' => GlobalConfig::fillKeys($groups[2]['key'], $prefixes[0]['key'], 'TestVal')[0],
            'key_id' => 1,
            'value' => 247.2,
            'author_by' => 42,
        ], [
            'key' => 'TestBiu',
            'key_full' => GlobalConfig::fillKeys($groups[2]['key'], $prefixes[1]['key'], 'TestBiu')[0],
            'key_id' => 2,
            'value' => 'value is string!',
            'author_by' => 42,
        ], [
            'key' => 'TestSharp',
            'key_full' => GlobalConfig::fillKeys($groups[1]['key'], $prefixes[0]['key'], 'TestSharp')[0],
            'key_id' => 3,
            'value' => '高端的美食，往往采用最朴素的做工……忙碌了两个小时的孙师傅，点了一份美团外卖。',
            'author_by' => 42,
        ], ]);
    }
}
