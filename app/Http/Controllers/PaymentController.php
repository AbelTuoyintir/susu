<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use App\Models\Contribution;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Book;
use App\Models\Ledger;
use App\Notifications\PaymentReceived;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class PaymentController extends Controller
{
    public function verify(Request $request)
    {
        $reference = $request->reference;
        if (!$reference) {
            return response()->json(['status' => false, 'message' => 'No reference supplied'], 400);
        }

        $secretKey = config('services.paystack.secret_key');

        $response = Http::withToken($secretKey)
            ->get("https://api.paystack.co/transaction/verify/" . rawurlencode($reference));

        if (!$response->successful()) {
            return response()->json(['status' => false, 'message' => 'Failed to verify transaction'], 400);
        }

        $data = $response->json();
        if ($data['status'] && $data['data']['status'] === 'success') {
            // SECURITY: Verify the actual amount paid (in kobo/pesewas)
            $actualAmountPaid = $data['data']['amount'] / 100;

            $metadata = $data['data']['metadata'];
            $userId = $metadata['user_id'];
            $paymentType = $metadata['payment_type'];

            // Determine expected amount from metadata
            $expectedAmount = 0;
            if ($paymentType === 'contribution') {
                $expectedAmount = $metadata['amount_to_pay'] + $metadata['welfare_to_pay'];
            } elseif ($paymentType === 'loan') {
                $expectedAmount = $metadata['loan_payment_amount'];
            }

            // Cross-check amounts
            if (abs($actualAmountPaid - $expectedAmount) > 0.01) {
                Log::error("Payment amount mismatch for ref: $reference. Expected: $expectedAmount, Paid: $actualAmountPaid");
                return response()->json(['status' => false, 'message' => 'Payment amount mismatch'], 400);
            }

            // Check if payment already processed
            if (Payment::where('transaction_id', $reference)->exists()) {
                return response()->json(['status' => true, 'message' => 'Already processed']);
            }

            if ($paymentType === 'contribution') {
                $bookId = $metadata['book_id'];

                try {
                    $result = DB::transaction(function () use ($bookId, $userId, $reference, $actualAmountPaid, $metadata) {
                        $book = Book::where('id', $bookId)->lockForUpdate()->first();
                        if (!$book) {
                            return ['status' => false, 'message' => 'Invalid book', 'code' => 400];
                        }

                        // Refine week_number determination with a lock or check
                        $nextWeek = (Contribution::where('book_id', $bookId)->max('week_number') ?? 0) + 1;

                        // Double check for duplicate week record to avoid race conditions
                        if (Contribution::where('book_id', $bookId)->where('week_number', $nextWeek)->exists()) {
                            Log::error("Duplicate week $nextWeek for book $bookId detected during payment processing of ref: $reference");
                            return ['status' => false, 'message' => 'This week payment has already been recorded', 'code' => 400];
                        }

                        // Validate against settings
                        $expectedContribution = $book->contribution_amount;
                        $expectedWelfare = (float) \App\Models\Setting::val('welfare_amount', 10);

                        $amountToPay = $metadata['amount_to_pay'];
                        $welfareToPay = $metadata['welfare_to_pay'];

                        if (abs($amountToPay - $expectedContribution) > 0.01 || abs($welfareToPay - $expectedWelfare) > 0.01) {
                            Log::error("Payment amount tampered for ref: $reference. Expected Contrib: $expectedContribution, Got: $amountToPay. Expected Welfare: $expectedWelfare, Got: $welfareToPay");
                            return ['status' => false, 'message' => 'Invalid payment breakdown', 'code' => 400];
                        }

                        // 1. Create Contribution
                        Contribution::create([
                            'user_id' => $userId,
                            'book_id' => $bookId,
                            'week_number' => $nextWeek,
                            'contribution' => $amountToPay,
                            'welfare' => $welfareToPay,
                            'penalty' => 0,
                            'is_missed' => false,
                        ]);

                        // 2. Create Ledger entries
                        Ledger::create([
                            'user_id' => $userId,
                            'book_id' => $bookId,
                            'type' => 'contribution',
                            'amount' => $amountToPay,
                            'week_number' => $nextWeek,
                            'description' => "Contribution for Week $nextWeek (via Paystack)",
                        ]);

                        Ledger::create([
                            'user_id' => $userId,
                            'book_id' => $bookId,
                            'type' => 'welfare',
                            'amount' => $welfareToPay,
                            'week_number' => $nextWeek,
                            'description' => "Welfare for Week $nextWeek (via Paystack)",
                        ]);

                        // 3. Create Contribution Payment record
                        Payment::create([
                            'user_id' => $userId,
                            'book_id' => $bookId,
                            'payment_type' => 'contribution',
                            'transaction_id' => $reference,
                            'payment_method' => 'card',
                            'amount_paid' => $actualAmountPaid,
                            'status' => 'completed',
                            'paid_at' => now(),
                        ]);

                        return ['status' => true, 'nextWeek' => $nextWeek, 'amountToPay' => $amountToPay, 'welfareToPay' => $welfareToPay];
                    });

                    if (!$result['status']) {
                        return response()->json(['status' => false, 'message' => $result['message']], $result['code']);
                    }

                    // Notify User
                    $user = \App\Models\User::find($userId);
                    if ($user) {
                        $nextWeek = $result['nextWeek'];
                        $amountToPay = $result['amountToPay'];
                        $welfareToPay = $result['welfareToPay'];

                        $user->notify(new PaymentReceived([
                            'title' => 'Contribution Received',
                            'message' => "Your contribution for Week " . $nextWeek . " (GH₵ " . number_format($amountToPay + $welfareToPay, 2) . ") has been received.",
                            'amount' => $amountToPay + $welfareToPay,
                            'transaction_id' => $reference,
                        ]));

                        // Notify Admins
                        $admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->get();
                        Notification::send($admins, new PaymentReceived([
                            'title' => 'Contribution Received (Admin)',
                            'message' => "A contribution of GH₵ " . number_format($amountToPay + $welfareToPay, 2) . " has been received from " . $user->name . " for Week $nextWeek.",
                            'amount' => $amountToPay + $welfareToPay,
                            'transaction_id' => $reference,
                        ]));
                    }

                } catch (\Exception $e) {
                    Log::error("Database transaction failed during contribution payment verification: " . $e->getMessage());
                    return response()->json(['status' => false, 'message' => 'An error occurred during payment processing'], 500);
                }

            } elseif ($paymentType === 'loan') {
                $loanId = $metadata['loan_id'];
                $amountPaid = $metadata['loan_payment_amount'];

                try {
                    $result = DB::transaction(function () use ($loanId, $userId, $amountPaid, $reference, $actualAmountPaid) {
                        $loan = Loan::where('id', $loanId)->lockForUpdate()->first();
                        if (!$loan) {
                            return ['status' => false, 'message' => 'Invalid loan', 'code' => 400];
                        }

                        // SECURITY: Prevent cross-user payment fraud
                        if ($loan->user_id != $userId) {
                            Log::error("Cross-user payment attempt: metadata user $userId tries to pay for loan owned by " . $loan->user_id);
                            return ['status' => false, 'message' => 'Unauthorized loan payment', 'code' => 403];
                        }

                        // SECURITY: Prevent overpayment
                        $totalOwed = $loan->amount + $loan->interest;
                        $remainingBalance = $totalOwed - $loan->amount_repaid;

                        if ($amountPaid > $remainingBalance + 0.01) {
                            Log::error("Loan overpayment attempt for loan $loanId. Owed: $remainingBalance, Paid: $amountPaid");
                            return ['status' => false, 'message' => 'Payment exceeds outstanding balance', 'code' => 400];
                        }

                        LoanPayment::create([
                            'loan_id' => $loan->id,
                            'amount_paid' => $amountPaid,
                        ]);

                        // Create Ledger entry for repayment
                        Ledger::create([
                            'user_id' => $userId,
                            'book_id' => $loan->book_id,
                            'type' => 'repayment',
                            'amount' => $amountPaid,
                            'description' => "Loan repayment for Loan #LN-" . sprintf('%04d', $loan->id) . " (via Paystack)",
                        ]);

                        Payment::create([
                            'user_id' => $userId,
                            'loan_id' => $loan->id,
                            'payment_type' => 'loan_repayment',
                            'transaction_id' => $reference,
                            'payment_method' => 'card',
                            'amount_paid' => $actualAmountPaid,
                            'status' => 'completed',
                            'paid_at' => now(),
                        ]);

                        $loan->refresh();
                        $totalOwed = $loan->amount + $loan->interest;
                        if ($loan->amount_repaid >= $totalOwed) {
                            $loan->update(['status' => 'paid']);
                        }

                        return ['status' => true, 'loan' => $loan];
                    });

                    if (!$result['status']) {
                        return response()->json(['status' => false, 'message' => $result['message']], $result['code']);
                    }

                    $loan = $result['loan'];

                    // Notify User
                    $loan->user->notify(new PaymentReceived([
                        'title' => 'Loan Repayment Received',
                        'message' => "Your loan repayment of GH₵ " . number_format($amountPaid, 2) . " has been successfully processed.",
                        'amount' => $amountPaid,
                        'transaction_id' => $reference,
                    ]));

                    // Notify Admins
                    $admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->get();
                    Notification::send($admins, new PaymentReceived([
                        'title' => 'Loan Repayment Received (Admin)',
                        'message' => "A loan repayment of GH₵ " . number_format($amountPaid, 2) . " has been received from " . $loan->user->name . ".",
                        'amount' => $amountPaid,
                        'transaction_id' => $reference,
                    ]));

                } catch (\Exception $e) {
                    Log::error("Database transaction failed during loan payment verification: " . $e->getMessage());
                    return response()->json(['status' => false, 'message' => 'An error occurred during payment processing'], 500);
                }
            }

            return response()->json(['status' => true, 'message' => 'Payment successful']);
        }

        return response()->json(['status' => false, 'message' => 'Transaction failed'], 400);
    }
}
