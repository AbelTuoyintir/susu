<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    /**
     * Display all books for logged-in user
     */
    public function index()
    {
        $books = auth()->user()->books()->latest()->get();

        return response()->json($books);
    }

    /**
     * Store a new book
     */
    public function store(Request $request)
    {
        $request->validate([
            'contribution_amount' => 'required|numeric|min:1',
            'start_date' => 'required|date',
        ]);

        $user = auth()->user();

        // 🚫 Rule: Max 3 books
        if ($user->books()->count() >= 3) {
            return response()->json([
                'error' => 'You can only own a maximum of 3 books'
            ], 403);
        }

        $book = DB::transaction(function () use ($request, $user) {

            return Book::create([
                'user_id' => $user->id,
                'book_number' => 'BOOK-' . strtoupper(Str::random(8)),
                'contribution_amount' => $request->contribution_amount,
                'duration_weeks' => 55,
                'start_date' => $request->start_date,
                'status' => 'active',
            ]);
        });

        return response()->json([
            'message' => 'Book created successfully',
            'book' => $book
        ]);
    }

    /**
     * Show a single book
     */
    public function show($id)
    {
        $book = Book::findOrFail($id);

        // 🔐 Security: Ensure owner
        if ($book->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        return response()->json($book);
    }

    /**
     * Update book (optional)
     */
    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);

        // 🔐 Security
        if ($book->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'contribution_amount' => 'sometimes|numeric|min:1',
        ]);

        $book->update($request->only('contribution_amount'));

        return response()->json([
            'message' => 'Book updated',
            'book' => $book
        ]);
    }

    /**
     * Delete a book
     */
    public function destroy($id)
    {
        $book = Book::findOrFail($id);

        // 🔐 Security
        if ($book->user_id !== auth()->id()) {
            abort(403);
        }

        // ⚠️ Business Rule: Prevent deletion if active transactions exist
        if ($book->contributions()->exists() || $book->loans()->exists()) {
            return response()->json([
                'error' => 'Cannot delete book with transactions'
            ], 400);
        }

        $book->delete();

        return response()->json([
            'message' => 'Book deleted successfully'
        ]);
    }

    /**
     * Get book summary (VERY IMPORTANT)
     */
    public function summary($id)
    {
        $book = Book::findOrFail($id);

        // 🔐 Security
        if ($book->user_id !== auth()->id()) {
            abort(403);
        }

        $totalContribution = $book->ledgers()
            ->where('type', 'contribution')
            ->sum('amount');

        $totalWelfare = $book->ledgers()
            ->where('type', 'welfare')
            ->sum('amount');

        $totalPenalty = $book->ledgers()
            ->where('type', 'penalty')
            ->sum('amount');

        $totalLoan = $book->loans()->sum('amount');

        return response()->json([
            'book' => $book,
            'summary' => [
                'contribution' => $totalContribution,
                'welfare' => $totalWelfare,
                'penalty' => $totalPenalty,
                'loan' => $totalLoan,
                'balance' => ($totalContribution + $totalWelfare) - $totalLoan
            ]
        ]);
    }
}