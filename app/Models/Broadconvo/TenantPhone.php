<?php

namespace App\Models\Broadconvo;

use Illuminate\Database\Eloquent\Model;

class TenantPhone extends Model
{
    protected $connection = 'broadconvo';

    protected $table = 'tenant_did_rachel';

    protected $primaryKeys = ['did_number', 'tenant_id'];

    public $timestamps = false;

    protected $keyType = 'string';

    protected $guarded = [];
}
