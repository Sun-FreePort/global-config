<?php

namespace Yggdrasill\GlobalConfig\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigActionLog extends Model
{
    public const ACTION_ADD = 1;
    public const ACTION_CHANGE = 2;
    public const ACTION_DELETE = 3;

    // ConfigPrefix::TYPE_* 的补充
    public const TYPE_CONFIG = 'config';
    public const TYPE_VALUE = 'value';

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
