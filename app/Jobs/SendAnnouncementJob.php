<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Contribution;
use App\Models\Loan;
use App\Notifications\SystemNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendAnnouncementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $announcement;

    /**
     * Create a new job instance.
     */
    public function __construct($announcement)
    {
        $this->announcement = $announcement;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $targetGroup = $this->announcement->target_group;
        $users = collect();

        switch ($targetGroup) {
            case 'All Users':
                $users = User::all();
                break;
            case 'Defaulters Only':
                $userIds = Contribution::where('is_missed', true)->pluck('user_id')->unique();
                $users = User::whereIn('id', $userIds)->get();
                break;
            case 'Loan Overdue':
                $userIds = Loan::where('status', 'defaulted')->pluck('user_id')->unique();
                $users = User::whereIn('id', $userIds)->get();
                break;
            case 'Active Users':
                $users = User::where('status', 'active')->get();
                break;
        }

        if ($users->isNotEmpty()) {
            Notification::send($users, new SystemNotification(
                $this->announcement->title,
                $this->announcement->content,
                $this->announcement->type
            ));
        }
    }
}
