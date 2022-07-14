<?php

namespace Yggdrasill\GlobalConfig\Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Yggdrasill\GlobalConfig\Exception\GlobalConfigException;
use Yggdrasill\GlobalConfig\Models\ConfigKey;
use Yggdrasill\GlobalConfig\Support\Facades\GlobalConfig;
use Yggdrasill\GlobalConfig\Tests\TestBase;

class ValueTest extends TestBase
{
    use RefreshDatabase;

    /*
     * 配置的查询
     *
     * @return void
     */
    public function test_config_select_test()
    {
        $this->databaseFactory();

        $key1 = GlobalConfig::fillKeys('group3', $this->prefix, 'TestVal')[0];
        $result = GlobalConfig::values($key1);
        $this->assertEquals(1, count($result));
        $this->assertEquals($key1, array_key_first($result));
    }

    /*
     * 配置的批量动作
     *
     * @return void
     */
    public function test_config_add_test()
    {
        $this->databaseFactory();

        $key1 = GlobalConfig::fillKeys('group3', $this->prefix, 'TestVal')[0];
        $val = GlobalConfig::values($key1)[$key1];
        GlobalConfig::valuesChange($this->authID, [$key1 => ++$val]);
        $this->assertEquals($val, GlobalConfig::values($key1)[$key1]);
    }

    /*
     * 配置值类型不符
     *
     * @return void
     */
    public function test_failed_value_test()
    {
        $this->databaseFactory();

        $this->expectException(GlobalConfigException::class);
        GlobalConfig::valuesChange($this->authID,
            [GlobalConfig::fillKeys('group3', $this->prefix, 'TestVal')[0] => '42aaaaaaaa']);
    }
}
