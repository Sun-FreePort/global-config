# global-config

## 使用指南

本包旨在提供基础配置与缓存功能。

服务以单例对象 GlobalConfig 提供。通过 `src/Support/Facades/GlobalConfig.php` 可以直接获得本包支持的接口。

在包安装后：

- 初始化命令为 `php artisan global-config:init`。

## 扩展指南

### 缓存

目前支持 Redis 缓存扩展；

如果你需要定义自己的缓存扩展，请在 `src/Cache/` 目录下建立 `*Cache.php` 文件，并遵循 `GlobalConfigCacheInterface` 接口。

如有疑问，可以参考作为 Redis 驱动的 `RedisCache` 和加载驱动的 `GlobalConfigCacheManager`。

### 自动化测试

本包采用 `orchestra/testbench` 作为模拟 Laravel 的测试工具，开发时无需引入实际项目。

测试文件请继承 `TestBase`，作为自定义父类。

自动化测试所需配置与 Laravel 框架内测试近似，区别与功能可参考：[Testbench Document](https://packages.tools/testbench/getting-started/introduction.html)。

### 版本设定

| 包版本 | 对应框架版本 |
| --- | --- |
| ^1.0 | 6.x |

Ps：请避免在生产项目使用 `dev-master` 版本，其将自动指向最新版本。
