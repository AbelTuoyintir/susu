<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\with;

layout('layouts.admin');

state([
    'recipientGroup' => 'All Users (248)',
    'channel' => ' SMS', // SMS, Email, Push
    'template' => '',
    'message' => '',
]);

$setTemplate = function ($val) {
    $this->template = $val;
    $templates = [
        'weekly' => 'Dear member, this is a reminder to make your weekly contribution of GH₵ 120. Kindly pay before Sunday. Thank you.',
        'overdue' => 'Dear member, your loan repayment is overdue. Please make payment immediately to avoid further penalties.',
        'penalty' => 'Dear member, a penalty has been applied to your account for a missed contribution. Please contact admin.',
        'payout' => 'Dear member, the year-end profit sharing has been calculated. Contact admin for your payout details.',
    ];
    $this->message = $templates[$val] ?? '';
};

$sendBulkSMS = function () {
    $this->validate(['message' => 'required|min:5']);
    
    // In production, this dispatches via Twilio or Resend API
    session()->flash('success', "Notification dispatched successfully via {$this->channel} to {$this->recipientGroup}!");
    
    // Clear draft
    $this->message = '';
    $this->template = '';
};

$scheduleMsg = function () {
    $this->validate(['message' => 'required|min:5']);
    session()->flash('success', "Message scheduled to be broadcasted at a later time via {$this->channel}.");
};

?>

<!-- ═══════════════════════════════════════════
     PAGE 9: NOTIFICATIONS
═══════════════════════════════════════════ -->
<div class="page active" id="page-notifications">
  
  @if(session()->has('success'))
    <div style="background:var(--success); color:#fff; padding:12px 16px; border-radius:6px; margin-bottom:16px; font-size:13px; font-weight:500;">
        ✓ {{ session('success') }}
    </div>
  @endif

  <div class="grid-2">
    <!-- SEND MODULE -->
    <div class="card">
      <div class="card-header"><div class="card-title">Send Notification</div></div>
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
          <div style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:5px">Channel</div>
          <div style="display:flex;gap:8px">
            <label style="display:flex;align-items:center;gap:5px;cursor:pointer;font-size:var(--fs-sm)">
                <input type="radio" wire:model="channel" value="SMS"> 📱 SMS
            </label>
            <label style="display:flex;align-items:center;gap:5px;cursor:pointer;font-size:var(--fs-sm)">
                <input type="radio" wire:model="channel" value="Email"> 📧 Email
            </label>
            <label style="display:flex;align-items:center;gap:5px;cursor:pointer;font-size:var(--fs-sm)">
                <input type="radio" wire:model="channel" value="Push"> 🔔 Push
            </label>
          </div>
        </div>
        <div>
          <div style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:5px">Message Template</div>
          <select wire:model.live="template" wire:change="setTemplate($event.target.value)" class="filter-input" style="width:100%;margin-bottom:8px">
            <option value="">Custom message…</option>
            <option value="weekly">Weekly Contribution Reminder</option>
            <option value="overdue">Overdue Loan Warning</option>
            <option value="penalty">Penalty Applied Notice</option>
            <option value="payout">Year-End Payout Info</option>
          </select>
          <textarea wire:model="message" class="filter-input" rows="4" style="width:100%;resize:vertical" placeholder="Type your message here…"></textarea>
          @error('message') <span style="color:var(--danger);font-size:12px;">{{ $message }}</span> @enderror
        </div>
        <div style="display:flex;gap:8px">
          <button class="btn btn-primary" wire:click="sendBulkSMS">
              <span wire:loading.remove wire:target="sendBulkSMS">📤 Send Now</span>
              <span wire:loading wire:target="sendBulkSMS">Dispatching...</span>
          </button>
          <button class="btn btn-outline" wire:click="scheduleMsg">🕐 Schedule</button>
        </div>
      </div>
    </div>

    <!-- LOG MODULE -->
    <div class="card">
      <div class="card-header"><div class="card-title">Recent Notifications</div></div>
      <div>
        <!-- Simulated Log Entries -->
        <div class="notif-item">
          <div class="notif-icon-wrap" style="background:var(--danger-bg)">⚠️</div>
          <div class="notif-content">
            <div class="notif-title">Contribution Reminder — Bulk SMS</div>
            <div class="notif-msg">Sent to 7 defaulters: "Dear member, you have missed your weekly contribution…"</div>
            <div class="notif-time">Today, 08:00 • 7 recipients • ✅ All delivered</div>
          </div>
        </div>
        <div class="notif-item">
          <div class="notif-icon-wrap" style="background:var(--warn-bg)">🏦</div>
          <div class="notif-content">
            <div class="notif-title">Loan Overdue Alert</div>
            <div class="notif-msg">Sent to Yaw Osei: "Your loan repayment is now 19 days overdue…"</div>
            <div class="notif-time">Yesterday, 09:00 • 1 recipient • ✅ Delivered</div>
          </div>
        </div>
        <div class="notif-item">
          <div class="notif-icon-wrap" style="background:var(--success-bg)">📣</div>
          <div class="notif-content">
            <div class="notif-title">Weekly Contribution Reminder</div>
            <div class="notif-msg">General reminder sent to all 248 members for Week 14.</div>
            <div class="notif-time">Mon Apr 1, 07:00 • 248 recipients • ✅ 241 delivered</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
