<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Contribution;
use App\Models\Ledger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContributionController extends Controller
{
    public function store(Request $request, $bookId)
    {
        $book = Book::findOrFail($bookId);

        // 🔐 Security
        if ($book->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'week_number' => 'required|integer|min:1'
        ]);

        $currentWeek = $request->week_number;

        // 🧠 Get last paid week
        $lastWeek = Contribution::where('book_id', $book->id)
            ->max('week_number') ?? 0;

        $missedWeeks = $currentWeek - $lastWeek - 1;

        $contribution = $book->contribution_amount;
        $welfare = (float) \App\Models\Setting::val('welfare_amount', 10);
        $penalty = (float) \App\Models\Setting::val('penalty_amount', 6);

        DB::transaction(function () use ($book, $currentWeek, $missedWeeks, $contribution, $welfare, $penalty) {

            // 🔁 Handle missed weeks
            for ($i = 1; $i <= $missedWeeks; $i++) {

                $week = ($currentWeek - $missedWeeks) + ($i - 1);

                Contribution::create([
                    'user_id' => auth()->id(),
                    'book_id' => $book->id,
                    'week_number' => $week,
                    'contribution' => $contribution,
                    'welfare' => $welfare,
                    'penalty' => $penalty,
                    'is_missed' => true,
                ]);

                Ledger::create([
                    'user_id' => auth()->id(),
                    'book_id' => $book->id,
                    'type' => 'penalty',
                    'amount' => $penalty,
                    'week_number' => $week,
                    'description' => "Missed week {$week}",
                ]);
            }

            // ✅ Current week payment
            Contribution::create([
                'user_id' => auth()->id(),
                'book_id' => $book->id,
                'week_number' => $currentWeek,
                'contribution' => $contribution,
                'welfare' => $welfare,
            ]);

            // Ledger entries
            Ledger::create([
                'user_id' => auth()->id(),
                'book_id' => $book->id,
                'type' => 'contribution',
                'amount' => $contribution,
                'week_number' => $currentWeek,
            ]);

            Ledger::create([
                'user_id' => auth()->id(),
                'book_id' => $book->id,
                'type' => 'welfare',
                'amount' => $welfare,
                'week_number' => $currentWeek,
            ]);
        });

        return response()->json(['message' => 'Contribution recorded']);
    }
}