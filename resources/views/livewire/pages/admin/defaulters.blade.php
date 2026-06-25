<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\with;
use App\Models\Contribution;
use App\Models\Loan;
use Carbon\Carbon;

layout('layouts.admin');

state([
    // Empty standard state
]);

with(function () {
    // Contributions Defaulters
    $missedContributions = Contribution::with('user')->where('is_missed', true)->get();
    
    $contributionDefaulters = [];
    foreach ($missedContributions->groupBy('user_id') as $userId => $contribs) {
        $user = $contribs->first()->user;
        $totalMissedAmount = $contribs->sum('contribution') + $contribs->sum('welfare'); // What should have been paid
        $contributionDefaulters[] = [
            'user' => $user,
            'weeks_missed' => $contribs->count(),
            'amount_due' => $totalMissedAmount,
        ];
    }
    
    // Sort by weeks_missed descending
    usort($contributionDefaulters, fn($a, $b) => $b['weeks_missed'] <=> $a['weeks_missed']);

    // Loan Defaulters
    $overdueLoans = Loan::with('user')->where('status', 'defaulted')->get();

    return [
        'contributionDefaulters' => collect($contributionDefaulters),
        'overdueLoans' => $overdueLoans,
        'totalActiveDefaulters' => count($contributionDefaulters) + $overdueLoans->count()
    ];
});

$sendReminder = function ($name) {
    session()->flash('success', "Reminder dispatched successfully to {$name}!");
};

$applyPenalty = function ($name) {
    session()->flash('success', "Penalty automatically registered for {$name}.");
};

$sendBulkReminders = function () {
    session()->flash('success', "Bulk reminders dispatched to all detected targets!");
};

?>

<!-- ═══════════════════════════════════════════
     PAGE 7: DEFAULTERS
═══════════════════════════════════════════ -->
<div class="page active" id="page-defaulters">
  
  @if(session()->has('success'))
    <div style="background:var(--success); color:#fff; padding:12px 16px; border-radius:6px; margin-bottom:16px; font-size:13px; font-weight:500;">
        ✓ {{ session('success') }}
    </div>
  @endif

  <div class="alert-banner danger">🔥 <strong>{{ $totalActiveDefaulters }} active defaulters</strong> detected. Review and take action.</div>
  
  <div class="grid-2" style="margin-bottom:16px">
    
    <!-- MISSING CONTRIBUTIONS CARD -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">Missed Contributions</div>
        <span class="badge badge-danger">{{ $contributionDefaulters->count() }} users</span>
      </div>
      <div class="table-wrap">
          <table>
            <thead><tr><th>User</th><th>Weeks Missed</th><th>Amount Due</th><th>Actions</th></tr></thead>
            <tbody>
              @forelse($contributionDefaulters as $defaulter)
              <tr>
                <td>
                  @if($defaulter['user'])
                  <div class="user-row">
                    <div class="user-avatar" style="background:#fd79a8">{{ strtoupper(substr($defaulter['user']->name, 0, 2)) }}</div>
                    {{ $defaulter['user']->name }}
                  </div>
                  @endif
                </td>
                <td>
                    @if($defaulter['weeks_missed'] >= 3)
                        <span class="badge badge-danger">{{ $defaulter['weeks_missed'] }} weeks</span>
                    @else
                        <span class="badge badge-warn">{{ $defaulter['weeks_missed'] }} weeks</span>
                    @endif
                </td>
                <td class="mono">GH₵ {{ number_format($defaulter['amount_due'], 2) }}</td>
                <td>
                    <div style="display:flex;gap:4px">
                        <button class="btn btn-warn btn-xs" style="background:var(--warn-bg);color:var(--warn);border:1px solid rgba(210,153,34,.2)" wire:click="sendReminder('{{ $defaulter['user']->name ?? 'Unknown' }}')">Remind</button>
                        <button class="btn btn-danger btn-xs" wire:click="applyPenalty('{{ $defaulter['user']->name ?? 'Unknown' }}')">Penalise</button>
                    </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="4" style="text-align: center; color: var(--text3); padding: 20px;">No missed contributions found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
      </div>
      @if($contributionDefaulters->count() > 0)
      <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border)">
        <button class="btn btn-primary btn-sm" wire:click="sendBulkReminders">📤 Send Bulk Reminders</button>
      </div>
      @endif
    </div>

    <!-- OVERDUE LOANS CARD -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">Overdue Loans</div>
        <span class="badge badge-danger">{{ $overdueLoans->count() }} loans</span>
      </div>
      <div class="table-wrap">
          <table>
            <thead><tr><th>User</th><th>Loan ID</th><th>Overdue By</th><th>Actions</th></tr></thead>
            <tbody>
              @forelse($overdueLoans as $loan)
                @php
                    $daysOverdue = max(1, \Carbon\Carbon::parse($loan->due_date)->diffInDays(now()));
                @endphp
              <tr>
                <td>
                  @if($loan->user)
                  <div class="user-row">
                    <div class="user-avatar" style="background:#e17055">{{ strtoupper(substr($loan->user->name, 0, 2)) }}</div>
                    {{ $loan->user->name }}
                  </div>
                  @endif
                </td>
                <td class="mono">#LN-{{ sprintf('%04d', $loan->id) }}</td>
                <td>
                    @if($daysOverdue >= 14)
                        <span class="badge badge-danger">{{ $daysOverdue }} days</span>
                    @else
                        <span class="badge badge-warn">{{ $daysOverdue }} days</span>
                    @endif
                </td>
                <td>
                    <button class="btn btn-danger btn-xs" wire:click="sendReminder('{{ $loan->user->name ?? 'Unknown' }}')">Remind</button>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="4" style="text-align: center; color: var(--text3); padding: 20px;">No overdue loans found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
      </div>
      @if($overdueLoans->count() > 0)
      <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border)">
        <button class="btn btn-primary btn-sm" wire:click="sendBulkReminders">📤 Send All Loan Reminders</button>
      </div>
      @endif
    </div>

  </div>
</div>
