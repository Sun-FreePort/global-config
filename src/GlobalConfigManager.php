<?php

namespace Yggdrasill\GlobalConfig;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yggdrasill\GlobalConfig\Cache\GlobalConfigCacheManager;
use Yggdrasill\GlobalConfig\Exception\GlobalConfigException;
use Yggdrasill\GlobalConfig\Models\ConfigActionLog;
use Yggdrasill\GlobalConfig\Models\ConfigKey;
use Yggdrasill\GlobalConfig\Models\ConfigPrefix;
use Yggdrasill\GlobalConfig\Models\ConfigValue;

class GlobalConfigManager extends GlobalConfigBase
{
    /**
     * @var array
     */

    public function __construct(GlobalConfigCacheManager $cache)
    {
        parent::__construct($cache);
    }

    /**
     * 获取分组
     * @param string ...$keys
     * @return array
     */
    public function groupsGet(string ...$keys): array
    {
        return $this->prefixGet(ConfigPrefix::TYPE_GROUP, true, $keys);
    }

    /**
     * 获取分组（根据 ID）
     * @param int ...$ids
     * @return array
     */
    public function groupsGetByID(int ...$ids): array
    {
        return $this->prefixGet(ConfigPrefix::TYPE_GROUP, false, $ids);
    }

    /**
     * 添加分组
     * @param int $authID
     * @param mixed ...$items
     * @return bool
     * @throws GlobalConfigException
     */
    public function groupsAdd(int $authID, array ...$items): bool
    {
        return $this->prefixAdd($authID, ConfigPrefix::TYPE_GROUP, ...$items);
    }

    /**
     * 更新分组
     * @param int $authID
     * @param mixed ...$items
     * @throws GlobalConfigException
     */
    public function groupsChange(int $authID, array ...$items): void
    {
        $this->prefixChange($authID, ConfigPrefix::TYPE_GROUP, ...$items);
    }

    /**
     * 删除分组
     * @param int $authID
     * @param array $keys
     * @throws GlobalConfigException
     */
    public function groupsDeleteByKey(int $authID, string ...$keys): void
    {
        $this->prefixDelete($authID, ConfigPrefix::TYPE_GROUP, $keys);
    }

    /**
     * 获取前缀
     * @param string ...$keys
     * @return array
     */
    public function prefixesGet(string ...$keys): array
    {
        return $this->prefixGet(ConfigPrefix::TYPE_PREFIX, true, $keys);
    }

    /**
     * 获取前缀（根据 ID）
     * @param int ...$ids
     * @return array
     */
    public function prefixesGetByID(int ...$ids): array
    {
        return $this->prefixGet(ConfigPrefix::TYPE_PREFIX, false, $ids);
    }

    /**
     * 添加前缀
     * @param int $authID
     * @param mixed ...$items
     * @return bool
     * @throws GlobalConfigException
     */
    public function prefixesAdd(int $authID, array ...$items): bool
    {
        return $this->prefixAdd($authID, ConfigPrefix::TYPE_PREFIX, ...$items);
    }

    /**
     * 更新前缀
     * @param int $authID
     * @param mixed ...$items
     * @throws GlobalConfigException
     */
    public function prefixesChange(int $authID, array ...$items): void
    {
        $this->prefixChange($authID, ConfigPrefix::TYPE_PREFIX, ...$items);
    }

    /**
     * 删除前缀
     * @param int $authID
     * @param array $keys
     * @throws GlobalConfigException
     */
    public function prefixesDeleteByKey(int $authID, string ...$keys): void
    {
        $this->prefixDelete($authID, ConfigPrefix::TYPE_PREFIX, $keys);
    }

    /**
     * 通过组名获取配置项
     * @param string ...$groups
     * @return array
     */
    public function configsGetByGroupName(string ...$groups): array
    {
        $prefixIDs = ConfigPrefix::query()
            ->select('id')
            ->whereIn('key', $groups)
            ->get()
            ->pluck('id');

        return ConfigKey::query()
            ->whereIn('group_id', $prefixIDs)
            ->get()
            ->toArray();
    }

    /**
     * 通过组 ID 获取配置项
     * @param string ...$groups
     * @return array
     */
    public function configsGetByGroupID(int ...$group): array
    {
        return ConfigKey::query()
            ->whereIn('group_id', $group)
            ->get()
            ->toArray();
    }

    /**
     * 通过配置键获取配置项
     * @param string ...$keys
     * @return array
     */
    public function configsGet(string ...$keys): array
    {
        return ConfigKey::query()
            ->whereIn('key_full', $keys)
            ->get()
            ->toArray();
    }

    /**
     * 通过配置全键获取配置项
     * @param string ...$fullKeys
     * @return array
     */
    public function configsGetByFullKey(string ...$fullKeys): array
    {
        return ConfigKey::query()
            ->whereIn('key_full', $fullKeys)
            ->get()
            ->toArray();
    }

