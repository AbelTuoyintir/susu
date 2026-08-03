<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Book;
use App\Models\Contribution;
use App\Models\Setting;
use App\Models\Tenant;

class ApplyWeeklyPenalties extends Command
{
    protected $signature = 'penalties:apply';
    protected $description = 'Automatically apply penalties for missed contributions';

    public function handle()
    {
        $tenants = Tenant::all();
        if ($tenants->isEmpty()) {
            $this->applyForCurrentTenant();
        } else {
            foreach ($tenants as $tenant) {
                Tenant::forTenant($tenant->id, function () use ($tenant) {
                    $this->info("Applying penalties for tenant: {$tenant->name}");
                    $this->applyForCurrentTenant();
                });
            }
        }

        $this->info('Weekly penalty check complete.');
    }

    protected function applyForCurrentTenant()
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
    }
}
