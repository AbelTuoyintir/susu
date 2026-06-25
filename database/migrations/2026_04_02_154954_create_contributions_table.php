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
        Schema::create('contributions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();

            $table->integer('week_number');

            $table->decimal('contribution', 10, 2);
            $table->decimal('welfare', 10, 2);
            $table->decimal('penalty', 10, 2)->default(0);

            $table->boolean('is_missed')->default(false);

            $table->timestamps();

            $table->unique(['book_id', 'week_number']); // prevent duplicates
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contributions');
    }
};