    /**
     * 配置增加
     * @param int $authID
     * @param mixed ...$items
     * @return int
     * @throws GlobalConfigException
     */
    public function configsAdd(int $authID, array ...$items): int
    {
        $logs = [];
        $keysOnly = [];
        $keys = [];
        $values = [];
        $time = time();
        $prefix = ConfigPrefix::query()
            ->select('id', 'type', 'key')
            ->where(function ($q) use ($items) {
                return $q->where('type', ConfigPrefix::TYPE_GROUP)
                    ->whereIn('id', Arr::pluck($items, 'group_id'));
            })
            ->orWhere(function ($q) use ($items) {
                return $q->where('type', ConfigPrefix::TYPE_PREFIX)
                    ->whereIn('id', Arr::pluck($items, 'prefix_id'));
            })
            ->get()
            ->pluck(null, 'id')
            ->toArray();
        foreach ($items as $item) {
            $FULL_KEY = $this->fillKeys($prefix[$item['group_id']]['key'], $prefix[$item['prefix_id']]['key'], $item['key'])[0];
            array_push($keysOnly, $item['key']);
            $data = [
                'group_id' => $item['group_id'],
                'prefix_id' => $item['prefix_id'],
                'key' => $item['key'],
                'key_full' => $FULL_KEY,
                'desc' => $item['desc'],
                'type' => $item['type'],
                'created_at' => $time,
            ];
            array_push($keys, $data);
            $logs[$FULL_KEY] = [
                'author_id' => $authID,
                'type' => ConfigActionLog::TYPE_CONFIG,
                'actions' => ConfigActionLog::ACTION_ADD,
                'snapshot' => json_encode($data),
            ];
        }
        ConfigKey::insert($items);

        $keyModels = ConfigKey::query()
            ->select('id', 'key')
            ->whereIn('key', $keysOnly)
            ->get()
            ->pluck('id', 'key');
        foreach ($items as $item) {
            $FULL_KEY = $this->fillKeys($prefix[$item['group_id']]['key'], $prefix[$item['prefix_id']]['key'], $item['key'])[0];
            $data = [
                'key_full' => $FULL_KEY,
                'key' => $item['key'],
                'key_id' => $keyModels[$item['key']],
                'value' => $item['type'] . ConfigValue::VALUE_SPLIT . ($item['value'] ?? ''),
                'author_by' => $authID,
                'created_at' => $time,
            ];
            array_push($values, $data);
            $logs[$FULL_KEY]['belong_id'] = $keyModels[$item['key']];
        }
        ConfigValue::insert($values);

        $this->setLogs($logs);

        return 0;
    }

    /**
     * 配置更新
     * @param int $authID
     * @param mixed ...$items
     * @return bool
     * @version 0.9
     */
    public function configsChange(int $authID, array ...$items): bool
    {
        if (!count($items)) return true;

        DB::transaction(function () use ($authID, $items) {
            $deleteIDs = [];
            $logs = [];
            $models = ConfigKey::query()
                ->where('id', Arr::pluck($items, 'id'))
                ->get()
                ->pluck(null, 'id');

            foreach ($items as $item) {
                if (empty($item['delete'])) {
                    unset($item['delete']);

                    if ($models[$item['id']]->group_id != $item['group_id']
                        || $models[$item['id']]->prefix_id != $item['prefix_id']) {

                        $group = $this->groupsGetByID($item['group_id']);
                        $prefix = $this->prefixesGetByID($item['prefix_id']);
                        $oldKeyFull = $item['key_full'];
                        $item['key_full'] = $this->fillKeys(array_pop($group)['key'], array_pop($prefix)['key'], $item['key'])[0];
                        ConfigValue::query()
                            ->where(['key_full' => $oldKeyFull])
                            ->update(['key_full' => $item['key_full']]);
                        $this->cache->deletes($oldKeyFull);
                    }
                    array_push($logs, [
                        'belong_id' => $item['id'],
                        'author_id' => $authID,
                        'type' => ConfigActionLog::TYPE_CONFIG,
                        'actions' => ConfigActionLog::ACTION_CHANGE,
                        'snapshot' => json_encode($item),
                    ]);
                    ConfigKey::query()
                        ->where('id', $item['id'])
                        ->update($item);
                } else {
                    array_push($deleteIDs, $item['id']);
                }
            }

            $this->setLogs($logs);

            if (count($deleteIDs)) $this->configsDelete($authID, ...$deleteIDs);
        });
        return true;
    }

