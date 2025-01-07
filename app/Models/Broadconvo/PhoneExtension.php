<?php

namespace App\Models\Broadconvo;

use Illuminate\Database\Eloquent\Model;

class PhoneExtension extends Model
{
    protected $connection = 'broadconvo';

    protected $table = 'extension_def';

    protected $primaryKey = 'extension_number';

    public $timestamps = false;

    protected $keyType = 'string';

    protected $guarded = [];
}
