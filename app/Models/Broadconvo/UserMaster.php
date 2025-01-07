<?php

namespace App\Models\Broadconvo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserMaster extends Model
{
    protected $connection = 'broadconvo';
    protected $table = 'balch.user_master';
    protected $primaryKey = 'master_id';
    protected $keyType = 'string';

    public $timestamps = ['added_on'];

    public function userAgent(): HasOne
    {
        return $this->hasOne(UserAgent::class, 'master_id', 'master_id');
    }

}
