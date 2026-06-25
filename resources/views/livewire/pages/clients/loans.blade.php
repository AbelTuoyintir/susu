<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\with;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Contribution;

layout('layouts.client');

state([
    'showRequestForm' => false,
    'selectedBookId' => '',
    'amount' => '',
    'interest' => 0,
    'savings' => 0,
    'maxLoanLimit' => 0,
]);

$updatedSelectedBookId = function ($val) {
    if (!$val) {
        $this->savings = 0;
        $this->maxLoanLimit = 0;
        return;
    }
    
    $book = Book::find($val);
    if ($book) {
        $this->savings = Contribution::where('book_id', $book->id)
            ->where('is_missed', false)
            ->sum('contribution');
        $this->maxLoanLimit = round($this->savings * 0.70, 2);
    }
};

$updatedAmount = function ($val) {
    if (!$val || !is_numeric($val)) {
        $this->interest = 0;
        return;
    }
    $this->interest = round($val * 0.10, 2);
};

$submitRequest = function () {
    $this->validate([
        'selectedBookId' => 'required|exists:books,id',
        'amount' => 'required|numeric|min:1|max:' . $this->maxLoanLimit,
    ], [
        'amount.max' => 'The loan request amount exceeds your ceiling of GH₵ ' . number_format($this->maxLoanLimit, 2) . ' (70% of total savings).',
    ]);
    
    // Check if they already have a pending/active loan on this book
    $hasLoan = Loan::where('book_id', $this->selectedBookId)
        ->whereIn('status', ['pending', 'active', 'defaulted'])
        ->exists();
        
    if ($hasLoan) {
        session()->flash('error', 'You already have an active, defaulted, or pending loan associated with this savings book.');
        return;
    }
    
    Loan::create([
        'user_id' => auth()->id(),
        'book_id' => $this->selectedBookId,
        'amount' => $this->amount,
        'interest' => $this->interest,
        'due_date' => now()->addDays(30),
        'status' => 'pending',
    ]);
    
    // Reset fields
    $this->selectedBookId = '';
    $this->amount = '';
    $this->interest = 0;
    $this->savings = 0;
    $this->maxLoanLimit = 0;
    $this->showRequestForm = false;
    
    session()->flash('success', 'Your loan request has been successfully submitted and is awaiting administrator approval.');
};

with(function () {
    $userId = auth()->id();
    
    // Fetch all user's loans
    $loans = Loan::where('user_id', $userId)
        ->with('book')
        ->latest()
        ->get();

    // Fetch active books for loan options
    $activeBooks = Book::where('user_id', $userId)
        ->where('status', 'active')
        ->get();

    // Summary metrics
    $totalDisbursed = Loan::where('user_id', $userId)->whereIn('status', ['active', 'paid'])->sum('amount');
    $outstandingBalance = Loan::where('user_id', $userId)->whereIn('status', ['active', 'defaulted'])->get()->sum(function($l) {
        return max(0, ($l->amount + $l->interest) - $l->amount_repaid);
    });
    $paidLoansCount = Loan::where('user_id', $userId)->where('status', 'paid')->count();

    return [
        'loans' => $loans,
        'activeBooks' => $activeBooks,
        'totalDisbursed' => $totalDisbursed,
        'outstandingBalance' => $outstandingBalance,
        'paidLoansCount' => $paidLoansCount,
    ];
});

?>

