<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Ledger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    // 💰 Request Loan
    public function requestLoan(Request $request, $bookId)
    {
        $book = Book::findOrFail($bookId);

        if ($book->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        // 🧠 Calculate savings (Only actual contributions count towards the 100% policy)
        $totalSaved = Contribution::where('book_id', $book->id)
            ->where('is_missed', false)
            ->sum('contribution');

        $maxLoan = round($totalSaved, 2);

        // Policy: Loan amount must be EXACTLY 100% of total savings
        if (abs($request->amount - $maxLoan) > 0.01) {
            return response()->json([
                'error' => 'Loan amount must be exactly GH₵ ' . number_format($maxLoan, 2) . ' (100% of savings).'
            ], 400);
        }

        // 🚫 Check existing loan
        if ($book->loans()->where('status', 'pending')->exists()) {
            return response()->json([
                'error' => 'Clear existing loan first'
            ], 400);
        }

        $interest = 0.10 * $request->amount;

        $loan = Loan::create([
            'user_id' => auth()->id(),
            'book_id' => $book->id,
            'amount' => $request->amount,
            'interest' => $interest,
            'due_date' => now()->addMonth(),
        ]);

        // Ledger record
        Ledger::create([
            'user_id' => auth()->id(),
            'book_id' => $book->id,
            'type' => 'loan',
            'amount' => -$request->amount,
            'description' => 'Loan taken',
        ]);

        return response()->json($loan);
    }

    // 💳 Repay Loan
    public function repay(Request $request, $loanId)
    {
        $loan = Loan::findOrFail($loanId);

        if ($loan->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        DB::transaction(function () use ($loan, $request) {

            $loan->amount -= $request->amount;

            if ($loan->amount <= 0) {
                $loan->status = 'paid';
            }

            $loan->save();

            Ledger::create([
                'user_id' => auth()->id(),
                'book_id' => $loan->book_id,
                'type' => 'repayment',
                'amount' => $request->amount,
                'description' => 'Loan repayment',
            ]);
        });

        return response()->json(['message' => 'Payment successful']);
    }

    // ⚠️ Handle Default (can be cron job)
    public function checkDefaults()
    {
        $loans = Loan::where('status', 'pending')->get();

        foreach ($loans as $loan) {
            if (now()->gt($loan->due_date)) {
                $loan->amount += $loan->interest;
                $loan->status = 'defaulted';
                $loan->save();
            }
        }

        return response()->json(['message' => 'Defaults updated']);
    }
}