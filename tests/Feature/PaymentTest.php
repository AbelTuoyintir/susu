<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use App\Models\Contribution;
use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_verification_logic()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $book = Book::create([
            'user_id' => $user->id,
            'book_number' => 'BK-001',
            'contribution_amount' => 120,
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addYear(),
        ]);

        \App\Models\Setting::create(['key' => 'welfare_amount', 'value' => '10']);

        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'amount' => 13000, // 130.00
                    'metadata' => [
                        'user_id' => $user->id,
                        'payment_type' => 'contribution',
                        'book_id' => $book->id,
                        'amount_to_pay' => 120,
                        'welfare_to_pay' => 10,
                    ]
                ]
            ], 200),
        ]);

        $response = $this->postJson(route('payment.verify'), ['reference' => 'test-ref']);

        $response->assertStatus(200)
                 ->assertJson(['status' => true]);

        $this->assertDatabaseHas('contributions', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'contribution' => 120,
            'welfare' => 10,
        ]);
    }
}
