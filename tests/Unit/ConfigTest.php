<?php

namespace Yggdrasill\GlobalConfig\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Yggdrasill\GlobalConfig\Support\Facades\GlobalConfig;
use Yggdrasill\GlobalConfig\Tests\TestBase;

class ConfigTest extends TestBase
{
    use RefreshDatabase;

//    /*
//     * 配置细节的查询
//     *
//     * @return void
//     */
//    public function test_config_select_test()
//    {
//        $result = GlobalConfig::configsGet('testGroup:testPrefix:testKey1');
//        $this->assertSameSize($result, ['keys', 'value']);
//        $this->assertEquals('testValue1', $result);
//    }
//
//    /*
//     * 配置细节的批量动作（增）
//     *
//     * @return void
//     */
//    public function test_config_add_test()
//    {
//        GlobalConfig::configsAdd();
//        $this->assertIsNumeric(0);
//    }
//
//    /*
//     * 配置细节的批量动作（删）
//     *
//     * @return void
//     */
//    public function test_config_delete_test()
//    {
//        GlobalConfig::configsDelete();
//        $this->assertIsNumeric(0);
//    }
//
//    /*
//     * 配置细节的批量动作（改）
//     *
//     * @return void
//     */
//    public function test_config_change_test()
//    {
//        GlobalConfig::configsChange();
//        $this->assertIsNumeric(0);
//    }
}
