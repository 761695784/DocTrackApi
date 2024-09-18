<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentPublishedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $document;
    public $Phone;
    public $documentUrl;  // Ajout de cette propriété

    public function __construct($document, $Phone, $documentUrl)
    {
        $this->document = $document;
        $this->Phone = $Phone;        // Certains paramètres changés pour la cohérence
        $this->documentUrl = $documentUrl; // Initialisez la propriété
    }

    public function build()
    {
        return $this->subject('Correspondance à votre déclaration de perte')
                    ->markdown('emails.document.published', [
                        'document' => $this->document,
                        'Phone' => $this->Phone,
                        'documentUrl' => $this->documentUrl, // Passez l'URL
                    ]);
    }
}
