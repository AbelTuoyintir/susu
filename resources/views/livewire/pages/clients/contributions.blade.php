<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\with;
use App\Models\Book;
use App\Models\Contribution;

layout('layouts.client');

state([
    'bookFilter' => 'All Books',
    'statusFilter' => 'All Status',
]);

with(function () {
    $userId = auth()->id();
    $query = Contribution::where('user_id', $userId)->with('book');

    if ($this->bookFilter !== 'All Books') {
        $bookId = (int)$this->bookFilter;
        $query->where('book_id', $bookId);
    }

    if ($this->statusFilter !== 'All Status') {
        $isMissed = ($this->statusFilter === 'Missed');
        $query->where('is_missed', $isMissed);
    }

    $contributions = $query->latest('week_number')->get();
    
    // Dropdown list of books owned by user
    $myBooks = Book::where('user_id', $userId)->orderBy('book_number')->get();

    // Summary Statistics for the current user
    $totalSaved = Contribution::where('user_id', $userId)->where('is_missed', false)->sum('contribution');
    $totalWelfare = Contribution::where('user_id', $userId)->where('is_missed', false)->sum('welfare');
    $totalPenalties = Contribution::where('user_id', $userId)->where('is_missed', true)->sum('penalty');

    return [
        'contributions' => $contributions,
        'myBooks' => $myBooks,
        'totalSaved' => $totalSaved,
        'totalWelfare' => $totalWelfare,
        'totalPenalties' => $totalPenalties,
    ];
});

?>

<div class="page active" id="page-client-contributions">
  <!-- Stats Cards -->
  <div class="stats-grid" style="grid-template-columns:repeat(3, 1fr); margin-bottom:16px;">
    <div class="stat-card">
      <div class="stat-label">Total Savings Contributed</div>
      <div class="stat-value" style="color:var(--success)">GH₵ {{ number_format($totalSaved, 2) }}</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Total Welfare Contributed</div>
      <div class="stat-value" style="color:var(--info)">GH₵ {{ number_format($totalWelfare, 2) }}</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Total Penalties Incurred</div>
      <div class="stat-value" style="color:var(--danger)">GH₵ {{ number_format($totalPenalties, 2) }}</div>
    </div>
  </div>

  <!-- Filters -->
  <div class="filters">
    <select wire:model.live="bookFilter" class="filter-input">
        <option>All Books</option>
        @foreach($myBooks as $book)
            <option value="{{ $book->id }}">Book #{{ $book->book_number }}</option>
        @endforeach
    </select>
    
    <select wire:model.live="statusFilter" class="filter-input">
        <option>All Status</option>
        <option>Paid</option>
        <option>Missed</option>
    </select>
    
    <a href="/client/payments?type=contribution" class="btn btn-primary btn-sm" wire:navigate style="margin-left: auto;">+ Pay Contribution</a>
  </div>

  <!-- Contributions Statement Card -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">Savings Statement & History</div>
      <div class="card-sub">Showing audited contributions records</div>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Book Number</th>
            <th>Week Number</th>
            <th>Savings Amount</th>
            <th>Welfare contribution</th>
            <th>Penalty paid</th>
            <th>Date Registered</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($contributions as $contrib)
          <tr>
            <td class="mono" style="color:var(--accent)">
              #{{ $contrib->book->book_number ?? '—' }}
            </td>
            <td class="mono">Week {{ $contrib->week_number }}</td>
            <td class="mono">GH₵ {{ number_format($contrib->contribution, 2) }}</td>
            <td class="mono">GH₵ {{ number_format($contrib->welfare, 2) }}</td>
            <td class="mono" style="{{ $contrib->penalty > 0 ? 'color:var(--danger)' : '' }}">
              GH₵ {{ number_format($contrib->penalty, 2) }}
            </td>
            <td style="color:var(--text3)">
              {{ \Carbon\Carbon::parse($contrib->created_at)->format('M j, Y') }}
            </td>
            <td>
              @if(!$contrib->is_missed)
                <span class="badge badge-success"><span class="dot"></span> Paid</span>
              @else
                <span class="badge badge-danger"><span class="dot"></span> Missed</span>
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" style="text-align: center; color: var(--text3); padding: 20px;">No contribution records found matching the filters.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
