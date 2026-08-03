<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Contribution;
use App\Models\Loan;
use App\Models\Tenant;
use App\Notifications\SystemNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SendAnnouncementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $title;
    protected $message;
    protected $type;
    protected $recipientGroup;
    protected $tenantId;

    /**
     * Create a new job instance.
     */
    public function __construct($title, $message, $type, $recipientGroup, $tenantId = null)
    {
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
        $this->recipientGroup = $recipientGroup;
        $this->tenantId = $tenantId ?? (auth()->check() ? auth()->user()->tenant_id : Tenant::currentId());
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $callback = function () {
            $users = collect();
            if ($this->recipientGroup === 'All Users') {
                $users = User::all();
            } elseif ($this->recipientGroup === 'Defaulters Only') {
                $defaulterIds = Contribution::where('is_missed', true)->pluck('user_id')->unique();
                $users = User::whereIn('id', $defaulterIds)->get();
            } elseif ($this->recipientGroup === 'Loan Overdue') {
                $overdueUserIds = Loan::where('status', 'defaulted')->pluck('user_id')->unique();
                $users = User::whereIn('id', $overdueUserIds)->get();
            } elseif ($this->recipientGroup === 'Active Users') {
                $users = User::whereHas('books', function($q) {
                    $q->where('status', 'active');
                })->get();
            }

            foreach ($users as $user) {
                $user->notify(new SystemNotification([
                    'title' => $this->title,
                    'message' => $this->message,
                    'type' => $this->type,
                ]));
            }
        };

        if ($this->tenantId) {
            Tenant::forTenant($this->tenantId, $callback);
        } else {
            $callback();
        }
    }
}
