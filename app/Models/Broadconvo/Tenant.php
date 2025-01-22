<?php

namespace App\Models\Broadconvo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    protected $connection = 'broadconvo';

    protected $table = 'tenant';

    protected $primaryKey = 'tenant_id';

    public $timestamps = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public function phones(): hasMany
    {
        return $this->hasMany(TenantPhone::class, 'tenant_id', 'tenant_id');
    }

    public function country(): HasOne
    {
        return $this->hasOne(Country::class, 'country_id', 'country_id');
    }

    public function userAgents(): HasMany
    {
        return $this->hasMany(UserAgent::class, 'tenant_id', 'tenant_id');
    }

    public function rachels(): HasMany
    {
        return $this->hasMany(TenantRachel::class, 'tenant_id', 'tenant_id');
    }

    public function knowledgebases(): HasManyThrough
    {
        return $this->hasManyThrough(
            Knowledgebase::class,     // The final related model
            TenantRachel::class,     // The intermediate model
            'tenant_id',             // Foreign key on the TenantRachel table (intermediate table)
            'rachel_id',           // Foreign key on the Knowledgebase table (final table)
            'tenant_id',             // Local key on the Tenant table
            'rachel_id'        // Local key on the TenantRachel table
        );
    }

    public function masters(): HasManyThrough
    {
        return $this->hasManyThrough(
            UserMaster::class,
            UserAgent::class,
            'tenant_id',
            'master_id',
            'tenant_id',
            'master_id'
        );
    }
}
