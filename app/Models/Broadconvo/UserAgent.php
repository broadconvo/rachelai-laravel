<?php

namespace App\Models\Broadconvo;

use Illuminate\Database\Eloquent\Model;

class UserAgent extends Model
{
    protected $connection = 'broadconvo';
    protected $table = 'balch.user_agent';
    protected $primaryKey = 'agent_id';
    protected $keyType = 'string';

    public $timestamps = [
        'added_on',
        'recording_toggle_timestamp'
    ];

}
