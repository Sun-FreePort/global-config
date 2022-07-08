<?php

namespace Yggdrasill\GlobalConfig;

use Yggdrasill\GlobalConfig\Cache\GlobalConfigCacheManager;
use Yggdrasill\GlobalConfig\Models\ConfigActionLog;
use Yggdrasill\GlobalConfig\Models\ConfigValue;

class GlobalConfigBase
{
    /**
     * @var GlobalConfigCacheManager
     */
    public $cache;

    public function __construct(GlobalConfigCacheManager $cache)
    {
        $this->cache = $cache;
    }

    // TODO 需要了再往这边拆，需求驱动重构

    public function groupsGet(string ...$key): array
    {
        $result = [];
        $data = $this->cache->gets(...$key);

        for ($i = count($key); $i >= 0; $i--) {
            if ($data[$i] == null) {
                $data[$i] = ConfigValue::query()->where('key_full', $key[$i])->first();
                if ($data[$i]) {
                    $data[$i] = $data[$i]->value;
                }
            }
            $result[$key[$i]] = $data[$i];
        }

        return $result;
    }

    public function valuesGet(string ...$key): array
    {
        $result = [];
        $data = $this->cache->gets(...$key);

        for ($i = count($key); $i >= 0; $i--) {
            if ($data[$i] == null) {
                $data[$i] = ConfigValue::query()->where('key_full', $key[$i])->first();
                if ($data[$i]) {
                    $data[$i] = $data[$i]->value;
                }
            }
            $result[$key[$i]] = $data[$i];
        }

        return $result;
    }

    /**
     * @param array ...$item [[key_full => value], ...]
     * @return array
     */
    public function valuesSet(array ...$item): array
    {
        $result = [];

        for ($i = count($item); $i >= 0; $i--) {
        }
//            if ($data[$i] == null) {
//                $data[$i] = ConfigValue::query()->where('key_full', $key[$i])->first();
//                if ($data[$i]) {
//                    $data[$i] = $data[$i]->value;
//                }
//            }
//            $result[$key[$i]] = $data[$i];

        return $result;
    }

    /**
     * 日志埋点
     */
    protected function setLog(int $belongID, int $authorID, string $actionTarget, int $actions, int $snapshot)
    {
        ConfigActionLog::create([
            'belong_id' => $belongID,
            'author_id' => $authorID,
            'type' => $actionTarget,
            'actions' => $actions,
            'snapshot' => $snapshot,
        ]);
    }
}
