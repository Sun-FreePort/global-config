<?php

namespace Yggdrasill\GlobalConfig\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigKey extends Model
{
    protected $table = 'gc_config_keys';
    protected $dateFormat = 'U';
    protected $fillable = [
        'group_id',
        'prefix_id',
        'key',
        'key_full',
        'desc',
        'type',
        'rules',
        'cache',
        'active',
        'visible',
    ];

    public const TYPE_NUMBER = 1;
    public const TYPE_STRING_SHORT = 2;
    public const TYPE_STRING_LONG = 3;
    public const TYPE_BOOL = 4;
}
