<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Discord\DiscordChannel;
use NotificationChannels\Discord\DiscordMessage;
use Illuminate\Notifications\Messages\MailMessage;

class UserPurged extends Notification
{
    use Queueable;

    /**
     * The collections of messages.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $passes;
    protected $fails;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($passes, $fails)
    {
        $this->passes = $passes;
        $this->fails = $fails;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [DiscordChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    /**
     * Discord message.
     *
     * @return any
     */
    public function toDiscord($notifiable)
    {
        return DiscordMessage::create(
            "{$this->passes->implode("\n")}\n\n{$this->fails->implode("\n")}"
        );
    }
}
