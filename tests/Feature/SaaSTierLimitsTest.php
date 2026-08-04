<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use App\Models\Tenant;
use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Volt\Volt;

class SaaSTierLimitsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_limit_is_enforced()
    {
        $tenant = Tenant::create(['name' => 'Acme Inc', 'slug' => 'acme', 'plan' => 'free']);
        Tenant::setTenantId($tenant->id);

        $admin = User::factory()->create(['role' => 'admin', 'tenant_id' => $tenant->id]);
        $this->actingAs($admin);

        // Create 9 other users (total 10 with admin)
        User::factory()->count(9)->create(['tenant_id' => $tenant->id]);

        $this->assertEquals(10, $tenant->getUsage('users'));
        $this->assertTrue($tenant->hasReachedLimit('users'));

        // Attempting to add one more user via controller should fail
        $response = $this->post(route('users.store'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'password' => 'secret123',
            'role' => 'user',
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertEquals(10, User::count());
    }

    public function test_book_limit_is_enforced()
    {
        $tenant = Tenant::create(['name' => 'Acme Inc', 'slug' => 'acme', 'plan' => 'free']);
        Tenant::setTenantId($tenant->id);

        $admin = User::factory()->create(['role' => 'admin', 'tenant_id' => $tenant->id]);
        $this->actingAs($admin);

        // Limit for books on free plan is 10. Let's create 10 books.
        for ($i = 0; $i < 10; $i++) {
            Book::create([
                'user_id' => $admin->id,
                'book_number' => 'BK-' . $i,
                'contribution_amount' => 100,
                'status' => 'active',
                'start_date' => now(),
                'tenant_id' => $tenant->id,
            ]);
        }

        $this->assertEquals(10, $tenant->getUsage('books'));
        $this->assertTrue($tenant->hasReachedLimit('books'));

        // Attempt to add 11th book via Livewire
        Volt::test('pages.admin.add-book')
            ->set('user_id', $admin->id)
            ->set('contribution_amount', 100)
            ->set('start_date', now()->format('Y-m-d'))
            ->call('save')
            ->assertHasErrors(['contribution_amount']);

        $this->assertEquals(10, Book::count());
    }

    public function test_loan_limit_is_enforced()
    {
        $tenant = Tenant::create(['name' => 'Acme Inc', 'slug' => 'acme', 'plan' => 'free']);
        Tenant::setTenantId($tenant->id);

        $admin = User::factory()->create(['role' => 'admin', 'tenant_id' => $tenant->id]);
        $user = User::factory()->create(['role' => 'user', 'tenant_id' => $tenant->id]);
        $this->actingAs($admin);

        // Limit for loans on free plan is 5. Let's create 5 books and 5 loans.
        for ($i = 0; $i < 5; $i++) {
            $book = Book::create([
                'user_id' => $user->id,
                'book_number' => 'BK-' . $i,
                'contribution_amount' => 100,
                'status' => 'active',
                'start_date' => now(),
                'tenant_id' => $tenant->id,
            ]);

            Loan::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'amount' => 100,
                'interest' => 10,
                'due_date' => now()->addDays(30),
                'status' => 'pending',
                'tenant_id' => $tenant->id,
            ]);
        }

        $this->assertEquals(5, $tenant->getUsage('loans'));
        $this->assertTrue($tenant->hasReachedLimit('loans'));

        // Attempt to request 6th loan via Admin Livewire form
        $extraBook = Book::create([
            'user_id' => $user->id,
            'book_number' => 'BK-EXTRA',
            'contribution_amount' => 100,
            'status' => 'active',
            'start_date' => now(),
            'tenant_id' => $tenant->id,
        ]);

        Volt::test('pages.admin.add-loan')
            ->set('user_id', $user->id)
            ->set('book_id', $extraBook->id)
            ->set('amount', 100)
            ->set('interest', 10)
            ->set('due_date', now()->addDays(30)->format('Y-m-d'))
            ->call('save')
            ->assertHasErrors(['amount']);

        $this->assertEquals(5, Loan::count());
    }
}
