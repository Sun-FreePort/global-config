<?php

namespace Yggdrasill\GlobalConfig\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConfigValue extends Model
{
    use SoftDeletes;

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
