<?php

namespace Yggdrasill\GlobalConfig\Cache;

use Illuminate\Contracts\Cache\Store;
use InvalidArgumentException;

/**
 * @method GlobalConfigCacheInterface array gets(string ...$keys);
 * @method GlobalConfigCacheInterface void sets(array ...$keyAndValue);
 */
class GlobalConfigCacheManager
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * @var Store
     */
    public $store;

    /**
     * Create a new Cache manager instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->resolve('global-config');
    }

    /**
     * Magic functional to store instance.
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->store->$name(...$arguments);
    }

    /**
     * Get the cache connection configuration.
     */
    protected function getConfig($name): array
    {
        if (!is_null($name) && $name !== 'null') {
            return $this->app['config']["global-config.cache"];
        }

        return ['driver' => 'null'];
    }

    /**
     * Resolve the given store.
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Global Cache store [{$name}] is not defined.");
        }

        $driverMethod = 'create' . ucfirst($config['driver']) . 'Driver';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        } else {
            throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
        }
    }

    /**
     * Create an instance of the Redis cache driver.
     */
    protected function createRedisDriver(array $config)
    {
//        TODO: 搞成独立连接？占用多个连接，性能会贼拉跨……
//        $redis = $this->app['redis'];
//        $connection = $config['connection'] ?? 'default';

        $this->store = new RedisCache($this->getPrefix($config));
    }

    /**
     * Get the cache prefix.
     *
     * @param array $config
     * @return string
     */
    protected function getPrefix(array $config)
    {
        return $config['prefix'] ?? $this->app['config']['global-config.cache.prefix'];
    }
}
