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

   // App\Mail\DocumentPublishedNotification.php
public $document;

public function __construct(Document $document)
{
    $this->document = $document;
}

public function build()
{
    return $this->subject('Document publié correspondant à votre déclaration de perte')
                ->markdown('emails.document.published', ['document' => $this->document]);
}
}
