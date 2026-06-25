<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\with;
use App\Models\Contribution;
use App\Models\User;
use App\Models\Book;

layout('layouts.admin');

state([
    'weekFilter' => 'All Weeks',
    'userFilter' => 'All Users',
    'bookFilter' => 'All Books',
]);

with(function () {
    $query = Contribution::with(['user', 'book']);

    if ($this->weekFilter !== 'All Weeks') {
        $weekInt = (int) str_replace('Week ', '', $this->weekFilter);
        $query->where('week_number', $weekInt);
    }

    if ($this->userFilter !== 'All Users') {
        $query->whereHas('user', function($q) {
            $q->where('name', $this->userFilter); 
        });
    }

    if ($this->bookFilter !== 'All Books') {
        $bookNo = str_replace('Book #', '', $this->bookFilter);
        $query->whereHas('book', function($q) use ($bookNo) {
            $q->where('book_number', $bookNo);
        });
    }

    $contributions = $query->latest()->get();

    return [
        'contributions' => $contributions,
        'thisWeekTotal' => $contributions->sum('contribution'),
        'totalWelfare' => Contribution::sum('welfare'),
        'totalPenalties' => Contribution::sum('penalty'),
        'defaultersCount' => Contribution::where('is_missed', true)->distinct('user_id')->count('user_id'),
        
        // Dropdown data options
        'weeksOptions' => Contribution::select('week_number')->distinct()->orderByDesc('week_number')->pluck('week_number'),
        'usersOptions' => User::has('contributions')->orderBy('name')->get(),
        'booksOptions' => Book::has('contributions')->orderBy('book_number')->get(),
        
        // Alert threshold - recent missing
        'recentMissed' => Contribution::where('is_missed', true)->where('created_at', '>=', now()->subDays(7))->distinct('user_id')->count('user_id'),
    ];
});

?>

<!-- ═══════════════════════════════════════════
     PAGE 4: CONTRIBUTIONS
═══════════════════════════════════════════ -->
<div class="page active" id="page-contributions">
  
  @if($recentMissed > 0)
  <div class="alert-banner warn">⚠️ <strong>{{ $recentMissed }} users</strong> have missed contributions this week. <a href="/defaulters" wire:navigate style="cursor:pointer;text-decoration:underline;margin-left:4px">View Defaulters →</a></div>
  @endif

  <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:16px">
    <div class="stat-card"><div class="stat-label">Results Total</div><div class="stat-value" style="color:var(--success)">GH₵ {{ number_format($thisWeekTotal, 2) }}</div></div>
    <div class="stat-card"><div class="stat-label">Total Welfare</div><div class="stat-value" style="color:var(--accent)">GH₵ {{ number_format($totalWelfare, 2) }}</div></div>
    <div class="stat-card"><div class="stat-label">Penalties</div><div class="stat-value" style="color:var(--danger)">GH₵ {{ number_format($totalPenalties, 2) }}</div></div>
    <div class="stat-card"><div class="stat-label">Defaulters</div><div class="stat-value" style="color:var(--warn)">{{ $defaultersCount }}</div></div>
  </div>
  <div class="filters">
    <select wire:model.live="weekFilter" class="filter-input">
        <option>All Weeks</option>
        @foreach($weeksOptions as $week)
            <option>Week {{ $week }}</option>
        @endforeach
    </select>
    <select wire:model.live="userFilter" class="filter-input">
        <option>All Users</option>
        @foreach($usersOptions as $user)
            <option>{{ $user->name }}</option>
        @endforeach
    </select>
    <select wire:model.live="bookFilter" class="filter-input">
        <option>All Books</option>
        @foreach($booksOptions as $book)
            <option>Book #{{ $book->book_number }}</option>
        @endforeach
    </select>
    <a href="{{ route('contributions.add') }}" class="btn btn-primary btn-sm" wire:navigate>+ Record</a>
  </div>
  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr><th>Week</th><th>User</th><th>Book</th><th>Amount</th><th>Welfare</th><th>Date</th><th>Status</th></tr></thead>
        <tbody>
          @forelse($contributions as $contrib)
          <tr>
            <td><span class="badge badge-neutral">Wk {{ $contrib->week_number }}</span></td>
            <td>
              @if($contrib->user)
              <div class="user-row">
                <div class="user-avatar" style="background:#00b894">{{ strtoupper(substr($contrib->user->name, 0, 2)) }}</div>
                {{ $contrib->user->name }}
              </div>
              @endif
            </td>
            <td class="mono">
              @if($contrib->book)
                  #{{ $contrib->book->book_number }}
              @endif
            </td>
            <td class="mono">GH₵ {{ number_format($contrib->contribution, 2) }}</td>
            <td class="mono">GH₵ {{ number_format($contrib->welfare, 2) }}</td>
            <td style="color:var(--text3)">
                @if($contrib->is_missed)
                   —
                @else
                   {{ $contrib->created_at->format('M j, Y') }}
                @endif
            </td>
            <td>
                @if($contrib->is_missed)
                    <span class="badge badge-danger">Missed</span>
                @else
                    <span class="badge badge-success">Paid</span>
                @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" style="text-align: center; color: var(--text3); padding: 20px;">No contributions found for selected criteria.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
