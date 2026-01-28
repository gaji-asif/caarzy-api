<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FriendRequestReceived extends Notification
{

    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $frontendUrl = config('app.frontend_url') ?? env('FRONTEND_URL');
        $url = $frontendUrl.'/friends?tab=received';
        
        return (new MailMessage)
            ->subject("Sinulla on uusi kaveripyyntö Nesti-sovelluksessa")
            ->greeting("Hei " . $notifiable->name . ",")
            ->line("Olet saanut uuden kaveripyynnön käyttäjältä **{$this->user->name}**. Voit hyväksyä tai hylätä pyynnön profiilisi Kaveripyynnöt-osiossa.")
            ->action('Näytä kaveripyynnöt', $url)
            ->line('Kiitos, että käytät NestiCommunitya!')
            ->salutation('Terveisin, Nesti-tiimi'); // ← CUSTOM FOOTER

    }
}
