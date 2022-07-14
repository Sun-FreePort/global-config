<?php

namespace Yggdrasill\GlobalConfig\Cache;

use Illuminate\Support\Facades\Redis;

class RedisCache implements GlobalConfigCacheInterface
{
    /**
     * @var string
     */
    private $prefix;

    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    public function gets(string ...$keys): array
    {
        if ($this->prefix) {
            foreach ($keys as &$key) {
                $key = $this->prefix . $key;
            }
        }

        return Redis::mget($keys);
    }

    /**
     * 批量更新
     * @param mixed ...$keyAndValue []
     */
    public function sets(array ...$keyAndValue): void
    {
        $length = count($keyAndValue);
        if ($length == 0) return;

        // 更新前缀
        if ($this->prefix) {
            $keys = array_keys($keyAndValue);
            for ($i = $length; $i >= 0; $i--) {
                $key = $this->prefix . $keys[$i];
                $keyAndValue[$key] = $keyAndValue[$keys[$i]];
                unset($keyAndValue[$keys[$i]]);
            }
        }

        // 更新值
        $key = array_key_first($keyAndValue);
        if (isset($keyAndValue[0][$key]) && is_array($keyAndValue[0][$key])) {
            foreach ($keyAndValue as $key => $value) {
                if (!is_array($value)) {
                    throw new \InvalidArgumentException("Only set same type value.");
                }
                $keyAndValue[$key] = json_encode($value);
            }
        }

        Redis::mset(...$keyAndValue);
    }

    public function deletes(string ...$keys): void
    {
        Redis::del(...$keys);
    }
}