<div class="page active" id="page-client-loans">
  <!-- Stats Summary -->
  <div class="stats-grid" style="grid-template-columns:repeat(3, 1fr); margin-bottom:16px;">
    <div class="stat-card">
      <div class="stat-label">Total Amount Borrowed</div>
      <div class="stat-value" style="color:var(--purple)">GH₵ {{ number_format($totalDisbursed, 2) }}</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Outstanding Loan Balance</div>
      <div class="stat-value" style="color:var(--danger)">GH₵ {{ number_format($outstandingBalance, 2) }}</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Fully Settled Loans</div>
      <div class="stat-value" style="color:var(--success)">{{ $paidLoansCount }} Loans</div>
    </div>
  </div>

  <!-- Messages -->
  @if (session()->has('success'))
    <div class="card" style="background:var(--success-bg); border-color:var(--success); color:var(--success); padding: 12px; margin-bottom:16px; font-weight:500;">
      ✔️ {{ session('success') }}
    </div>
  @endif

  @if (session()->has('error'))
    <div class="card" style="background:var(--danger-bg); border-color:var(--danger); color:var(--danger); padding: 12px; margin-bottom:16px; font-weight:500;">
      ⚠️ {{ session('error') }}
    </div>
  @endif

  <!-- Toggle Buttons -->
  <div style="margin-bottom:16px; display:flex; justify-content:space-between; align-items:center;">
    <div class="card-title" style="margin:0;">{{ $showRequestForm ? 'New Loan Request Form' : 'Active & Previous Loans' }}</div>
    
    @if(!$showRequestForm)
      <button class="btn btn-primary btn-sm" wire:click="$set('showRequestForm', true)">Apply for Loan</button>
    @else
      <button class="btn btn-outline btn-sm" wire:click="$set('showRequestForm', false)">Back to Loans</button>
    @endif
  </div>

  @if($showRequestForm)
    <!-- Loan Application Form -->
    <div class="card" style="max-width:600px; margin: 0 auto;">
      <form wire:submit="submitRequest" style="display:flex; flex-direction:column; gap:14px;">
        <div class="form-group">
          <label class="form-label">Select Active Savings Book *</label>
          <select wire:model.live="selectedBookId" class="filter-input" style="width:100%" required>
            <option value="">-- Choose Savings Book --</option>
            @foreach($activeBooks as $book)
              <option value="{{ $book->id }}">Book #{{ $book->book_number }} (GH₵ {{ number_format($book->contribution_amount) }}/wk)</option>
            @endforeach
          </select>
          @error('selectedBookId') <span style="color:var(--danger); font-size:11px;">{{ $message }}</span> @enderror
        </div>

        @if($selectedBookId)
          <!-- Dynamic Ceiling/Savings Info -->
          <div class="grid-2" style="background:var(--bg3); padding: 12px; border-radius:var(--r); border:1px solid var(--border); gap: 10px; margin-bottom:4px;">
            <div>
              <div style="font-size:10px; color:var(--text3);">Total Savings Accumulated</div>
              <div style="font-size:var(--fs-md); font-weight:600; color:var(--success);">GH₵ {{ number_format($savings, 2) }}</div>
            </div>
            <div>
              <div style="font-size:10px; color:var(--text3);">Max Loan Ceiling (70%)</div>
              <div style="font-size:var(--fs-md); font-weight:600; color:var(--accent);">GH₵ {{ number_format($maxLoanLimit, 2) }}</div>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Principal Amount to Borrow (GH₵) *</label>
            <input type="number" step="0.01" max="{{ $maxLoanLimit }}" wire:model.live="amount" class="form-input" placeholder="Enter amount to borrow" required>
            @error('amount') <span style="color:var(--danger); font-size:11px;">{{ $message }}</span> @enderror
          </div>

          @if($amount && is_numeric($amount))
            <!-- Repayment Summary Breakdown -->
            <div style="background:var(--bg3); padding: 12px; border-radius:var(--r); border:1px solid var(--border); margin-bottom: 4px;">
              <div style="font-weight:600; font-size:var(--fs-md); margin-bottom:8px; border-bottom:1px solid var(--border); padding-bottom:4px;">Loan Repayment Summary</div>
              <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                <span style="color:var(--text2)">Principal Amount:</span>
                <span class="mono">GH₵ {{ number_format((float)$amount, 2) }}</span>
              </div>
              <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                <span style="color:var(--text2)">Interest Applied (10%):</span>
                <span class="mono" style="color:var(--warn)">GH₵ {{ number_format((float)$interest, 2) }}</span>
              </div>
              <div style="display:flex; justify-content:space-between; font-weight:600; margin-top:8px; border-top:1px solid var(--border); padding-top:6px; color:var(--accent)">
                <span>Total Repayment Due:</span>
                <span class="mono">GH₵ {{ number_format((float)$amount + (float)$interest, 2) }}</span>
              </div>
              <div style="font-size:9px; color:var(--text3); margin-top:8px;">
                * Repayment is due in full within 30 days ({{ now()->addDays(30)->format('M j, Y') }}).
              </div>
            </div>
          @endif
        @endif

        <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:8px;">
          <button type="button" class="btn btn-outline" wire:click="$set('showRequestForm', false)">Cancel</button>
          <button type="submit" class="btn btn-primary" @if(!$selectedBookId || !$amount) disabled @endif>Submit Loan Application</button>
        </div>
      </form>
    </div>
  @else
    <!-- Loans Listing Table -->
    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Loan ID</th>
              <th>Passbook</th>
              <th>Principal</th>
              <th>Interest (10%)</th>
              <th>Total Repayment</th>
              <th>Amount Settled</th>
              <th>Due Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($loans as $loan)
              @php
                $totalOwed = $loan->amount + $loan->interest;
                $repaid = $loan->amount_repaid;
                $progress = $loan->progress_percentage;
              @endphp
              <tr>
                <td class="mono" style="color:var(--accent)">#{{ sprintf('LN-%04d', $loan->id) }}</td>
                <td class="mono">Book #{{ $loan->book->book_number ?? '—' }}</td>
                <td class="mono">GH₵ {{ number_format($loan->amount, 2) }}</td>
                <td class="mono">GH₵ {{ number_format($loan->interest, 2) }}</td>
                <td class="mono" style="color:var(--accent)">GH₵ {{ number_format($totalOwed, 2) }}</td>
                <td>
                  <div style="width:100px;">
                    <div class="progress-bar" style="height:5px;"><div class="progress-fill" style="width:{{ $progress }}%; background:{{ $loan->status === 'defaulted' ? 'var(--danger)' : ($loan->status === 'paid' ? 'var(--success)' : 'var(--info)') }}"></div></div>
                    <div style="font-size:9px; color:var(--text3); margin-top:2px;">GH₵ {{ number_format($repaid, 2) }} ({{ $progress }}%)</div>
                  </div>
                </td>
                <td style="color:var(--text3)">{{ \Carbon\Carbon::parse($loan->due_date)->format('M j, Y') }}</td>
                <td>
                  @if($loan->status === 'active')
                    <span class="badge badge-info">Active</span>
                  @elseif($loan->status === 'paid')
                    <span class="badge badge-success">Paid</span>
                  @elseif($loan->status === 'defaulted')
                    <span class="badge badge-danger">Defaulted</span>
                  @elseif($loan->status === 'pending')
                    <span class="badge badge-warn">Pending Approval</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" style="text-align: center; color: var(--text3); padding: 20px;">No loan applications or active loans found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  @endif
</div>
