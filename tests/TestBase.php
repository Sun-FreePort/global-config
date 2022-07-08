<?php

namespace Yggdrasill\GlobalConfig\Tests;

use Illuminate\Support\Facades\Redis;
use Orchestra\Testbench\TestCase;

abstract class TestBase extends TestCase
{
    protected $group = 'testGroup';
    protected $prefix = 'testPrefix';

    protected function setUp(): void
    {
        parent::setUp();

        // TODO 预填充数据
    }

    /**
     * Fill your provider before test
     * @param \Illuminate\Foundation\Application $app
     * @return string[]
     */
    protected function getPackageProviders($app): array
    {
        return [
            'Yggdrasill\GlobalConfig\GlobalConfigServiceProvider',
        ];
    }
}
