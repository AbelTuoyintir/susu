<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Volt\Volt;

class VoltServiceProvider extends ServiceProvider
{
    /** @var bool */
    private static bool $mounted = false;

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
        // Prevent Volt anonymous component compilation from being executed twice,
        // which triggers:
        // "Cannot redeclare Livewire\\Volt\\Component@anonymous::mount()".
        if (self::$mounted && !app()->runningUnitTests()) {
            return;
        }

        if (!app()->runningUnitTests()) {
            self::$mounted = true;
        }

        Volt::mount([
            resource_path('views/livewire'),
        ]);
    }

}
