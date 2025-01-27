<?php

namespace App\Models\Broadconvo;

use Illuminate\Database\Eloquent\Model;

class TenantPhone extends Model
{
    protected $connection = 'broadconvo';

    protected $table = 'tenant_did_rachel';

    protected $primaryKeys = ['did_number', 'tenant_id'];

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function setKeysForSaveQuery($query)
    {
        return $query->where('did_number', $this->getAttribute('did_number'))
            ->where('tenant_id', $this->getAttribute('tenant_id'));
    }
}
