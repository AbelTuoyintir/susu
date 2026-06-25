<?php

use App\Console\Commands\CheckLoanDefaults;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ⚠️ Default checker: run from cron using `php artisan loans:check-defaults`
// (This project may not have the standard Console Kernel; keep this file minimal.)


// Register the command (for apps that don't have a Console Kernel yet)
Artisan::command('loans:check-defaults', function () {
    // Run the real command class so options/signature work correctly
    $this->call(\App\Console\Commands\CheckLoanDefaults::class);
})->describe('Mark pending loans as defaulted when due_date has passed');

Artisan::command('remind:loans', function () {
    $this->call(\App\Console\Commands\RemindLoanPayments::class);
})->describe('Send reminders to members with upcoming loan payments');

Artisan::command('remind:sharing', function () {
    $this->call(\App\Console\Commands\RemindContributionSharing::class);
})->describe('Remind admin to share contributions at the end of the week');

