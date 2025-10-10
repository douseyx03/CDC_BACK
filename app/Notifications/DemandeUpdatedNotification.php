<?php

namespace App\Notifications;

use App\Models\Demande;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DemandeUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<int, string>  $changes
     */
    public function __construct(private readonly Demande $demande, private readonly array $changes)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Mise à jour de votre demande #'.$this->demande->id)
            ->greeting('Bonjour '.$notifiable->prenom)
            ->line('Votre demande a été mise à jour par nos équipes :');

        foreach ($this->changes as $change) {
            $mail->line('• '.$change);
        }

        return $mail
            ->action('Consulter ma demande', config('app.frontoffice_url', config('app.url')).'/demandes/'.$this->demande->id)
            ->line('Merci pour votre confiance.');
    }
}
