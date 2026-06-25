<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Loan;
use App\Notifications\LoanPaymentReminder;
use Carbon\Carbon;

class RemindLoanPayments extends Command
{
    protected $signature = 'remind:loans';
    protected $description = 'Send reminders for loans due soon';

    public function handle()
    {
        $dueSoon = Loan::where('status', 'active')
            ->whereDate('due_date', '<=', Carbon::now()->addDays(3))
            ->get();

        foreach ($dueSoon as $loan) {
            // SPAM PREVENTION: Check if we already sent a reminder in the last 24 hours
            $alreadyNotified = $loan->user->notifications()
                ->where('data->loan_id', $loan->id)
                ->where('data->type', 'loan_reminder')
                ->where('created_at', '>=', Carbon::now()->subDay())
                ->exists();

            if (!$alreadyNotified) {
                $loan->user->notify(new LoanPaymentReminder($loan));
            }
        }

        $this->info('Reminders sent.');
    }
}
