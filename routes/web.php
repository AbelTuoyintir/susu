<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'welcome');

// Admin Routes (restricted to admin & super_admin)
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Volt::route('dashboard', 'pages.admin.dashboard')->name('dashboard');
    Volt::route('users', 'pages.admin.users')->name('users');
    
    Route::get('users/add', function () {
        return view('admin.add-user');
    })->name('users.add');

    Route::post('users/add', [App\Http\Controllers\UserController::class, 'addUser'])->name('users.store');

    Volt::route('books', 'pages.admin.books')->name('books');
    Volt::route('books/add', 'pages.admin.add-book')->name('books.add');

    Volt::route('contributions', 'pages.admin.contributions')->name('contributions');
    Volt::route('contributions/add', 'pages.admin.add-contribution')->name('contributions.add');

    Volt::route('loans', 'pages.admin.loans')->name('loans');
    Volt::route('loans/add', 'pages.admin.add-loan')->name('loans.add');

    Volt::route('payments', 'pages.admin.payments')->name('payments');
    Volt::route('defaulters', 'pages.admin.defaulters')->name('defaulters');
    Volt::route('reports', 'pages.admin.reports')->name('reports');
    Volt::route('notifications', 'pages.admin.notifications')->name('notifications');
    Volt::route('settings', 'pages.admin.settings')->name('settings');
});

// Client Routes (accessible by regular logged-in users)
Route::middleware(['auth', 'verified'])->prefix('client')->name('client.')->group(function () {
    Volt::route('dashboard', 'pages.clients.dashboard')->name('dashboard');
    Volt::route('books', 'pages.clients.books')->name('books');
    Volt::route('contributions', 'pages.clients.contributions')->name('contributions');
    Volt::route('loans', 'pages.clients.loans')->name('loans');
    Volt::route('payments', 'pages.clients.payments')->name('payments');
    Volt::route('settings', 'pages.clients.settings')->name('settings');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
