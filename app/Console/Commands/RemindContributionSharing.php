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
                $admin->notify(new ContributionSharingReminder($book));
            }
        }

        $this->info('Sharing reminders sent to admins.');
    }
}
