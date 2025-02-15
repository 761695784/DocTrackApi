<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
{
    use HasFactory; use SoftDeletes;
    protected $guarded = [];

    public function documentType() {
        return $this->belongsTo(DocumentType::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
    public function comments() {
        return $this->hasMany(Commentaire::class);
    }
}
