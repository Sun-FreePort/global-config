<?php

namespace Yggdrasill\GlobalConfig\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigValue extends Model
{
    protected $table = 'gc_config_values';
    public $dateFormat = 'U';
    protected $fillable = [
        'belong_id',
        'author_id',
        'type',
        'actions',
        'snapshot',
    ];
}
