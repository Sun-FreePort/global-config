<?php

namespace Yggdrasill\GlobalConfig\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Yggdrasill\GlobalConfig\Tests\TestBase;

class ConfigTest extends TestBase
{
    use RefreshDatabase;

    /*
     * 配置的查询
     *
     * @return void
     */
    public function test_config_select_test()
    {
        $this->assertIsNumeric(0);
    }

    /*
     * 配置的批量动作（增）
     *
     * @return void
     */
    public function test_config_add_test()
    {
        $this->assertIsNumeric(0);
    }

    /*
     * 配置的批量动作（删）
     *
     * @return void
     */
    public function test_config_delete_test()
    {
        $this->assertIsNumeric(0);
    }

    /*
     * 配置的批量动作（改）
     *
     * @return void
     */
    public function test_config_change_test()
    {
        $this->assertIsNumeric(0);
    }
}
