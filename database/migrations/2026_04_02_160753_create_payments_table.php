<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loan_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->nullable()->constrained()->cascadeOnDelete();

            $table->enum('payment_type', [
                'loan_repayment',
                'contribution',
                'penalty',
                'welfare'
            ]);

            $table->string('transaction_id')->unique()->nullable();

            $table->enum('payment_method', [
                'cash',
                'mobile_money',
                'card'
            ]);

            $table->decimal('amount_paid', 10, 2);

            $table->enum('status', [
                'pending',
                'completed',
                'failed'
            ])->default('completed');

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
