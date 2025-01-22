<?php

namespace App\Models\Broadconvo;

use Illuminate\Database\Eloquent\Model;

class KnowledgebaseEntry extends Model
{
    protected $connection = 'broadconvo';

    protected $table = 'kb_entry';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $guarded = [];
}
