<?php

namespace App\Models\Broadconvo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Knowledgebase extends Model
{
    protected $connection = 'broadconvo';

    protected $table = 'kb_rachel';

    protected $primaryKey = 'kb_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $fillable = [
        'rachel_id',
        'kb_id',
        'kb_label',
        'kb_industry',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(KnowledgebaseEntry::class, 'kb_id','kb_id');
    }

    public function addEntry(array $entry)
    {
        return $this->entries()->create($entry);
    }
}
