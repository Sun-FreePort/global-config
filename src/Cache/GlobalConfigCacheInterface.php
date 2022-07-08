<?php

namespace Yggdrasill\GlobalConfig\Cache;

interface GlobalConfigCacheInterface
{
    public function gets(string ...$keys): array;

    public function sets(array ...$keyAndValue): void;

    public function deletes(array ...$keys): void;
}
