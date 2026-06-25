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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('book_number')->unique();
            $table->decimal('contribution_amount', 10, 2);

            $table->integer('duration_weeks')->default(55);
            $table->date('start_date');
            $table->date('end_date')->nullable();

            $table->enum('status', ['active', 'completed', 'deactivated', 'unassigned'])->default('active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
