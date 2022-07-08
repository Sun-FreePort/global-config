<?php

namespace Yggdrasill\GlobalConfig\Cache;

use Illuminate\Support\Arr;
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
        foreach ($keys as &$key) {
            $key = $this->prefix . $key;
        }

        return Redis::mget($keys);
    }

    public function sets(array ...$keyAndValue): void
    {
        $length = count($keyAndValue);
        if ($length == 0) return;

        $keys = array_keys($keyAndValue);
        for ($i = $length; $i >= 0; $i--) {
            $key = $this->prefix . $keys[$i];
            $keyAndValue[$key] = $keyAndValue[$keys[$i]];
            unset($keyAndValue[$keys[$i]]);
        }

        if (is_array($keyAndValue[0]) && isset(
                $keyAndValue[array_key_first($keyAndValue)])) {
            foreach ($keyAndValue as $key => $value) {
                if (!is_array($value)) {
                    throw new \InvalidArgumentException("Only set same type value.");
                }
                $keyAndValue[$key] = json_encode($value);
            }
        }

        Redis::mset($keyAndValue);
    }
}
