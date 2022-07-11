<?php

namespace Yggdrasill\GlobalConfig\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigValue extends Model
{
    public const VALUE_SPLIT = '|';

    protected $table = 'gc_config_values';
    public $dateFormat = 'U';
    protected $fillable = [
        'key_full',
        'key',
        'key_id',
        'value',
        'author_by',
    ];
}
