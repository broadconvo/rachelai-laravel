<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GmailSentItem extends Model
{
    protected $table = 'sent_items';

    protected $fillable = ['user_id', 'message_id', 'subject', 'content', 'filename'];
}
