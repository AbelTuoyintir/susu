<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Book;
use App\Models\Contribution;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;

class ApplyWeeklyPenalties extends Command
{
    protected $signature = 'penalties:apply';
    protected $description = 'Automatically apply penalties for missed contributions';

    public function handle()
    {
        if (Setting::val('auto_apply_penalties', '0') !== '1') {
            $this->info('Auto-apply penalties is disabled.');
            return;
        }

        $activeBooks = Book::where('status', 'active')->get();
        $penaltyAmount = Setting::val('penalty_amount', 6);

        foreach ($activeBooks as $book) {
            $lastContribution = Contribution::where('book_id', $book->id)->latest('week_number')->first();
            $nextWeekNumber = ($lastContribution ? $lastContribution->week_number : 0) + 1;

            // Logic to determine if a week is missed.
            // In a real system, this would check if today is past the weekly deadline.
            // For this implementation, we check if there's no contribution for the current calculated week.

            $alreadyRecorded = Contribution::where('book_id', $book->id)
                ->where('week_number', $nextWeekNumber)
                ->exists();

            if (!$alreadyRecorded) {
                 Contribution::create([
                    'user_id' => $book->user_id,
                    'book_id' => $book->id,
                    'week_number' => $nextWeekNumber,
                    'contribution' => 0,
                    'welfare' => 0,
                    'penalty' => $penaltyAmount,
                    'is_missed' => true,
                ]);
                $this->info("Penalty applied to Book #{$book->book_number} for Week {$nextWeekNumber}");
            }
        }

        $this->info('Weekly penalty check complete.');
    }
}
