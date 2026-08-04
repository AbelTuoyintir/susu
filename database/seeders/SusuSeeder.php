<?php
 
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Book;
use App\Models\Contribution;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Payment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SusuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key constraints during seeding
        Schema::disableForeignKeyConstraints();

        // Truncate tables and reset sqlite auto-increments
        DB::table('loan_payments')->delete();
        DB::table('payments')->delete();
        DB::table('loans')->delete();
        DB::table('contributions')->delete();
        DB::table('books')->delete();
        DB::table('users')->delete();
        DB::table('tenants')->delete();

        try {
            DB::table('sqlite_sequence')->whereIn('name', ['loan_payments', 'payments', 'loans', 'contributions', 'books', 'users', 'tenants'])->delete();
        } catch (\Exception $e) {
            // Ignore if not sqlite or table doesn't exist
        }

        Schema::enableForeignKeyConstraints();

        // Create Tenant
        $tenant = \App\Models\Tenant::create([
            'name' => 'Main Co-op',
            'slug' => 'main',
            'plan' => 'free',
            'status' => 'active',
        ]);
        \App\Models\Tenant::setTenantId($tenant->id);

        // Create Super Admin
        User::create([
            'name' => 'Super Administrator',
            'email' => 'super@susu.com',
            'phone' => '0557777777',
            'role' => 'super_admin',
            'password' => Hash::make('password'),
            'member_id' => '999999',
            'status' => 'active',
            'tenant_id' => $tenant->id,
        ]);

        // 1. Create Admins
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@susu.com',
            'phone' => '0551234567',
            'role' => 'admin',
            'password' => Hash::make('password'),
            'member_id' => '100001',
            'status' => 'active',
            'tenant_id' => $tenant->id,
        ]);

        // 2. Create Clients
        $client1 = User::create([
            'name' => 'Alex Mercer',
            'email' => 'alex@mercer.com',
            'phone' => '0241112222',
            'phoneOne' => '0243334444',
            'role' => 'user',
            'password' => Hash::make('password'),
            'member_id' => '200001',
            'status' => 'active',
            'country' => 'Ghana',
            'city' => 'Accra',
            'state' => 'Greater Accra',
            'zip' => '00233',
        ]);

        $client2 = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@doe.com',
            'phone' => '0205556666',
            'role' => 'user',
            'password' => Hash::make('password'),
            'member_id' => '200002',
            'status' => 'active',
            'country' => 'Ghana',
            'city' => 'Kumasi',
            'state' => 'Ashanti',
            'zip' => '00234',
        ]);

        // 3. Create Savings Books
        $book1 = Book::create([
            'user_id' => $client1->id,
            'book_number' => 'BK-7701',
            'contribution_amount' => 100.00,
            'duration_weeks' => 55,
            'start_date' => '2026-01-05',
            'end_date' => '2027-01-25',
            'status' => 'active',
        ]);

        $book2 = Book::create([
            'user_id' => $client2->id,
            'book_number' => 'BK-8802',
            'contribution_amount' => 150.00,
            'duration_weeks' => 55,
            'start_date' => '2026-02-02',
            'end_date' => '2027-02-22',
            'status' => 'active',
        ]);

        // 4. Seed contributions for Client 1 on Book BK-7701 (Weeks 1 to 12)
        $baseDate = \Carbon\Carbon::parse($book1->start_date);
        for ($wk = 1; $wk <= 12; $wk++) {
            $payDate = $baseDate->copy()->addWeeks($wk - 1);
            $isMissed = ($wk == 10); // Week 10 is missed (creates a penalty)
            
            Contribution::create([
                'user_id' => $client1->id,
                'book_id' => $book1->id,
                'week_number' => $wk,
                'contribution' => 100.00,
                'welfare' => 10.00,
                'penalty' => $isMissed ? 10.00 : 0.00,
                'is_missed' => $isMissed,
                'created_at' => $payDate,
                'updated_at' => $payDate,
            ]);

            $txnId = 'TXN-SEEDED-' . (1000 + $wk);

            if (!$isMissed) {
                Payment::create([
                    'user_id' => $client1->id,
                    'book_id' => $book1->id,
                    'payment_type' => 'contribution',
                    'transaction_id' => $txnId . '-C',
                    'payment_method' => 'mobile_money',
                    'amount_paid' => 100.00,
                    'status' => 'completed',
                    'paid_at' => $payDate,
                ]);

                Payment::create([
                    'user_id' => $client1->id,
                    'book_id' => $book1->id,
                    'payment_type' => 'welfare',
                    'transaction_id' => $txnId . '-W',
                    'payment_method' => 'mobile_money',
                    'amount_paid' => 10.00,
                    'status' => 'completed',
                    'paid_at' => $payDate,
                ]);
            } else {
                // If missed, client paid penalty later
                $penaltyPayDate = $payDate->copy()->addDays(2);
                Payment::create([
                    'user_id' => $client1->id,
                    'book_id' => $book1->id,
                    'payment_type' => 'penalty',
                    'transaction_id' => $txnId . '-P',
                    'payment_method' => 'mobile_money',
                    'amount_paid' => 10.00,
                    'status' => 'completed',
                    'paid_at' => $penaltyPayDate,
                ]);
            }
        }

        // 5. Seed contributions for Client 2 on Book BK-8802 (Weeks 1 to 6)
        $baseDate2 = \Carbon\Carbon::parse($book2->start_date);
        for ($wk = 1; $wk <= 6; $wk++) {
            $payDate = $baseDate2->copy()->addWeeks($wk - 1);
            
            Contribution::create([
                'user_id' => $client2->id,
                'book_id' => $book2->id,
                'week_number' => $wk,
                'contribution' => 150.00,
                'welfare' => 15.00,
                'penalty' => 0.00,
                'is_missed' => false,
                'created_at' => $payDate,
                'updated_at' => $payDate,
            ]);

            $txnId = 'TXN-SEEDED-' . (2000 + $wk);

            Payment::create([
                'user_id' => $client2->id,
                'book_id' => $book2->id,
                'payment_type' => 'contribution',
                'transaction_id' => $txnId . '-C',
                'payment_method' => 'card',
                'amount_paid' => 150.00,
                'status' => 'completed',
                'paid_at' => $payDate,
            ]);

            Payment::create([
                'user_id' => $client2->id,
                'book_id' => $book2->id,
                'payment_type' => 'welfare',
                'transaction_id' => $txnId . '-W',
                'payment_method' => 'card',
                'amount_paid' => 15.00,
                'status' => 'completed',
                'paid_at' => $payDate,
            ]);
        }

        // 6. Seed Loans for Client 1
        // Loan 1: Active, partially repaid
        $loan1 = Loan::create([
            'user_id' => $client1->id,
            'book_id' => $book1->id,
            'amount' => 800.00,
            'interest' => 80.00,
            'due_date' => '2026-08-15',
            'status' => 'active',
            'created_at' => '2026-03-01',
        ]);

        LoanPayment::create([
            'loan_id' => $loan1->id,
            'amount_paid' => 300.00,
            'created_at' => '2026-04-01',
        ]);

        Payment::create([
            'user_id' => $client1->id,
            'loan_id' => $loan1->id,
            'payment_type' => 'loan_repayment',
            'transaction_id' => 'TXN-REPAY-1',
            'payment_method' => 'mobile_money',
            'amount_paid' => 300.00,
            'status' => 'completed',
            'paid_at' => '2026-04-01',
        ]);

        LoanPayment::create([
            'loan_id' => $loan1->id,
            'amount_paid' => 250.00,
            'created_at' => '2026-05-01',
        ]);

        Payment::create([
            'user_id' => $client1->id,
            'loan_id' => $loan1->id,
            'payment_type' => 'loan_repayment',
            'transaction_id' => 'TXN-REPAY-2',
            'payment_method' => 'mobile_money',
            'amount_paid' => 250.00,
            'status' => 'completed',
            'paid_at' => '2026-05-01',
        ]);

        // Loan 2: Pending Approval
        Loan::create([
            'user_id' => $client1->id,
            'book_id' => $book1->id,
            'amount' => 400.00,
            'interest' => 40.00,
            'due_date' => '2026-09-10',
            'status' => 'pending',
            'created_at' => '2026-06-20',
        ]);
    }
}
