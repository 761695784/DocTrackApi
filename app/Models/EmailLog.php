<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'from', 'to', 'subject', 'body', 'publisher_user_id', 'requester_user_id', 'document_id', 'declarant_user_id',
    ];
    }



