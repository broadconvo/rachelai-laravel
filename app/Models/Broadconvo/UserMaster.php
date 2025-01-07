<?php

namespace App\Models\Broadconvo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserMaster extends Model
{
    protected $connection = 'broadconvo';

    protected $table = 'user_master';
    protected $primaryKey = 'master_id';

    public $timestamps = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Automatically generate the master_id if not already set
            if (empty($model->master_id)) {
                $model->master_id = str()->uuid();
            }
        });
    }

    public function userAgent(): HasOne
    {
        return $this->hasOne(UserAgent::class, 'master_id', 'master_id');
    }

}
