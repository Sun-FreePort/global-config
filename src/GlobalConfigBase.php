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

class GlobalConfigBase
{
    /**
     * @var GlobalConfigCacheManager
     */
    public $cache;
    public $fillPrefixSelect;
    public $fillPrefixCreate;
    public $fillPrefixUpdate;

    public $ACTION_ = 0;

    public function __construct(GlobalConfigCacheManager $cache)
    {
        $this->cache = $cache;
        $this->fillPrefixUpdate = ['id', 'key', 'name'];
        $this->fillPrefixCreate = ['key', 'name', 'type'];
        $this->fillPrefixSelect = ['id', 'key', 'name', 'type'];
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

    /**
     * 解析全键
     * @param string $key
     * @return array
     * @throws GlobalConfigException
     */
    public function splitKey(string $key)
    {
        $result = explode('|', $key, 3);

        if (count($result) < 3) throw new GlobalConfigException('键解析失败：全键结构不完整');

        return [
            ConfigPrefix::TYPE_GROUP => $result[0],
            ConfigPrefix::TYPE_PREFIX => $result[1],
            'key' => $result[2],
        ];
    }

    /**
     * 日志埋点
     * @param array $logs 支持直传一条日志、或多条日志构成的数组，每条日志需以 ConfigActionLog::$fillable 为 Key
     * @throws GlobalConfigException
     */
    protected function setLogs(array $logs)
    {
        $time = time();
        if (!is_array($logs[array_key_first($logs)])) $logs = [$logs];

        foreach ($logs as &$log) {
            // FIXME 拿验证器换掉这个
            if (
                (int)$log['belong_id'] == 0 || !is_int((int)$log['belong_id'])
                || (int)$log['author_id'] == 0 || !is_int((int)$log['author_id'])
                || !is_string($log['type'])
                || (int)$log['actions'] == 0 || !is_int((int)$log['actions'])
                || !is_string($log['snapshot'])
            ) {
                throw new GlobalConfigException('setLogs failed: type not support.');
            }
            $log['created_at'] = $time;
        }
        ConfigActionLog::insert($logs);
    }

    /**
     * 获取前缀表数据
     * @param string $type
     * @param bool $isKey
     * @param int[]|string[] $keyOrIDs
     * @return array
     */
    protected function prefixGet(string $type, bool $isKey, array $keyOrIDs): array
    {
        $result = ConfigPrefix::query()
            ->select($this->fillPrefixSelect)
            ->where('type', $type)
            ->whereIn($isKey ? 'key' : 'id', $keyOrIDs)
            ->get()
            ->toArray();
        foreach ($result as $key => $item) {
            $result[$item['key']] = $item;
            unset($result[$key]);
        }

        return $result;
    }

    /**
     * 前缀表新增方法
     * @param int $authID
     * @param string $type
     * @param mixed ...$items
     * @return bool
     * @throws GlobalConfigException
     */
    protected function prefixAdd(int $authID, string $type, array ...$items): bool
    {
        $keys = [];
        $creates = [];
        $i = 0;
        $ts = time();
        $length = count($this->fillPrefixCreate);
        $logs = [];
        foreach ($items as $item) {
            foreach ($this->fillPrefixCreate as $key) {
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
            $keys[$i] = $item['key'];

            $logs[$item['key']] = [
                'belong_id' => 0,
                'author_id' => $authID,
                'type' => $type,
                'actions' => ConfigActionLog::ACTION_ADD,
                'snapshot' => json_encode($creates[$i]),
            ];
            $i++;
        }

        if (ConfigPrefix::query()
            ->where('type', $type)
            ->whereIn('key', $keys)
            ->exists()) {
            throw new GlobalConfigException("Group add failed: keys exists.");
        }

        $result = ConfigPrefix::insert($creates);
        if (!$result) throw new GlobalConfigException('Group add failed: insert fail.');

        // FIXME：ID 改成 Key？需要确定性能
        $prefixIDs = ConfigPrefix::query()
            ->select('id', 'key')
            ->where('type', $type)
            ->whereIn('key', $keys)
            ->get()
            ->pluck('id', 'key');
        foreach ($logs as $key => $log) {
            $logs[$key]['belong_id'] = $prefixIDs[$key];
        }
        $this->setLogs($logs);

        return true;
    }

    /**
     * 前缀表更新方法
     * 没异常就是成功。分组下没有配置，方可删除分组
     *
     * @param int $authID
     * @param string $type
     * @param mixed ...$items
     * @throws GlobalConfigException
     */
    protected function prefixChange(int $authID, string $type, array ...$items): void
    {
        $length = count($this->fillPrefixUpdate) + 1; // include delete key
        $deleteIDs = [];
        foreach ($items as $item) {
            if (count($item) != $length) {
                throw new GlobalConfigException("prefixChange failed: array length is not support.");
            }

            foreach ($this->fillPrefixUpdate as $key) {
                if (!isset($item[$key])) {
                    throw new GlobalConfigException("prefixChange failed: {$key} not found.");
                }
            }

            if ($item['delete']) {
                array_push($deleteIDs, $item['id']);
            }
        }

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
                    $model = ConfigPrefix::query()->find($id, $this->fillPrefixSelect)->toArray();
                    if ($model) {
                        $caches[$model['key']] = json_encode($model);
                    } else {
                        throw new GlobalConfigException('更新失败: 数据已不存在');
                    }
                    if ($item['key'] != $model['key']) {
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
    protected function prefixDelete(int $authID, string $type, array $keys): void
    {
        if (!count($keys)) {
            return;
        }
        foreach ($keys as $key) {
            if (!is_string($key)) {
                throw new GlobalConfigException('Group change failed: ID type is not support');
            }
        }

        $groupIDs = ConfigPrefix::query()
            ->select('id')
            ->where('type', $type)
            ->whereIn('key', $keys)
            ->get()
            ->pluck('id');
        $e = $this->checkKeyExists($type, $groupIDs);
        if ($e) {
            throw new GlobalConfigException($e);
        }

        DB::beginTransaction();
        try {
            $prefixIDs = ConfigPrefix::query()
                ->select('id')
                ->where('type', $type)
                ->whereIn('key', $keys)
                ->get()
                ->pluck('id');
            ConfigPrefix::query()
                ->whereIn('key', $keys)
                ->where('type', $type)
                ->delete();

            $logs = [];
            foreach ($prefixIDs as $id) {
                array_push($logs, [
                    'belong_id' => $id,
                    'author_id' => $authID,
                    'type' => $type,
                    'actions' => ConfigActionLog::ACTION_DELETE,
                    'snapshot' => '{}',
                ]);
            }
            $this->setLogs($logs);
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
    protected function checkKeyExists($type, $groupIDs): string
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
}
