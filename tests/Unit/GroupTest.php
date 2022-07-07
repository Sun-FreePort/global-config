<?php

namespace Yggdrasill\GlobalConfig\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Yggdrasill\GlobalConfig\Support\Facades\GlobalConfig;
use Yggdrasill\GlobalConfig\Tests\TestBase;

class GroupTest extends TestBase
{
    use RefreshDatabase;

    /*
     * 分组、前缀的查询
     *
     * @return void
     */
    public function test_group_select_test()
    {
        dd(GlobalConfig::students());
        $this->assertIsNumeric(0);
    }

    /*
     * 分组、前缀的批量动作（增）
     *
     * @return void
     */
    public function test_group_add_test()
    {
        $this->assertIsNumeric(0);
    }

    /*
     * 分组、前缀的批量动作（删）
     *
     * @return void
     */
    public function test_group_delete_test()
    {
        $this->assertIsNumeric(0);
    }

    /*
     * 分组、前缀的批量动作（改）
     *
     * @return void
     */
    public function test_group_change_test()
    {
        $this->assertIsNumeric(0);
    }
}
