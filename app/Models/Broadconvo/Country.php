<?php

namespace App\Models\Broadconvo;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $connection = 'broadconvo';

    protected $table = 'country';

    protected $primaryKey = 'country_id';

    public $timestamps = false;

    protected $keyType = 'string';

    protected $guarded = [];
}
