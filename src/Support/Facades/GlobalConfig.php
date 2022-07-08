<?php

namespace Yggdrasill\GlobalConfig\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Yggdrasill\GlobalConfig\GlobalConfigManager;

/**
 * @method static array groupsGet(string ...$key)
 * @method static bool groupsAdd(array ...$items)
 * @method static void groupsChange(array ...$items)
 * @method static void groupsDelete(array $itemIDs)
 *
 * @method static array configsGet(string ...$key)
 * @method static array configsGetByGroup(string ...$group)
 * @method static bool configsAdd(array ...$items)
 * @method static void configsChange(array ...$items)
 * @method static void configsDelete(array $itemIDs)
 *
 * @method static string[] fillKeys (string $group, string $prefix, string ...$keys)
 */
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
