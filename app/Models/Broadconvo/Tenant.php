<?php

namespace App\Models\Broadconvo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    protected $connection = 'broadconvo';

    protected $table = 'tenant';

    protected $primaryKey = 'tenant_id';

    public $timestamps = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public function tenantPhones(): hasMany
    {
        return $this->hasMany(TenantPhone::class, 'tenant_id', 'tenant_id');
    }

    public function country(): HasOne
    {
        return $this->hasOne(Country::class, 'country_id', 'country_id');
    }
}
