<?php

namespace App\Models\Broadconvo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

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

    // user_master -> user_agent -> tenant
    public function tenant(): HasOneThrough
    {
        return $this->hasOneThrough(
            Tenant::class,
            UserAgent::class,
            'master_id',
            'tenant_id',
            'master_id',
            'tenant_id'
        );
    }

    // user_master -> user_agent -> tenant -> rachel_tenant
    public function rachels(): HasManyThrough
    {
        return $this->hasManyThrough(
            TenantRachel::class,    // Final destination table (rachel_tenant)
            UserAgent::class,      // First intermediate table (user_agent)
            'master_id',           // Foreign key in UserAgent that links to UserMaster
            'tenant_id',         // Foreign key in TenantRachel that links to Tenant
            'master_id',           // Local key in UserMaster
            'tenant_id'      // Local key in UserAgent that links to Tenant
        );
    }

}
