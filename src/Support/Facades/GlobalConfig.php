<?php

namespace Yggdrasill\GlobalConfig\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Yggdrasill\GlobalConfig\GlobalConfigManager;

/**
 * @method static array groupsGet(string ...$keys)
 * @method static array groupsGetByID(int ...$ids)
 * @method static bool groupsAdd(int $authID, array ...$items)
 * @method static void groupsChange(int $authID, array ...$items)
 * @method static void groupsDeleteByKey(int $authID, string ...$keys)
 * @method static array prefixesGet(string ...$keys)
 * @method static array prefixesGetByID(int ...$ids)
 * @method static bool prefixesAdd(int $authID, array ...$items)
 * @method static void prefixesChange(int $authID, array ...$items)
 * @method static void prefixesDeleteByKey(int $authID, string ...$keys)
 *
 * @method static array configsGet(string ...$keys)
 * @method static array configsGetByGroupName(string ...$group)
 * @method static array configsGetByGroupID(int ...$group)
 * @method static bool configsAdd(int $authID, array ...$items)
 * @method static void configsChange(int $authID, array ...$items)
 * @method static void configsDelete(int $authID, int ...$itemIDs)
 *
 * @method static array values(array $itemIDs) [[Key => Value], ...]
 * @method static array valuesChange(int $authID, array $items) [[Key => Value], ...]
 *
 * @method static string|string[] fillKeys (string $group, string $prefix, string ...$keys)
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
