<?php

namespace App\Models\Broadconvo;

use Illuminate\Database\Eloquent\Model;

/**
 * Creates AI-agent for specific tenant
 */
class Rachel extends Model
{
    protected $connection = 'broadconvo';

    protected $table = 'rachel_tenant';

    protected $primaryKey = 'rachel_id';

    public $timestamps = false;

    protected $keyType = 'string';

    protected $guarded = [];
}
