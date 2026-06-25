<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\with;
use App\Models\Book;
use App\Models\Contribution;

layout('layouts.client');

with(function () {
    $userId = auth()->id();
    
    // Load all books for this client along with their contributions
    $books = Book::where('user_id', $userId)
        ->with('contributions')
        ->latest()
        ->get();

    // Map contributions for each book for quick lookup in blade
    $bookContributions = [];
    foreach ($books as $book) {
        $bookContributions[$book->id] = $book->contributions->keyBy('week_number');
    }

    return [
        'books' => $books,
        'bookContributions' => $bookContributions,
    ];
});

?>

<div class="page active" id="page-client-books">
  <style>
    .weeks-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(40px, 1fr));
      gap: 8px;
      margin-top: 15px;
    }
    .week-box {
      aspect-ratio: 1;
      border-radius: 6px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      font-size: 10px;
      font-weight: 600;
      cursor: pointer;
      position: relative;
      transition: all 0.15s ease-in-out;
      border: 1px solid transparent;
      user-select: none;
    }
    .week-box:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.25);
    }
    .week-box.paid {
      background: var(--success-bg);
      color: var(--success);
      border-color: rgba(63, 185, 80, 0.25);
    }
    .week-box.missed {
      background: var(--danger-bg);
      color: var(--danger);
      border-color: rgba(248, 81, 73, 0.25);
    }
    .week-box.pending {
      background: var(--bg3);
      color: var(--text3);
      border-color: var(--border);
    }
    
    /* Hover Tooltip styling */
    .week-box .tooltip {
      visibility: hidden;
      opacity: 0;
      width: 140px;
      background: var(--bg2);
      border: 1px solid var(--border2);
      color: var(--text);
      text-align: center;
      border-radius: var(--r);
      padding: 8px;
      position: absolute;
      z-index: 10;
      bottom: 125%;
      left: 50%;
      transform: translateX(-50%);
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
      transition: opacity 0.15s ease-in-out, visibility 0.15s ease-in-out;
      font-size: 10px;
      pointer-events: none;
      line-height: 1.4;
    }
    .week-box .tooltip::after {
      content: "";
      position: absolute;
      top: 100%;
      left: 50%;
      transform: translateX(-50%);
      border-width: 5px;
      border-style: solid;
      border-color: var(--border2) transparent transparent transparent;
    }
    .week-box:hover .tooltip {
      visibility: visible;
      opacity: 1;
    }
    .legend-indicator {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: var(--fs-sm);
      color: var(--text2);
    }
    .legend-color {
      width: 12px;
      height: 12px;
      border-radius: 3px;
    }
  </style>

  @forelse($books as $book)
    @php
      $contribs = $bookContributions[$book->id];
      $savedAmount = $contribs->where('is_missed', false)->sum('contribution');
      $targetAmount = $book->duration_weeks * $book->contribution_amount;
      $percent = $targetAmount > 0 ? round(($savedAmount / $targetAmount) * 100) : 0;
      $weeksPaidCount = $contribs->where('is_missed', false)->count();
      $weeksMissedCount = $contribs->where('is_missed', true)->count();
    @endphp

    <div class="card" style="margin-bottom: 24px;">
      <!-- Passbook Title Details -->
      <div class="card-header" style="border-bottom: 1px solid var(--border); padding-bottom: 12px; align-items: flex-start; flex-direction: row;">
        <div>
          <div style="display:flex; align-items:center; gap: 8px;">
            <div class="card-title" style="font-size: var(--fs-lg);">Susu Book #{{ $book->book_number }}</div>
            <span class="badge {{ $book->status === 'active' ? 'badge-success' : 'badge-neutral' }}">{{ ucfirst($book->status) }}</span>
          </div>
          <div class="card-sub" style="margin-top: 4px;">
            Start Date: <strong>{{ \Carbon\Carbon::parse($book->start_date)->format('M j, Y') }}</strong> &nbsp;·&nbsp; 
            End Date: <strong>{{ \Carbon\Carbon::parse($book->end_date)->format('M j, Y') }}</strong>
          </div>
        </div>
        
        <div style="text-align: right;">
          <div style="font-size: 16px; font-weight:600; color:var(--accent);">GH₵ {{ number_format($savedAmount, 2) }} Saved</div>
          <div style="font-size: 10px; color:var(--text3);">Target: GH₵ {{ number_format($targetAmount, 2) }} ({{ $percent }}%)</div>
        </div>
      </div>

      <!-- Quick Metrics Strip -->
      <div class="grid-3" style="margin: 16px 0; gap:12px;">
        <div style="background:var(--bg3); padding:10px 12px; border-radius:var(--r); border:1px solid var(--border)">
          <div style="color:var(--text3); font-size:10px;">Contribution Target</div>
          <div style="font-weight:600; font-size:var(--fs-md); color:var(--text); margin-top:2px;">GH₵ {{ number_format($book->contribution_amount, 2) }} / week</div>
        </div>
        <div style="background:var(--bg3); padding:10px 12px; border-radius:var(--r); border:1px solid var(--border)">
          <div style="color:var(--text3); font-size:10px;">Savings Progress</div>
          <div style="font-weight:600; font-size:var(--fs-md); color:var(--success); margin-top:2px;">{{ $weeksPaidCount }} / {{ $book->duration_weeks }} Weeks Paid</div>
        </div>
        <div style="background:var(--bg3); padding:10px 12px; border-radius:var(--r); border:1px solid var(--border)">
          <div style="color:var(--text3); font-size:10px;">Missed & Penalties</div>
          <div style="font-weight:600; font-size:var(--fs-md); color:var(--danger); margin-top:2px;">{{ $weeksMissedCount }} Weeks Missed</div>
        </div>
      </div>

      <!-- Visual Legend -->
      <div style="display:flex; gap:16px; margin-bottom: 12px; align-items:center;">
        <span style="font-weight:500; font-size:10px; text-transform:uppercase; color:var(--text3); letter-spacing:.5px;">Legend:</span>
        <div class="legend-indicator">
          <div class="legend-color" style="background:var(--success-bg); border:1px solid rgba(63, 185, 80, 0.3);"></div>
          <span>Paid Week</span>
        </div>
        <div class="legend-indicator">
          <div class="legend-color" style="background:var(--danger-bg); border:1px solid rgba(248, 81, 73, 0.3);"></div>
          <span>Missed Week</span>
        </div>
        <div class="legend-indicator">
          <div class="legend-color" style="background:var(--bg3); border:1px solid var(--border);"></div>
          <span>Pending / Future</span>
        </div>
      </div>

      <!-- 55-Week visual passbook grid -->
      <div class="weeks-grid">
        @for($w = 1; $w <= $book->duration_weeks; $w++)
          @php
            $contrib = $contribs->get($w);
          @endphp
          
          @if($contrib)
            @if(!$contrib->is_missed)
              <!-- Paid Week -->
              <div class="week-box paid">
                W{{ $w }}
                <div class="tooltip">
                  <strong>Week {{ $w }} Paid</strong><br>
                  Savings: GH₵ {{ number_format($contrib->contribution, 2) }}<br>
                  Welfare: GH₵ {{ number_format($contrib->welfare, 2) }}<br>
                  Date: {{ \Carbon\Carbon::parse($contrib->created_at)->format('M j, Y') }}
                </div>
              </div>
            @else
              <!-- Missed Week -->
              <div class="week-box missed">
                W{{ $w }}
                <div class="tooltip" style="border-color:var(--danger)">
                  <strong>Week {{ $w }} Missed ⚠️</strong><br>
                  Penalty: GH₵ {{ number_format($contrib->penalty, 2) }}<br>
                  Status: Overdue
                </div>
              </div>
            @endif
          @else
            <!-- Unpaid / Future Week -->
            <div class="week-box pending">
              W{{ $w }}
              <div class="tooltip">
                <strong>Week {{ $w }} Unpaid</strong><br>
                Savings Target: GH₵ {{ number_format($book->contribution_amount, 2) }}<br>
                Status: Pending
              </div>
            </div>
          @endif
        @endfor
      </div>

      <!-- Book Action Trigger -->
      <div style="margin-top: 16px; text-align:right;">
        <a href="/client/payments?book_id={{ $book->id }}&type=contribution" class="btn btn-primary btn-sm" wire:navigate>
          Pay Contribution Online
        </a>
      </div>
    </div>
  @empty
    <div class="card" style="text-align:center; padding: 40px;">
      <div style="font-size: 32px; margin-bottom: 12px;">📒</div>
      <div class="card-title">No savings passbooks found</div>
      <div class="card-sub">You do not currently have any active savings books assigned. Please contact the administrator.</div>
    </div>
  @endforelse
</div>
