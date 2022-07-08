<?php

namespace Yggdrasill\GlobalConfig\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Yggdrasill\GlobalConfig\Exception\GlobalConfigException;
use Yggdrasill\GlobalConfig\Models\ConfigPrefix;
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
        $this->databaseFactory();

        $oneGroup = GlobalConfig::groupsGet($this->group);
        $this->assertArrayHasKey($this->group, $oneGroup);
        $this->assertCount(3, $oneGroup[$this->group]);
    }

    /*
     * 分组、前缀的查询
     *
     * @return void
     */
    public function test_group_select_undefined_test()
    {
        $oneGroup = GlobalConfig::groupsGet($this->group);
        $this->assertCount(0, $oneGroup);
    }

    /*
     * 分组、前缀的批量动作（增）
     *
     * @return void
     */
    public function test_group_add_test()
    {
        $groupDemo = [[
            'key' => $this->group,
            'name' => '测试分组',
        ], [
            'key' => 'group2',
            'name' => '测试分组',
        ]];
        $result = GlobalConfig::groupsAdd(...$groupDemo);
        $this->assertTrue($result);
    }

    /*
     * 分组、前缀的批量动作（增）
     *
     * @depends test_group_add_test
     * @return void
     */
    public function test_group_add_key_exists_test()
    {
        $this->databaseFactory();

        $groupDemo = [
            'key' => $this->group,
            'name' => '测试分组',
        ];
        $this->expectException(GlobalConfigException::class);
        GlobalConfig::groupsAdd($groupDemo);
    }

    /*
     * 分组、前缀的批量动作（改）
     *
     * @return void
     */
    public function test_group_change_test()
    {
        $this->databaseFactory();

        $groupDemo = [[
            'id' => 1,
            'key' => 'fyiuofeiwu',
            'name' => '测试分组213132',
        ]];

        GlobalConfig::groupsChange(...$groupDemo);
        $this->assertIsNumeric(0); // 无意义断言，表示流程正常
    }

    /*
     * 分组、前缀的批量动作（改）
     *
     * @return void
     */
    public function test_group_change_not_exists_test()
    {
        $groupDemo = [[
            'id' => 154242,
            'key' => 'fyiuofeiwu',
            'name' => '测试分组213132',
        ]];

        GlobalConfig::groupsChange(...$groupDemo);
        $this->assertIsNumeric(0);
    }

    /*
     * 分组、前缀的批量动作（改）
     *
     * @return void
     */
    public function test_group_change_unsafe_column_test()
    {
        $groupDemo = [[
            'id' => 154242,
            'key' => 'fyiuofeiwu',
            'name' => '测试分组213132',
            'created_at' => 120947902,
            'fiohwihv' => 'iuwfgwi',
        ]];

        $this->expectException(GlobalConfigException::class);
        GlobalConfig::groupsChange(...$groupDemo);
    }

    /*
     * 分组、前缀的批量动作（删）
     *
     * @depends test_group_select_test
     * @return void
     */
    public function test_group_delete_test()
    {
        $this->databaseFactory();
        $IDs = [$this->group, '2424', 'group2'];

        $result1 = count(GlobalConfig::groupsGet(...$IDs));
        GlobalConfig::groupsDelete($IDs);
        $result2 = count(GlobalConfig::groupsGet(...$IDs));

        $this->assertLessThan($result1, $result2);
        $this->assertIsNumeric(0);
    }
}
