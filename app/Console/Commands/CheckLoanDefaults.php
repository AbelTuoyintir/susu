<?php

namespace App\Console\Commands;

use App\Models\Loan;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CheckLoanDefaults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loans:check-defaults {--chunk=1000 : Number of loans to process per chunk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark pending loans as defaulted when due_date has passed';

    public function handle(): int
    {
        $tenants = Tenant::all();
        $updated = 0;

        if ($tenants->isEmpty()) {
            $updated += $this->checkDefaultsForCurrentTenant();
        } else {
            foreach ($tenants as $tenant) {
                $updated += Tenant::forTenant($tenant->id, function () use ($tenant) {
                    $this->info("Checking defaults for tenant: {$tenant->name}");
                    return $this->checkDefaultsForCurrentTenant();
                });
            }
        }

        $this->info("Defaults updated: {$updated}");

        return self::SUCCESS;
    }

    protected function checkDefaultsForCurrentTenant(): int
    {
        $chunkSize = (int) $this->option('chunk');
        $now = Carbon::now();
        $updated = 0;

        DB::transaction(function () use ($chunkSize, $now, &$updated) {
            Loan::query()
                ->where('status', 'pending')
                ->where('due_date', '<', $now)
                ->chunkById($chunkSize, function ($loans) use ($now, &$updated) {
                    foreach ($loans as $loan) {
                        $loan->amount += $loan->interest;
                        $loan->status = 'defaulted';
                        $loan->save();
                        $updated++;
                    }
                });
        });

        return $updated;
    }
}
