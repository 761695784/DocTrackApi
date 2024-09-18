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
        // Récupérer le numéro de téléPhone du propriétaire du document
        $Phone = $document->user->Phone; // Assurez-vous que 'Phone' est bien le nom de votre colonne
        $documentUrl = route('documents.show', $document->id); // Créer l'URL pour afficher le document

        // Envoyer le mail avec le document et le numéro de téléPhone
        Mail::to($user->email)->send(new DocumentPublishedNotification($document, $Phone, $documentUrl));
    }
}
