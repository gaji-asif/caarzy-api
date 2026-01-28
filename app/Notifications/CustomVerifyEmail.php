<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmail extends VerifyEmail
{
    public function toMail($notifiable)
    {
        // $frontendUrl = config('app.frontend_url') ?? env('FRONTEND_URL');
        // $url = $frontendUrl.'/reset-password?token='.$this->token.'&email='.urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject('Vahvista sähköpostiosoitteesi') // Finnish subject
            ->greeting('Moi ' . $notifiable->name . ',')
            ->line('Kiitos rekisteröitymisestä Nesti Communityyn!')
            ->line('Vahvista sähköpostiosoitteesi alla olevasta painikkeesta.')
            ->action('Vahvista sähköposti', $this->verificationUrl($notifiable))
            ->line('Jos et luonut tätä tiliä, voit sivuuttaa tämän viestin.')
            // ->line('Jos sinulla on ongelmia painikkeen kanssa, kopioi ja liitä alla oleva linkki selaimeesi:')
            // ->line($this->verificationUrl($notifiable));
             ->salutation("Terveisin,\nNesti Community");
    }
}
