<?php

namespace Yggdrasill\GlobalConfig\Tests\Unit;

use Yggdrasill\GlobalConfig\Exception\GlobalConfigException;
use Yggdrasill\GlobalConfig\Support\Facades\GlobalConfig;
use Yggdrasill\GlobalConfig\Tests\TestBase;

class SupportTest extends TestBase
{
    /*
     * 键名拼接方法
     *
     * @return void
     */
    public function test_fill_keys_test()
    {
        $i = ['key1', 'Key2', 'key_3', 'KEY:4'];
        $result = GlobalConfig::fillKeys($this->group, $this->prefix, ...$i);

        $this->assertSameSize($i, $result);
        $this->assertIsArray($result);
        $this->assertIsBool(in_array("{$this->group}:{$this->prefix}:KEY:4", $result));
    }

    /*
     * 键名拼接失败
     *
     * @return void
     */
    public function test_fill_keys_failed_by_group_test()
    {
        $i = ['key1', 'Key2', 'key_3', 'KEY:4'];
        $this->expectException(GlobalConfigException::class);
        GlobalConfig::fillKeys('test:group', $this->prefix, ...$i);
    }

    /*
     * 键名拼接失败
     *
     * @return void
     */
    public function test_fill_keys_failed_by_prefix_test()
    {
        $i = ['key1', 'Key2', 'key_3', 'KEY:4'];
        $this->expectException(GlobalConfigException::class);
        GlobalConfig::fillKeys($this->group, 'test:prefix', ...$i);
    }
}
