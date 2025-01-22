<?php

namespace App\Models\Broadconvo;

use Illuminate\Database\Eloquent\Model;

/**
 * Creates AI-agent for specific tenant
 */
class TenantRachel extends Model
{
    protected $connection = 'broadconvo';

    protected $table = 'rachel_tenant';

    protected $primaryKeys = ['tenant_id', 'rachel_id'];

    public $timestamps = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public function knowledgebases()
    {
        return $this->hasMany(Knowledgebase::class, 'rachel_id', 'rachel_id');
    }
}
