<?php

namespace App\Models\Broadconvo;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $connection = 'broadconvo';

    protected $table = 'tenant';

    protected $primaryKey = 'tenant_id';

    public $timestamps = false;

    protected $keyType = 'string';

    protected $guarded = [];
}
