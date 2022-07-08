<?php

namespace Yggdrasill\GlobalConfig\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Yggdrasill\GlobalConfig\Exception\GlobalConfigException;
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
        $oneGroup = GlobalConfig::groupsGet($this->group);
        $this->assertCount(4, $oneGroup);
    }

    /*
     * 分组、前缀的批量动作（增）
     *
     * @return void
     */
    public function test_group_add_test()
    {
        $groupDemo = [
            'key' => $this->group,
            'name' => '测试分组',
            'type' => 'key42',
        ];
        $result = GlobalConfig::groupsAdd($groupDemo);
        $this->assertTrue($result);
    }

    /*
     * 分组、前缀的批量动作（增）
     *
     * @depends test_group_add_test
     * @return void
     */
    public function test_group_add_failed_test()
    {
        $groupDemo = [
            'key' => $this->group,
            'name' => '测试分组',
            'type' => 'key42',
        ];
        $this->expectException(GlobalConfigException::class);
        GlobalConfig::groupsAdd($groupDemo);
    }

//    /*
//     * 分组、前缀的批量动作（改）
//     *
//     * @return void
//     */
//    public function test_group_change_test()
//    {
//        GlobalConfig::groupsChange();
//        $this->assertIsNumeric(0);
//    }
//
//    /*
//     * 分组、前缀的批量动作（删）
//     *
//     * @return void
//     */
//    public function test_group_delete_test()
//    {
//        GlobalConfig::groupsDelete();
//        $this->assertIsNumeric(0);
//    }
}
