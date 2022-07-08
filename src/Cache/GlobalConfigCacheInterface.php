<?php

namespace Yggdrasill\GlobalConfig\Cache;

interface GlobalConfigCacheInterface
{
    public function gets(string ...$keys);
    public function sets(array ...$keyAndValue);
}
