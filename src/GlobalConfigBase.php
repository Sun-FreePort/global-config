<?php

namespace Yggdrasill\GlobalConfig;

use Yggdrasill\GlobalConfig\Cache\GlobalConfigCacheManager;
use Yggdrasill\GlobalConfig\Exception\GlobalConfigException;
use Yggdrasill\GlobalConfig\Models\ConfigActionLog;
use Yggdrasill\GlobalConfig\Models\ConfigPrefix;

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
    public function splitKey(string $key) {
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
