<?php

namespace Yggdrasill\GlobalConfig\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigActionLog extends Model
{
    protected $table = 'gc_config_action_logs';
    public $dateFormat = 'U';
    public const UPDATED_AT = null;
    protected $fillable = [
        'belong_id',
        'author_id',
        'type',
        'actions',
        'snapshot',
    ];
}
