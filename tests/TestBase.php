<?php

namespace Yggdrasill\GlobalConfig\Tests;

use Illuminate\Support\Facades\Redis;
use Orchestra\Testbench\TestCase;

abstract class TestBase extends TestCase
{
    protected function SetUp(): void
    {
        parent::setUp();

        app()->bind('globalConfig', function () {
            return new \Yggdrasill\GlobalConfig\GlobalConfigManager;
        });
    }
}
