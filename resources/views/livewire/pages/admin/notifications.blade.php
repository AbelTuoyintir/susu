<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\with;
use App\Models\Announcement;

layout('layouts.admin');

state([
    'recipientGroup' => 'All Users',
    'type' => 'general',
    'title' => '',
    'message' => '',
]);

$setTemplate = function ($val) {
    $templates = [
        'weekly' => [
            'title' => 'Weekly Contribution Reminder',
            'message' => 'Dear member, this is a reminder to make your weekly contribution of GH₵ 120. Kindly pay before Sunday. Thank you.'
        ],
        'overdue' => [
            'title' => 'Overdue Loan Warning',
            'message' => 'Dear member, your loan repayment is overdue. Please make payment immediately to avoid further penalties.'
        ],
        'penalty' => [
            'title' => 'Penalty Applied Notice',
            'message' => 'Dear member, a penalty has been applied to your account for a missed contribution. Please contact admin.'
        ],
        'payout' => [
            'title' => 'Year-End Payout Info',
            'message' => 'Dear member, the year-end profit sharing has been calculated. Contact admin for your payout details.'
        ],
    ];

    if (isset($templates[$val])) {
        $this->title = $templates[$val]['title'];
        $this->message = $templates[$val]['message'];
    }
};

$sendAnnouncement = function () {
    $this->validate([
        'title' => 'required|min:3',
        'message' => 'required|min:5',
        'type' => 'required',
        'recipientGroup' => 'required',
    ]);

    $announcement = Announcement::create([
        'title' => $this->title,
        'content' => $this->message,
        'type' => $this->type,
        'target_group' => $this->recipientGroup,
        'user_id' => auth()->id(),
    ]);

    \App\Jobs\SendAnnouncementJob::dispatch($announcement);
    
    session()->flash('success', "Announcement dispatched successfully!");
    
    // Clear draft
    $this->title = '';
    $this->message = '';
};

with(function () {
    return [
        'recentAnnouncements' => Announcement::with('user')->latest()->take(5)->get(),
        'adminNotifications' => auth()->user()->notifications()->latest()->take(10)->get(),
    ];
});

$markAsRead = function ($id) {
    $notification = auth()->user()->notifications()->find($id);
    if ($notification) {
        $notification->markAsRead();
    }
};

?>

<div class="page active" id="page-notifications">
  
  @if(session()->has('success'))
    <div style="background:var(--success); color:#fff; padding:12px 16px; border-radius:6px; margin-bottom:16px; font-size:13px; font-weight:500;">
        ✓ {{ session('success') }}
    </div>
  @endif

  <div class="grid-2">
    <!-- SEND MODULE -->
    <div class="card">
      <div class="card-header"><div class="card-title">Send Notification / Announcement</div></div>
      <div style="display:flex;flex-direction:column;gap:12px">
        <div>
          <div style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:5px">Recipients</div>
          <select wire:model="recipientGroup" class="filter-input" style="width:100%">
            <option value="All Users">All Users</option>
            <option value="Defaulters Only">Defaulters Only</option>
            <option value="Loan Overdue">Loan Overdue</option>
            <option value="Active Users">Active Users</option>
          </select>
        </div>
        <div>
          <div style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:5px">Type</div>
          <select wire:model="type" class="filter-input" style="width:100%">
            <option value="general">General</option>
            <option value="alert">Alert (Red)</option>
            <option value="success">Success (Green)</option>
            <option value="info">Info (Blue)</option>
          </select>
        </div>
        <div>
          <div style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:5px">Message Template</div>
          <select wire:change="setTemplate($event.target.value)" class="filter-input" style="width:100%;margin-bottom:8px">
            <option value="">Custom message…</option>
            <option value="weekly">Weekly Contribution Reminder</option>
            <option value="overdue">Overdue Loan Warning</option>
            <option value="penalty">Penalty Applied Notice</option>
            <option value="payout">Year-End Payout Info</option>
          </select>

          <input type="text" wire:model="title" class="filter-input" style="width:100%; margin-bottom:8px;" placeholder="Announcement Title">
          @error('title') <span style="color:var(--danger);font-size:12px;">{{ $message }}</span> @enderror

          <textarea wire:model="message" class="filter-input" rows="4" style="width:100%;resize:vertical" placeholder="Type your message here…"></textarea>
          @error('message') <span style="color:var(--danger);font-size:12px;">{{ $message }}</span> @enderror
        </div>
        <div style="display:flex;gap:8px">
          <button class="btn btn-primary" wire:click="sendAnnouncement">
              <span wire:loading.remove wire:target="sendAnnouncement">📤 Send & Post</span>
              <span wire:loading wire:target="sendAnnouncement">Dispatching...</span>
          </button>
        </div>
      </div>
    </div>

    <!-- LOG MODULE -->
    <div style="display:flex; flex-direction:column; gap:16px;">
      <div class="card">
        <div class="card-header"><div class="card-title">Recent Announcements</div></div>
        <div>
          @forelse($recentAnnouncements as $ann)
        <div class="notif-item">
          <div class="notif-icon-wrap" style="background:{{ $ann->type === 'alert' ? 'var(--danger-bg)' : ($ann->type === 'success' ? 'var(--success-bg)' : 'var(--info-bg)') }}">
            {{ $ann->type === 'alert' ? '⚠️' : ($ann->type === 'success' ? '✅' : '📣') }}
          </div>
          <div class="notif-content">
            <div class="notif-title">{{ $ann->title }} — {{ $ann->target_group }}</div>
            <div class="notif-msg">{{ Str::limit($ann->content, 100) }}</div>
            <div class="notif-time">{{ $ann->created_at->diffForHumans() }} • Sent by {{ $ann->user->name ?? 'Admin' }}</div>
          </div>
        </div>
          @empty
          <div style="text-align:center; padding: 20px; color:var(--text3);">No recent announcements.</div>
          @endforelse
        </div>
      </div>

      <div class="card">
        <div class="card-header"><div class="card-title">System Notifications</div></div>
        <div>
          @forelse($adminNotifications as $notif)
          <div class="notif-item" style="opacity: {{ $notif->read_at ? '0.6' : '1' }}">
            <div class="notif-icon-wrap" style="background:var(--accent-dim); color:var(--accent)">
              🔔
            </div>
            <div class="notif-content">
              <div class="notif-title">{{ $notif->data['title'] ?? 'Notification' }}</div>
              <div class="notif-msg">{{ $notif->data['message'] ?? '' }}</div>
              <div class="notif-time">{{ $notif->created_at->diffForHumans() }}</div>
              @if(!$notif->read_at)
                <button wire:click="markAsRead('{{ $notif->id }}')" class="btn btn-xs btn-outline" style="margin-top:5px;">Mark as Read</button>
              @endif
            </div>
          </div>
          @empty
          <div style="text-align:center; padding: 20px; color:var(--text3);">No system notifications.</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</div>
