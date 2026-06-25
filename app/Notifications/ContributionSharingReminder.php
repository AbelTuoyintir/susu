<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContributionSharingReminder extends Notification
{
    use Queueable;

    protected $book;

    /**
     * Create a new notification instance.
     */
    public function __construct($book)
    {
        $this->book = $book;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Contribution Sharing Reminder',
            'message' => 'Book #' . $this->book->book_number . ' for ' . $this->book->user->name . ' has reached its end date. Please prepare for sharing.',
            'book_id' => $this->book->id,
            'type' => 'sharing_reminder',
        ];
    }
}
