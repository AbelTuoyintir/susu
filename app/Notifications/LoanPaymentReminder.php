<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanPaymentReminder extends Notification
{
    use Queueable;

    protected $loan;

    /**
     * Create a new notification instance.
     */
    public function __construct($loan)
    {
        $this->loan = $loan;
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
            'title' => 'Loan Repayment Reminder',
            'message' => 'Your loan of GH₵ ' . number_format($this->loan->amount, 2) . ' is due on ' . $this->loan->due_date . '.',
            'loan_id' => $this->loan->id,
            'type' => 'loan_reminder',
        ];
    }
}
