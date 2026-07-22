<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Book;
use App\Models\User;
use App\Notifications\ContributionSharingReminder;
use Carbon\Carbon;

class RemindContributionSharing extends Command
{
    protected $signature = 'remind:sharing';
    protected $description = 'Remind admin to share contributions for books that ended';

    public function handle()
    {
        $endedBooks = Book::where('status', 'active')
            ->whereDate('end_date', '<=', Carbon::now())
            ->with('user')
            ->get();

        $admins = User::whereIn('role', ['admin', 'super_admin'])->get();

        foreach ($endedBooks as $book) {
            foreach ($admins as $admin) {
                // SPAM PREVENTION: Check if we already sent a reminder in the last 24 hours for this book
                $alreadyNotified = $admin->notifications()
                    ->where('data->book_id', $book->id)
                    ->where('data->type', 'sharing_reminder')
                    ->where('created_at', '>=', Carbon::now()->subDay())
                    ->exists();

                if (!$alreadyNotified) {
                    $admin->notify(new ContributionSharingReminder($book));
                }
            }
        }

        $this->info('Sharing reminders sent to admins.');
    }
}
