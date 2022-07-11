<?php

namespace Yggdrasill\GlobalConfig\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConfigPrefix extends Model
{
    use SoftDeletes;

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
