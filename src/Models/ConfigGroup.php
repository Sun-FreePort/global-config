<?php

namespace Yggdrasill\GlobalConfig\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigGroup extends Model
{
    public $dateFormat = 'U';
    protected $fillable = [
        'name',
        'type',
    ];

    public const TYPE_GROUP = 'group';
    public const TYPE_PREFIX = 'prefix';
}
