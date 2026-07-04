<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use App\Models\Contribution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Volt\Volt;

class LoanPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_loan_amount_must_match_savings()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $book = Book::create([
            'user_id' => $user->id,
            'book_number' => 'BK-001',
            'contribution_amount' => 100,
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addYear(),
        ]);

        // Total savings = 200
        Contribution::create(['user_id' => $user->id, 'book_id' => $book->id, 'week_number' => 1, 'contribution' => 100, 'welfare' => 10, 'penalty' => 0, 'is_missed' => false]);
        Contribution::create(['user_id' => $user->id, 'book_id' => $book->id, 'week_number' => 2, 'contribution' => 100, 'welfare' => 10, 'penalty' => 0, 'is_missed' => false]);

        $this->actingAs($admin);

        Volt::test('pages.admin.add-loan')
            ->set('user_id', $user->id)
            ->set('book_id', $book->id)
            ->set('amount', 150) // Invalid amount
            ->set('interest', 15)
            ->set('due_date', now()->addDays(30)->format('Y-m-d'))
            ->call('save')
            ->assertHasErrors(['amount' => 'in']);

        Volt::test('pages.admin.add-loan')
            ->set('user_id', $user->id)
            ->set('book_id', $book->id)
            ->set('amount', 200) // Valid amount
            ->set('interest', 20)
            ->set('due_date', now()->addDays(30)->format('Y-m-d'))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('loans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'amount' => 200,
        ]);
    }
}
