<?php

namespace Yggdrasill\GlobalConfig;

use Yggdrasill\GlobalConfig\Cache\GlobalConfigCacheManager;
use Yggdrasill\GlobalConfig\Exception\GlobalConfigException;
use Yggdrasill\GlobalConfig\Models\ConfigPrefix;
use Yggdrasill\GlobalConfig\Models\ConfigValue;

class GlobalConfigManager extends GlobalConfigBase
{
    /**
     * @var array
     */
    public $fillSelect;
    public $fillCreate;
    public $fillUpdate;

    public function __construct(GlobalConfigCacheManager $cache)
    {
        parent::__construct($cache);
        $this->fillUpdate = ['id', 'key', 'name'];
        $this->fillCreate = ['key', 'name'];
        $this->fillSelect = ['id', 'key', 'name', 'type'];
    }

    public function groupsGet(string ...$key): array
    {
        $data = $this->cache->gets(...$key);
        $needFindNames = [];
        $result = [];

        for ($i = count($key) - 1; $i >= 0; $i--) {
            ($data[$i] == null)
                ? array_push($needFindNames, $key[$i])
                : $result[$key[$i]] = $data[$i];
        }

        $dbResult = ConfigPrefix::query()
            ->select($this->fillSelect)
            ->where('type', ConfigPrefix::TYPE_GROUP)
            ->whereIn('key', $needFindNames)
            ->get()
            ->toArray();
        foreach ($dbResult as $item) {
            $result[$item['key']] = $item;
            unset($result[$item['key']]['key']);

            $this->cache->sets([
                $item['key'] => json_encode($result[$item['key']]),
            ]);
        }

        return $result;
    }

    public function groupsAdd(array ...$items): bool
    {
        return $this->prefixAdd(ConfigPrefix::TYPE_GROUP, ...$items);
    }

    public function groupsChange(array ...$items): void
    {
        $this->prefixChange(ConfigPrefix::TYPE_GROUP, ...$items);
    }

    public function prefixesAdd(array ...$items): bool
    {
        return $this->prefixAdd(ConfigPrefix::TYPE_PREFIX, ...$items);
    }

    public function prefixesChange(array ...$items): void
    {
        $this->prefixChange(ConfigPrefix::TYPE_PREFIX, ...$items);
    }

    /**
     * 前缀表新增方法。
     * @param string $type
     * @param mixed ...$items
     * @return bool
     * @throws GlobalConfigException
     */
    private function prefixAdd(string $type, array ...$items): bool
    {
        $keys = [];
        $creates = [];
        $i = 0;
        $ts = time();
        $length = count($this->fillCreate);
        foreach ($items as $item) {
            foreach ($this->fillCreate as $key) {
                if (count($item) != $length) {
                    throw new GlobalConfigException("Group add failed: array length not support.");
                }

                if (!isset($item[$key])) {
                    throw new GlobalConfigException("Group add failed: {$key} not found.");
                }
                $creates[$i][$key] = $item[$key];
            }
            $creates[$i]['type'] = $type;
            $creates[$i]['created_at'] = $ts;
            $creates[$i]['updated_at'] = $ts;
            $creates[$i]['deleted_at'] = $ts;
            $keys[$i] = $item['key'];
        }

        $checks = ConfigPrefix::query()
            ->where('type', $type)
            ->whereIn('key', $keys)
            ->exists();
        if ($checks) {
            throw new GlobalConfigException("Group add failed: keys exists.");
        }

        return ConfigPrefix::insert($creates);
    }

    /**
     * 前缀表更新方法。没异常就是成功
     * @param string $type
     * @param mixed ...$items
     * @throws GlobalConfigException
     */
    private function prefixChange(string $type, array ...$items): void
    {
        $length = count($this->fillUpdate);
        foreach ($items as $item) {
            if (count($item) != $length) {
                throw new GlobalConfigException("Group change failed: array length is not support.");
            }

            foreach ($this->fillUpdate as $key) {
                if (!isset($item[$key])) {
                    throw new GlobalConfigException("Group change failed: {$key} not found.");
                }
            }
        }

        foreach ($items as $item) {
            $id = $item['id'];
            unset($item['id']);
            ConfigPrefix::query()
                ->where('id', $id)
                ->where('type', $type)
                ->update($item);
        }
    }

    /**
     * 前缀表快捷删除方法。没异常就是成功
     * @param string $type
     * @param mixed ...$items
     * @throws GlobalConfigException
     */
    private function prefixDelete(string $type, array $keys): void
    {
        foreach ($keys as $key) {
            if (!is_string($key)) {
                throw new GlobalConfigException('Group change failed: ID type is not support');
            }
        }

        ConfigPrefix::query()
            ->whereIn('key', $keys)
            ->where('type', $type)
            ->delete();
        $this->cache->deletes($keys);
    }

    public function groupsDelete(array $keys): void
    {
        $this->prefixDelete(ConfigPrefix::TYPE_GROUP, $keys);
    }

    public function configsGetByGroup(string ...$groups): array
    {
        return [];
    }

    public function configsGet(string ...$key): array
    {
        return [];
    }

    public function configsAdd(array ...$items): int
    {
        return 0;
    }

    public function configsChange(array ...$items): bool
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
