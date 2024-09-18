<?php

namespace App\Mail;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class DocumentPublishedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $document;
    public $Phone; // Ajoutez la propriété Phone

    public function __construct($document, $Phone)
    {
        $this->document = $document;
        $this->Phone = $Phone;
    }

    public function build()
    {
        return $this->subject('Document publié correspondant à votre déclaration de perte')
                    ->markdown('emails.document.published', [
                        'document' => $this->document,
                        'phone' => $this->Phone // Corrigez ici
                    ]);
    }
}
