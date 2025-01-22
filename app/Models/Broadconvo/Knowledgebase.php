<?php

namespace App\Models\Broadconvo;

use Illuminate\Database\Eloquent\Model;

class Knowledgebase extends Model
{
    protected $connection = 'broadconvo';

    protected $table = 'kb_rachel';

    protected $primaryKeys = ['kb_id', 'rachel_id'];

    public $timestamps = false;

    protected $keyType = 'string';

    protected $guarded = [];
}
