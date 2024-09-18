<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Mail\DocumentPublishedNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeclarationDePerte extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function documenttype() {
        return $this->belongsTo(Document::class);
    }
    public function user() {
        return $this->belongsTo(User::class);
    }

    private function sendNotificationEmail($user, $document)
{
    Mail::to($user->email)->send(new DocumentPublishedNotification($document));
}
}
