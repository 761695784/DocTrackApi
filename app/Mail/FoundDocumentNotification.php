<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FoundDocumentNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $finderPhone;
    /**
     * Create a new message instance.
     */
    public function __construct($finderPhone)
    {
        $this->finderPhone = $finderPhone;
    }

    /**
     * Build the message.
     *
     */
    public function build()
    {
        return $this->subject('Votre document a été trouvé')
                    ->view('emails.found_document')
                    ->with(['finderPhone' => $this->finderPhone]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Found Document Notification',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'view.name',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
