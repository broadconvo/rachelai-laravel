<?php

namespace App\Models\Broadconvo;

use Illuminate\Database\Eloquent\Model;

class UserAgent extends Model
{
    protected $connection = 'broadconvo';

    protected $table = 'balch.user_agent';

    protected $primaryKey = 'agent_id';

    public $timestamps = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Automatically generate the master_id if not already set
            if (empty($model->agent_id)) {
                $model->agent_id = str()->uuid();
            }
        });
    }

}
