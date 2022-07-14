<?php

namespace Yggdrasill\GlobalConfig\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Yggdrasill\GlobalConfig\Exception\GlobalConfigException;
use Yggdrasill\GlobalConfig\Models\ConfigActionLog;
use Yggdrasill\GlobalConfig\Models\ConfigPrefix;
use Yggdrasill\GlobalConfig\Support\Facades\GlobalConfig;
use Yggdrasill\GlobalConfig\Tests\TestBase;

class LogTest extends TestBase
{
    use RefreshDatabase;

    /*
     * 日志记录
     *
     * @return void
     */
    public function test_log_add_test()
    {
        $groupDemo = [[
            'key' => $this->group,
            'name' => '测试分组',
            'type' => ConfigPrefix::TYPE_GROUP,
        ], [
            'key' => 'group2',
            'name' => '测试分组',
            'type' => ConfigPrefix::TYPE_GROUP,
        ]];

        GlobalConfig::groupsAdd($this->authID, ...$groupDemo);
        $this->assertDatabaseCount('gc_config_action_logs', 2);
        $this->assertDatabaseHas('gc_config_action_logs', [
            'author_id' => $this->authID,
            'actions' => ConfigActionLog::ACTION_ADD,
        ]);
        $this->assertDatabaseMissing('gc_config_action_logs', [
            'author_id' => $this->authID,
            'actions' => ConfigActionLog::ACTION_DELETE,
        ]);

        GlobalConfig::groupsDeleteByKey($this->authID, $this->group);
        $this->assertDatabaseHas('gc_config_action_logs', [
            'author_id' => $this->authID,
            'actions' => ConfigActionLog::ACTION_DELETE,
        ]);
    }
}
