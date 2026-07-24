<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Contribution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_verification_prevents_loan_overpayment()
    {
        $user = User::factory()->create(['role' => 'user']);
        $book = Book::create([
            'user_id' => $user->id,
            'book_number' => 'BK-TEST',
            'contribution_amount' => 100,
            'duration_weeks' => 10,
            'start_date' => now(),
            'status' => 'active',
        ]);

        $loan = Loan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'amount' => 500,
            'interest' => 50,
            'due_date' => now()->addDays(30),
            'status' => 'active',
        ]);

        // Total owed is 550.
        // Let's say user already paid 500.
        LoanPayment::create([
            'loan_id' => $loan->id,
            'amount_paid' => 500,
        ]);

        // Remaining is 50.
        // Attempt to pay 100.

        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'amount' => 10000, // 100.00
                    'metadata' => [
                        'user_id' => $user->id,
                        'payment_type' => 'loan',
                        'loan_id' => $loan->id,
                        'loan_payment_amount' => 100,
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson(route('payment.verify'), ['reference' => 'test_ref']);

        $response->assertStatus(400);
        $this->assertEquals('Payment exceeds outstanding balance', $response->json('message'));
    }

    public function test_payment_verification_ensures_user_owns_loan()
    {
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);

        $book = Book::create([
            'user_id' => $user1->id,
            'book_number' => 'BK-TEST',
            'contribution_amount' => 100,
            'duration_weeks' => 10,
            'start_date' => now(),
            'status' => 'active',
        ]);

        $loan = Loan::create([
            'user_id' => $user1->id,
            'book_id' => $book->id,
            'amount' => 500,
            'interest' => 50,
            'due_date' => now()->addDays(30),
            'status' => 'active',
        ]);

        // User 2 tries to pay for User 1's loan
        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'amount' => 10000,
                    'metadata' => [
                        'user_id' => $user2->id,
                        'payment_type' => 'loan',
                        'loan_id' => $loan->id,
                        'loan_payment_amount' => 100,
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson(route('payment.verify'), ['reference' => 'test_ref']);

        // It shouldn't create a payment record for user2 on user1's loan
        $this->assertEquals(0, LoanPayment::count());
    }

    public function test_payment_verification_ensures_user_owns_book()
    {
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);

        $book = Book::create([
            'user_id' => $user1->id,
            'book_number' => 'BK-TEST-2',
            'contribution_amount' => 100,
            'duration_weeks' => 10,
            'start_date' => now(),
            'status' => 'active',
        ]);

        // User 2 tries to pay for User 1's book contribution
        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'amount' => 11000,
                    'metadata' => [
                        'user_id' => $user2->id,
                        'payment_type' => 'contribution',
                        'book_id' => $book->id,
                        'amount_to_pay' => 100,
                        'welfare_to_pay' => 10,
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson(route('payment.verify'), ['reference' => 'test_ref_book']);

        $response->assertStatus(403);
        $this->assertEquals(0, Contribution::count());
    }
}
