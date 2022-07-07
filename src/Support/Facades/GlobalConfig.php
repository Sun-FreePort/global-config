<?php

namespace Yggdrasill\GlobalConfig\Support\Facades;

use Illuminate\Support\Facades\Facade;

class GlobalConfig extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'globalConfig';
    }
}