    /**
     * 配置删除
     * @param int $authID
     * @param mixed ...$items
     * @return bool
     * @version 0.9
     */
    public function configsDelete(int $authID, int ...$itemIDs): bool
    {
        DB::transaction(function () use ($authID, $itemIDs) {
            ConfigKey::query()
                ->whereIn('id', $itemIDs)
                ->update([
                    'deleted_by' => $authID,
                    'deleted_at' => time(),
                ]);
            ConfigValue::query()
                ->whereIn('key_id', $itemIDs)
                ->delete();

            $logs = [];
            foreach ($itemIDs as $ID){
                array_push($logs, [
                    'belong_id' => $ID,
                    'author_id' => $authID,
                    'type' => ConfigActionLog::TYPE_VALUE,
                    'actions' => ConfigActionLog::ACTION_DELETE,
                    'snapshot' => "{}",
                ]);
            }
            $this->setLogs($logs);
        });

        return true;
    }

    /**
     * 获取值
     * @param array $keys
     * @return array
     * @version 1.0
     */
    public function values(string ...$keys)
    {
        $values = $this->cache->gets(...$keys);
        $result = array_combine($keys, $values);

        foreach ($result as $key => $value) {
            if ($value == null) {
                $model = ConfigValue::query()
                    ->select(['value'])
                    ->where('key_full', $key)
                    ->first();

                if ($model) {
                    $this->cache->sets([$key => $model->value]);
                    $result[$key] = $model->value;
                }
            }

            if ($value) {
                $i = explode(ConfigValue::VALUE_SPLIT, $result[$key], 2);
                $type = $i[0];
                $val = $i[1];
                switch ($type) {
                    case ConfigKey::TYPE_NUMBER:
                    case ConfigKey::TYPE_STRING_SHORT:
                    case ConfigKey::TYPE_STRING_LONG:
                        $result[$key] = $val;
                        break;
                    case ConfigKey::TYPE_BOOL:
                        $result[$key] = (bool)$val;
                        break;
                }
            }
        }

        return $result;
    }

    /**
     * 更新值
     * @param int $authID
     * @param mixed ...$items [[key_full => value], ...]
     * @return bool
     * @throws GlobalConfigException
     * @version 1.0
     */
    public function valuesChange(int $authID, array $items): bool
    {
        // TODO 计算修改量，量大走 SQL 生成
        $fullKeys = array_keys($items);
        $keysRule = ConfigKey::query()
            ->where('key_full', $fullKeys)
            ->get()
            ->pluck(null, 'key_full')
            ->toArray();
        if (!count($keysRule)) throw new GlobalConfigException('失败：对应配置项不存在');

        foreach ($items as $key => $value) {
            if (!$keysRule[$key]['active'] || !$keysRule[$key]['visible']) {
                unset($items[$key]);
                continue;
            }

            switch ($keysRule[$key]['type']) {
                case ConfigKey::TYPE_NUMBER:
                    if (!is_numeric($value)) {
                        throw new GlobalConfigException("{$key} 校验失败：不支持的类型");
                    }
                    break;
                case ConfigKey::TYPE_STRING_SHORT:
                    if (!is_string($value)) {
                        throw new GlobalConfigException("{$key} 校验失败：不支持的类型");
                    } else if (strlen($value) > 255) {
                        throw new GlobalConfigException("{$key} 校验失败：长度受限");
                    }
                    break;
                case ConfigKey::TYPE_STRING_LONG:
                    if (!is_string($value)) {
                        throw new GlobalConfigException("{$key} 校验失败：不支持的类型");
                    }
                    break;
                case ConfigKey::TYPE_BOOL:
                    $items[$key] = (int)(bool)$value;
                    break;
                // TODO 枚举，值内不要带符号
            }
            $items[$key] = $keysRule[$key]['type'] . ConfigValue::VALUE_SPLIT . $value;
        }

        // 更新
        $errorKeys = [];
        DB::beginTransaction();
        try {
            $values = ConfigValue::query()
                ->whereIn('key_full', array_keys($items))
                ->get()
                ->pluck(null, 'key_full');
            $logs = [];
            foreach ($items as $key => $value) {
                $values[$key]->value = $value;
                $values[$key]->author_by = $authID;
                $values[$key]->save();

                array_push($logs, [
                    'belong_id' => $values[$key]->id,
                    'author_id' => $authID,
                    'type' => ConfigActionLog::TYPE_VALUE,
                    'actions' => ConfigActionLog::ACTION_CHANGE,
                    'snapshot' => "{{$key}: {$values[$key]->value}}",
                ]);

                if (!$keysRule[$key]['cache']) {
                    unset($items[$key]);
                }
            }

            $this->setLogs($logs);

            if (count($items)) {
                $this->cache->sets($items);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMsg = 'valuesSet failed: ' . $e->getMessage() . ' | ' . $e->getFile() . ' | ' . $e->getLine();
            Log::error($errorMsg, $e->getTrace());
            throw new GlobalConfigException($errorMsg);
        }

        return true;
    }
}
