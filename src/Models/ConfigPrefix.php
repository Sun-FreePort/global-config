<?php

namespace Yggdrasill\GlobalConfig\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigPrefix extends Model
{
    protected $table = 'gc_config_prefixes';
    protected $dateFormat = 'U';
    protected $fillable = [
        'key',
        'name',
        'type',
    ];

    public const TYPE_GROUP = 'group';
    public const TYPE_PREFIX = 'prefix';
}
