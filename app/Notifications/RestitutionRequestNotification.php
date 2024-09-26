<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\Document;

class RestitutionRequestNotification extends Notification
{
    use Queueable;

    public $fromUser; // Utilisateur connecté qui demande la restitution
    public $document; // Document concerné

    public function __construct(User $fromUser, Document $document)
    {
        $this->fromUser = $fromUser;
        $this->document = $document;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
        ->subject('Demande de restitution du document')
        ->greeting('Bonjour ' . $notifiable->FirstName . ',')
        ->line('Nous vous informons qu\'un utilisateur a formulé une demande de restitution pour le document que vous avez publié.')
        ->line('Informations sur l\'utilisateur ayant fait la demande :')
        ->line('Nom : ' . $this->fromUser->FirstName . ' ' . $this->fromUser->LastName)
        ->line('Email : ' . $this->fromUser->email)
        ->line('Téléphone : ' . $this->fromUser->Phone)
        ->line('Pour plus de détails, veuillez cliquer sur le bouton ci-dessous :')
        ->action('Voir le document', url('/documents/' . $this->document->id))
        ->line('Nous vous remercions de votre confiance et de votre utilisation de notre plateforme.')
        ->line('Cordialement,')
        ->line('L’équipe de Sénégal DockTrack');

    }
}
