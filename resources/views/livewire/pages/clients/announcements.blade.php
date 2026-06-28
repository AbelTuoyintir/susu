<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\with;
use App\Models\Announcement;

layout('layouts.client');

with(function () {
    $user = auth()->user();

    // Determine if the user is a defaulter
    $isDefaulter = \App\Models\Contribution::where('user_id', $user->id)->where('is_missed', true)->exists();

    // Determine if the user has an overdue loan
    $hasOverdueLoan = \App\Models\Loan::where('user_id', $user->id)->where('status', 'defaulted')->exists();

    $targetGroups = ['All Users'];
    if ($isDefaulter) $targetGroups[] = 'Defaulters Only';
    if ($hasOverdueLoan) $targetGroups[] = 'Loan Overdue';
    $targetGroups[] = 'Active Users'; // Assuming all logged in users are active

    return [
        'announcements' => Announcement::whereIn('target_group', $targetGroups)->latest()->get(),
        'notifications' => $user->notifications()->latest()->get(),
    ];
});

$markAsRead = function ($id) {
    $notification = auth()->user()->notifications()->find($id);
    if ($notification) {
        $notification->markAsRead();
    }
};

?>

<div class="page active" id="page-client-announcements">
  <div class="grid-2">
    <!-- Announcements Column -->
    <div>
      <div class="card-header" style="margin-bottom: 16px;">
        <div class="card-title">Announcements</div>
        <div class="card-sub">Latest news from management</div>
      </div>

      <div style="display:flex; flex-direction:column; gap:16px;">
        @forelse($announcements as $ann)
      <div class="card" style="border-left: 4px solid {{ $ann->type === 'alert' ? 'var(--danger)' : ($ann->type === 'success' ? 'var(--success)' : 'var(--info)') }}">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
          <div style="font-weight:600; font-size:var(--fs-md); color:var(--text);">{{ $ann->title }}</div>
          <div style="font-size:10px; color:var(--text3);">{{ $ann->created_at->format('M j, Y H:i') }}</div>
        </div>
        <div style="color:var(--text2); line-height:1.6; font-size:var(--fs);">
          {{ $ann->content }}
        </div>
        <div style="margin-top:12px; display:flex; gap:8px;">
           <span class="badge {{ $ann->type === 'alert' ? 'badge-danger' : ($ann->type === 'success' ? 'badge-success' : 'badge-info') }}">
             {{ ucfirst($ann->type) }}
           </span>
        </div>
      </div>
        @empty
          <div class="card" style="text-align:center; padding:40px; color:var(--text3);">
            <div style="font-size:24px; margin-bottom:10px;">📣</div>
            <div>No announcements yet.</div>
          </div>
        @endforelse
      </div>
    </div>

    <!-- Personal Notifications Column -->
    <div>
      <div class="card-header" style="margin-bottom: 16px;">
        <div class="card-title">Personal Notifications</div>
        <div class="card-sub">Alerts and reminders for you</div>
      </div>

      <div style="display:flex; flex-direction:column; gap:12px;">
        @forelse($notifications as $notif)
          <div class="card" style="border-left: 4px solid {{ $notif->read_at ? 'var(--border2)' : 'var(--accent)' }}; padding: 12px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:4px;">
              <div style="font-weight:600; font-size:var(--fs-sm); color:{{ $notif->read_at ? 'var(--text2)' : 'var(--text)' }};">
                {{ $notif->data['title'] ?? 'Notification' }}
              </div>
              <div style="font-size:9px; color:var(--text3);">{{ $notif->created_at->diffForHumans() }}</div>
            </div>
            <div style="color:var(--text2); font-size:11px; line-height:1.4;">
              {{ $notif->data['message'] ?? '' }}
            </div>
            @if(!$notif->read_at)
              <div style="margin-top:8px; text-align:right;">
                <button wire:click="markAsRead('{{ $notif->id }}')" class="btn btn-xs btn-outline">Mark as read</button>
              </div>
            @endif
          </div>
        @empty
          <div class="card" style="text-align:center; padding:40px; color:var(--text3);">
            <div style="font-size:24px; margin-bottom:10px;">🔔</div>
            <div>No personal notifications.</div>
          </div>
        @endforelse
      </div>
    </div>
  </div>
</div>
