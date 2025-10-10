<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AgentCredentialsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $temporaryPassword)
    {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $loginUrl = config('app.backoffice_url') ?? config('app.url');

        return (new MailMessage)
            ->subject('Vos accès agent CDC')
            ->greeting('Bonjour '.$notifiable->prenom)
            ->line('Votre compte agent a été créé. Vous pouvez dès à présent accéder au back-office pour traiter les demandes.')
            ->line('Identifiant : '.$notifiable->email)
            ->line('Mot de passe provisoire : '.$this->temporaryPassword)
            ->action('Accéder au back-office', $loginUrl)
            ->line("Pour des raisons de sécurité, merci de modifier ce mot de passe dès votre première connexion.");
    }
}
