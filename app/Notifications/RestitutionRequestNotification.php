<?php
namespace App\Notifications;

use App\Models\User;
use App\Models\Document;
use App\Models\EmailLog; // Assurez-vous d'importer le modèle EmailLog
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

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
        // Crée le mail
        $mailMessage = (new MailMessage)
            ->subject('Demande de restitution du document')
            ->greeting('Bonjour ' . $notifiable->FirstName . ',')
            ->line('Nous vous informons qu\'un utilisateur a formulé une demande de restitution pour le document que vous avez publié.')
            ->line('Informations sur l\'utilisateur ayant fait la demande :')
            ->line('Prénom et Nom :' . $this->fromUser->FirstName . ' ' . $this->fromUser->LastName)
            ->line('Email : ' . $this->fromUser->email)
            ->line('Téléphone : ' . $this->fromUser->Phone)
            ->line('Pour plus de détails, veuillez cliquer sur le bouton ci-dessous :')
            ->action('Voir le document', url('https://sendoctrack.netlify.app/document/' . $this->document->id))
            ->line('Nous vous remercions de votre confiance se manifestant par l\'utilisation de notre plateforme.')
            ->line('Cordialement,')
            ->line('L’équipe de Sénégal DockTrack');

        // Enregistre le log de l'email dans la base de données
        EmailLog::create([
            'from' => config('mail.from.address'),
            'to' => $notifiable->email,
            'subject' => $mailMessage->subject,
            'body' => implode("\n", $mailMessage->introLines), // Combine les lignes du message pour le corps
        ]);

        // Debugging: Confirme que l'insertion a été réalisée
        Log::info('Email log inséré avec succès pour ' . $notifiable->email);

        // Retourne l'instance MailMessage pour l'envoi
        return $mailMessage;
    }
}
