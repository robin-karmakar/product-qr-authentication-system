<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaffAccountCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $token)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->email,
        ], false));

        return (new MailMessage)
            ->subject('Your SPATS Account Has Been Created')
            ->greeting("Hello {$notifiable->name},")
            ->line('An administrator has created an account for you on the Secure Product Authentication and Traceability System.')
            ->line('Click below to set your password and activate your account. This link expires in 60 minutes.')
            ->action('Set Your Password', $url)
            ->line('If you were not expecting this account, no further action is required.');
    }
}
