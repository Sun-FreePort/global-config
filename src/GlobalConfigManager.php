<?php

namespace Yggdrasill\GlobalConfig;

use Yggdrasill\GlobalConfig\Cache\GlobalConfigCacheManager;
use Yggdrasill\GlobalConfig\Exception\GlobalConfigException;
use Yggdrasill\GlobalConfig\Models\ConfigGroup;
use Yggdrasill\GlobalConfig\Models\ConfigValue;

class GlobalConfigManager extends GlobalConfigBase
{
    public function groupsGet(string ...$key): array
    {
        $data = $this->cache->gets(...$key);
        $needFindNames = [];
        $result = [];

        for ($i = count($key) - 1; $i >= 0; $i--) {
            ($data[$i] == null)
                ? array_push($needFindNames, $data[$i])
                : $result[$key[$i]] = $data[$i];
        }

        $dbResult = ConfigGroup::query()
            // TODO 明确一下参数
            ->where('type', ConfigGroup::TYPE_GROUP)
            ->whereIn('name', $needFindNames)
            ->get()
            ->toArray();
        foreach ($dbResult as $item) {
            $result[$item['key']] = $item;
            unset($result[$item['key']]['key']);
        }

        return $result;
    }

    public function groupsAdd(array $items): int
    {
        return 0;
    }

    public function groupsChange(array $items): bool
    {
        return true;
    }

    public function groupsDelete(array $itemIDs): bool
    {
        return true;
    }

    public function configsGetByGroup(string ...$groups): array
    {
        return [];
    }

    public function configsGet(string ...$key): array
    {
        return [];
    }

    public function configsAdd(array $items): int
    {
        return 0;
    }

    public function configsChange(array $items): bool
    {
        return true;
    }

    public function configsDelete(array $itemIDs): bool
    {
        return true;
    }

    /**
     * 获得完整键名
     * @param string $group
     * @param string $prefix
     * @param string ...$keys
     * @return array
     * @throws GlobalConfigException
     */
    public function fillKeys(string $group, string $prefix, string ...$keys): array
    {
        $result = [];
        if (strpos($group, ':')) throw new GlobalConfigException('组名不应包含 : 符号');
        if (strpos($prefix, ':')) throw new GlobalConfigException('前缀不应包含 : 符号');

        foreach ($keys as $key) {
            array_push($result, "{$group}:{$prefix}:{$key}");
        }

        return $result;
    }
}
