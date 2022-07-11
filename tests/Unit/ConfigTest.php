<?php

namespace Yggdrasill\GlobalConfig\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Yggdrasill\GlobalConfig\Models\ConfigKey;
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
     */
    public function test_config_add_test()
    {
        $this->databaseFactory();

        GlobalConfig::configsAdd($this->authID, [
            'group_id' => 1,
            'prefix_id' => 1,
            'key' => 'TestVal',
            'key_full' => GlobalConfig::fillKeys($this->group, $this->prefix, 'TestVal')[0],
            'desc' => 'test desc 1',
            'type' => ConfigKey::TYPE_NUMBER,
            'rules' => '{}',
            'cache' => 1,
            'active' => 1,
            'visible' => 0,
        ]);
        $this->assertIsNumeric(0);
    }

    /*
     * 配置细节的批量动作（删）
     *
     * @return void
     */
    public function test_config_delete_test()
    {
        $this->databaseFactory();

        GlobalConfig::configsDelete($this->authID, 1, 2);
        $this->assertIsNumeric(0);
    }

    /*
     * 配置细节的批量动作（改）
     *
     * @return void
     */
    public function test_config_change_test()
    {
        $this->databaseFactory();

        GlobalConfig::configsChange($this->authID, [
            'id' => 1,
            'group_id' => 1,
            'prefix_id' => 1,
            'key' => 'TestVal',
            'key_full' => GlobalConfig::fillKeys($this->group, $this->prefix, 'TestVal')[0],
            'desc' => 'test desc 1',
            'type' => ConfigKey::TYPE_NUMBER,
            'rules' => '{}',
            'cache' => 1,
            'active' => 1,
            'visible' => 0,
            'delete' => 0,
        ]);
        $this->assertIsNumeric(0);
    }
}
