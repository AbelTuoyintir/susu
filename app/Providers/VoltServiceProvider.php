<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Volt\Volt;

class VoltServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Mount Volt component views (required so routes like pages.clients.settings resolve).
        // Keep this to a single mount to avoid duplicate Volt anonymous component compilation.
        Volt::mount([
            resource_path('views/livewire'),
        ]);
    }
}
