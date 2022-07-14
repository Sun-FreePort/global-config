<?php

namespace Yggdrasill\GlobalConfig\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Yggdrasill\GlobalConfig\Models\ConfigKey;
use Yggdrasill\GlobalConfig\Models\ConfigPrefix;
use Yggdrasill\GlobalConfig\Support\Facades\GlobalConfig;
use Yggdrasill\GlobalConfig\Tests\TestBase;

class ConfigTest extends TestBase
{
    use RefreshDatabase;

    /*
     * 配置细节的查询
     *
     * @return void
     */
    public function test_config_select_test()
    {
        $this->databaseFactory();

        $result = GlobalConfig::configsGet(GlobalConfig::fillKeys('group3', $this->prefix, 'TestVal')[0]);
        $this->assertCount(1, $result);
        $this->assertTrue(key_exists('type', $result[0]));
    }

    /*
     * 配置细节的批量动作（增）
     *
     * @return void
     * @depends test_config_select_test
     */
    public function test_config_add_test()
    {
        $this->databaseFactory();
        $key1 = GlobalConfig::fillKeys($this->group, $this->prefix, 'TestVC')[0];

        $result = GlobalConfig::configsGet($key1);
        $this->assertCount(0, $result);

        GlobalConfig::configsAdd($this->authID, [
            'group_id' => 1,
            'prefix_id' => 1,
            'key' => 'TestVC',
            'key_full' => $key1,
            'desc' => 'test desc 1',
            'type' => ConfigKey::TYPE_NUMBER,
            'rules' => '{}',
            'cache' => 1,
            'active' => 1,
            'visible' => 1,
        ]);
        $this->assertCount(1, GlobalConfig::configsGet($key1));
    }

    /*
     * 配置细节的批量动作（删）
     *
     * @return void
     * @depends test_config_select_test
     */
    public function test_config_delete_test()
    {
        $this->databaseFactory();

        $model = ConfigKey::query()->findOrFail(1);
        $key1 = $model->key_full;
        $model = ConfigKey::query()->findOrFail(2);
        $key2 = $model->key_full;
        $this->assertCount(2, GlobalConfig::configsGet($key1, $key2));

        GlobalConfig::configsDelete($this->authID, 1, 2);

        $this->assertCount(0, GlobalConfig::configsGet($key1, $key2));
    }

    /*
     * 配置细节的批量动作（改）
     *
     * @return void
     * @depends test_config_select_test
     */
    public function test_config_change_test()
    {
        $this->databaseFactory();

        $key1 = GlobalConfig::fillKeys('group2', $this->prefix, 'TestSharp')[0];
        $config = GlobalConfig::configsGet($key1)[0];
        $groupIDTarget = 1;
        $prefixIDTarget = 5;
        $this->assertTrue($groupIDTarget != $config['group_id']);
        $this->assertTrue($prefixIDTarget != $config['prefix_id']);

        GlobalConfig::configsChange($this->authID, [
            'id' => 3,
            'group_id' => $groupIDTarget,
            'prefix_id' => $prefixIDTarget,
            'key' => 'TestSharp',
            'key_full' => $key1,
            'desc' => 'test desc 1',
            'type' => ConfigKey::TYPE_NUMBER,
            'rules' => '{}',
            'cache' => 1,
            'active' => 1,
            'visible' => 0,
            'delete' => 0,
        ]);

        $prefix = GlobalConfig::groupsGetByID($groupIDTarget);
        $group = GlobalConfig::prefixesGetByID($prefixIDTarget);
        $key1 = GlobalConfig::fillKeys(array_pop($prefix)['key'], array_pop($group)['key'], 'TestSharp')[0];
        $config = GlobalConfig::configsGet($key1)[0];
        $this->assertTrue($groupIDTarget == $config['group_id']);
        $this->assertTrue($prefixIDTarget == $config['prefix_id']);
    }
}
