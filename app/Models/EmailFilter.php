<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailFilter extends Model
{
    protected $fillable = ['user_id', 'operator', 'value', 'operation'];
}
