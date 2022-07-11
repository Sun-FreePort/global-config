<?php

namespace Yggdrasill\GlobalConfig;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yggdrasill\GlobalConfig\Cache\GlobalConfigCacheManager;
use Yggdrasill\GlobalConfig\Exception\GlobalConfigException;
use Yggdrasill\GlobalConfig\Models\ConfigKey;
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

    /**
     * 获取分组
     * @param string ...$key
     * @return array
     */
    public function groupsGet(string ...$key): array
    {
        $data = $this->cache->gets(...$key);
        $needFindNames = [];
        $result = [];

        for ($i = count($key) - 1; $i >= 0; $i--) {
            ($data[$i] == null)
                ? array_push($needFindNames, $key[$i])
                : $result[$key[$i]] = json_decode($data[$i], true);
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

    /**
     * 添加分组
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
     * @param mixed ...$items
     * @throws GlobalConfigException
     */
    public function groupsChange(int $authID, array ...$items): void
    {
        $this->prefixChange($authID, ConfigPrefix::TYPE_GROUP, ...$items);
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
     * 前缀表新增方法。
     * @param string $type
     * @param mixed ...$items
     * @return bool
     * @throws GlobalConfigException
     */
    private function prefixAdd(int $authID, string $type, array ...$items): bool
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
     * @param int $authID
     * @param string $type
     * @param mixed ...$items
     * @throws GlobalConfigException
     */
    private function prefixChange(int $authID, string $type, array ...$items): void
    {
        $length = count($this->fillUpdate) + 1; // include delete key
        $deleteIDs = [];
        foreach ($items as $item) {
            if (count($item) != $length) {
                throw new GlobalConfigException("prefixChange failed: array length is not support.");
            }

            foreach ($this->fillUpdate as $key) {
                if (!isset($item[$key])) {
                    throw new GlobalConfigException("prefixChange failed: {$key} not found.");
                }
            }

            if ($item['delete']) {
                array_push($deleteIDs, $item['id']);
            }
        }

        // 分组下没有配置，方可删除分组
        $e = $this->checkKeyExists($type, $deleteIDs);
        if ($e) {
            throw new GlobalConfigException($e);
        }

        $caches = [];
        DB::beginTransaction();
        try {
            foreach ($items as $item) {
                $id = $item['id'];
                unset($item['id']);
                if (key_exists('type', $item)) {
                    throw new GlobalConfigException('无法修改：类型仅能在创建时指定');
                }
                if (!isset($item['delete'])) {
                    $model = ConfigPrefix::query()->find($id, $this->fillSelect)->toArray();
                    if ($model) {
                        $caches[$model['key']] = json_encode($model);
                    } else {
                        throw new GlobalConfigException('更新失败: 数据已不存在');
                    }
                    if ($item['key'] != $model['key']) {
                        // 更新分组下的配置 key_full
                        // FIXME select
                        $keyModels = ConfigKey::query()
                            ->when($type == ConfigPrefix::TYPE_GROUP, function ($query) use ($id) {
                                return $query->where('group_id', $id);
                            })
                            ->when($type == ConfigPrefix::TYPE_PREFIX, function ($query) use ($id) {
                                return $query->where('prefix_id', $id);
                            })
                            ->get();
                        foreach ($keyModels as $keyModel) {
                            $key = $this->splitKey($keyModel->key_full);
                            $key[$type] = $item['key'];
                            $keyModel->key_full = $this->fillKeys(...$key);
                            $keyModel->save();
                        }
                    }
                    ConfigPrefix::query()
                        ->where('id', $id)
                        ->update($item);
                }
            }
            $this->prefixDelete($authID, $type, $deleteIDs);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMsg = 'prefixChange failed: ' . $e->getMessage() . ' | ' . $e->getFile() . ' | ' . $e->getLine();
            Log::error($errorMsg, $e->getTrace());
            throw new GlobalConfigException($errorMsg);
        }

        try {
            $this->cache->sets($caches);
        } catch (\Exception $e) { // 静默
            $errorMsg = 'prefixChange failed: ' . $e->getMessage() . ' | ' . $e->getFile() . ' | ' . $e->getLine();
            Log::error($errorMsg, $e->getTrace());
        }
    }

    /**
     * 前缀表快捷删除方法。没异常就是成功
     * @param int $authID
     * @param string $type
     * @param array $keys
     * @throws GlobalConfigException
     */
    private function prefixDelete(int $authID, string $type, array $keys): void
    {
        if (!count($keys)) {
            return ;
        }
        foreach ($keys as $key) {
            if (!is_string($key)) {
                throw new GlobalConfigException('Group change failed: ID type is not support');
            }
        }

        $groupIDs = ConfigPrefix::query()
            ->select('id')
            ->whereIn('key', $keys)
            ->where('type', $type)
            ->get()
            ->pluck('id');
        $e = $this->checkKeyExists($type, $groupIDs);
        if ($e) {
            throw new GlobalConfigException($e);
        }

        DB::beginTransaction();
        try {
            ConfigPrefix::query()
                ->whereIn('key', $keys)
                ->where('type', $type)
                ->delete();
            $this->cache->deletes(...$keys);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMsg = 'prefixDelete failed: ' . $e->getMessage() . ' | ' . $e->getFile() . ' | ' . $e->getLine();
            Log::error($errorMsg, $e->getTrace());
            throw new GlobalConfigException($errorMsg);
        }
    }

    /**
     * @param $type
     * @param Collection|array $groupIDs
     * @return string
     */
    private function checkKeyExists($type, $groupIDs): string
    {
        $has = ConfigKey::query()
            ->whereIn('group_id', $groupIDs)
            ->exists();
        if ($has) {
            $typeName = ($type == ConfigPrefix::TYPE_GROUP) ? '分组' : '前缀';
            return "{$typeName}下存在可用配置";
        }
        return '';
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
     * @param string ...$keys
     * @return array
     */
    public function configsGetByFullKey(string ...$fullKeys): array
    {
        return ConfigKey::query()
            ->whereIn('key_full', $fullKeys)
            ->get()
            ->toArray();
    }

    public function configsAdd(int $authID, array ...$items): int
    {
        // FIXME 累了。后续追日志
        DB::transaction(function () use ($authID, $items) {
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
                $FULL_KEY = "{$prefix[$item['group_id']]['key']}, {$prefix[$item['prefix_id']]['key']}, {$item['key']}";
                array_push($keysOnly, $item['key']);
                array_push($keys, [
                    'group_id' => $item['group_id'],
                    'prefix_id' => $item['prefix_id'],
                    'key' => $item['key'],
                    'key_full' => $FULL_KEY,
                    'desc' => $item['desc'],
                    'type' => $item['type'],
                    'created_at' => $time,
                ]);
            }
            ConfigKey::query()
                ->insert($items);
            $keyModels = ConfigKey::query()
                ->select('id', 'key')
                ->whereIn('key', $keysOnly)
                ->get()
                ->pluck('id', 'key');
            foreach ($items as $item) {
                array_push($values, [
                    'key_full' => $FULL_KEY,
                    'key' => $item['key'],
                    'key_id' => $keyModels[$item['key']],
                    'value' => $item['type'] . ConfigValue::VALUE_SPLIT . ($item['value'] ?? ''),
                    'author_by' => $authID,
                    'created_at' => $time,
                ]);
            }
            ConfigValue::query()
                ->insert($values);
        });
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
        // FIXME 累了。后续追日志
        DB::transaction(function () use ($authID, $items) {
            $deleteIDs = [];
            foreach ($items as $item) {
                if (!empty($item['delete'])) {
                    ConfigKey::query()
                        ->where('id', $item['id'])
                        ->update($item);

                    ConfigValue::query()
                        ->where([''])
                        ->update(['' => '']);
                } else {
                    array_push($deleteIDs, $item['id']);
                }
            }
            $this->configsDelete($authID, ...$deleteIDs);
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
        // FIXME 累了。后续追日志
        DB::transaction(function () use ($authID, $itemIDs) {
            ConfigKey::query()
                ->whereIn('id', $itemIDs)
                ->update([
                    'deleted_by' => $authID,
                    'deleted_at' => time(),
                ]);
            // 如果是删除，需要将数值更新掉
            ConfigValue::query()
                ->whereIn('key_id', $itemIDs)
                ->delete();
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
            foreach ($items as $key => $value) {
                $result = ConfigValue::query()
                    ->where('key_full', $key)
                    ->update([
                        'value' => $value,
                        'author_by' => $authID,
                    ]);

                if (!$result) {
                    array_push($errorKeys, $key);
                    unset($items[$key]);
                } else if (!$keysRule[$key]['cache']) {
                    unset($items[$key]);
                }
            }
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

        if (count($errorKeys)) {
            $errorMsg = implode(',', $errorKeys);
            Log::warning('valuesSet failed by keys: ' . $errorMsg);
            throw new GlobalConfigException('部分成功。因键不存在而失败(' . $errorMsg . ')。');
        }

        return true;
    }
}
