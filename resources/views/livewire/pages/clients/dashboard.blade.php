<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\with;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Payment;
use App\Models\Contribution;

layout('layouts.client');

with(function () {
    $userId = auth()->id();
    
    // Calculate total savings (sum of contributions paid)
    $totalSavings = Contribution::where('user_id', $userId)
        ->where('is_missed', false)
        ->sum('contribution');

    // Calculate total welfare contributed
    $totalWelfare = Contribution::where('user_id', $userId)
        ->where('is_missed', false)
        ->sum('welfare');

    // Get active loans and calculate outstanding balance
    $activeLoans = Loan::where('user_id', $userId)
        ->whereIn('status', ['active', 'defaulted'])
        ->get();
        
    $loansBalance = $activeLoans->sum(function ($loan) {
        $totalOwed = $loan->amount + $loan->interest;
        return max(0, $totalOwed - $loan->amount_repaid);
    });

    $pendingLoansCount = Loan::where('user_id', $userId)
        ->where('status', 'pending')
        ->count();

    // Goal Target Progress (across all active books)
    $activeBooks = Book::where('user_id', $userId)
        ->where('status', 'active')
        ->get();
        
    $totalTarget = $activeBooks->sum(function ($book) {
        return $book->duration_weeks * $book->contribution_amount;
    });
    
    $progressPercent = $totalTarget > 0 ? min(100, round(($totalSavings / $totalTarget) * 100)) : 0;

    // Recent Payments
    $recentPayments = Payment::where('user_id', $userId)
        ->latest('paid_at')
        ->take(5)
        ->get();

    return [
        'totalSavings' => $totalSavings,
        'totalWelfare' => $totalWelfare,
        'loansBalance' => $loansBalance,
        'pendingLoansCount' => $pendingLoansCount,
        'progressPercent' => $progressPercent,
        'totalTarget' => $totalTarget,
        'recentPayments' => $recentPayments,
        'activeBooks' => $activeBooks
    ];
});

?>

<div class="page active" id="page-client-dashboard">
  <!-- Stats Grid -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon" style="background:var(--success-bg);color:var(--success)">💰</div>
      <div class="stat-label">Total Savings</div>
      <div class="stat-value" style="color:var(--success)">GH₵ {{ number_format($totalSavings, 2) }}</div>
      <div class="stat-change" style="color:var(--text3)">Current cash pool</div>
    </div>
    
    <div class="stat-card">
      <div class="stat-icon" style="background:var(--info-bg);color:var(--info)">🛡️</div>
      <div class="stat-label">Welfare Fund</div>
      <div class="stat-value" style="color:var(--info)">GH₵ {{ number_format($totalWelfare, 2) }}</div>
      <div class="stat-change" style="color:var(--text3)">Insurance contribution</div>
    </div>

    <div class="stat-card">
      <div class="stat-icon" style="background:var(--purple-bg);color:var(--purple)">🏦</div>
      <div class="stat-label">Loan Balance</div>
      <div class="stat-value" style="color:var(--purple)">GH₵ {{ number_format($loansBalance, 2) }}</div>
      <div class="stat-change" style="color:var(--text3)">Outstanding repayment</div>
    </div>

    <div class="stat-card">
      <div class="stat-icon" style="background:var(--warn-bg);color:var(--warn)">⏳</div>
      <div class="stat-label">Pending Requests</div>
      <div class="stat-value" style="color:var(--warn)">{{ $pendingLoansCount }}</div>
      <div class="stat-change" style="color:var(--text3)">Loans awaiting approval</div>
    </div>
  </div>

  <div class="grid-2" style="margin-bottom: 20px;">
    <!-- Goal Progress Card -->
    <div class="card">
      <div class="card-header">
        <div>
          <div class="card-title">Savings Target Tracker</div>
          <div class="card-sub">Overall progress across active accounts</div>
        </div>
        <span class="badge badge-success">{{ $progressPercent }}% Achieved</span>
      </div>
      
      <div style="padding: 10px 0; display:flex; flex-direction:column; gap: 15px;">
        <div style="display:flex; justify-content:space-between; font-size: var(--fs-md); font-weight:600;">
            <span>GH₵ {{ number_format($totalSavings, 2) }} saved</span>
            <span style="color:var(--text3)">Target: GH₵ {{ number_format($totalTarget, 2) }}</span>
        </div>
        
        <div class="progress-bar" style="height: 12px; border-radius: 6px;">
            <div class="progress-fill" style="width: {{ $progressPercent }}%; background: linear-gradient(90deg, var(--accent) 0%, var(--info) 100%);"></div>
        </div>

        <div style="font-size: var(--fs-sm); color:var(--text2); background:var(--bg3); padding: 10px; border-radius:var(--r); border:1px solid var(--border)">
            💡 <strong>Susu Savings Tip:</strong> Automate your payments by completing your weekly contributions in the <strong>Make Payment</strong> simulator. Regular saving improves your loan ceiling!
        </div>
      </div>
    </div>

    <!-- Active Books Quick Summary -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">Active Passbooks</div>
        <a href="/client/books" class="btn btn-outline btn-xs" wire:navigate>View All Passbooks</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Book #</th>
              <th>Weekly Contribution</th>
              <th>Weeks Target</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($activeBooks as $book)
            <tr>
              <td class="mono" style="color:var(--accent)">#{{ $book->book_number }}</td>
              <td class="mono">GH₵ {{ number_format($book->contribution_amount, 2) }}</td>
              <td class="mono">{{ $book->duration_weeks }} weeks</td>
              <td><span class="badge badge-success"><span class="dot"></span> Active</span></td>
            </tr>
            @empty
            <tr>
              <td colspan="4" style="text-align: center; color:var(--text3); padding: 15px;">No active savings books found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Recent Transactions -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">Recent Transactions</div>
      <a href="/client/contributions" class="btn btn-outline btn-sm" wire:navigate>View All Transactions</a>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Transaction ID</th>
            <th>Type</th>
            <th>Method</th>
            <th>Amount</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentPayments as $payment)
          <tr>
            <td style="color:var(--text3)">{{ \Carbon\Carbon::parse($payment->paid_at)->format('M j, Y H:i') }}</td>
            <td class="mono" style="color:var(--accent)">{{ $payment->transaction_id ?? '—' }}</td>
            <td>
              @if($payment->payment_type === 'contribution')
                <span class="badge badge-success">Contribution</span>
              @elseif($payment->payment_type === 'welfare')
                <span class="badge badge-info">Welfare</span>
              @elseif($payment->payment_type === 'penalty')
                <span class="badge badge-danger">Penalty</span>
              @elseif($payment->payment_type === 'loan_repayment')
                <span class="badge badge-purple">Repayment</span>
              @else
                <span class="badge badge-neutral">{{ ucfirst($payment->payment_type) }}</span>
              @endif
            </td>
            <td>{{ $payment->payment_method === 'mobile_money' ? 'MoMo' : ($payment->payment_method === 'card' ? 'Bank Card' : 'Cash') }}</td>
            <td class="mono">GH₵ {{ number_format($payment->amount_paid, 2) }}</td>
            <td>
              @if($payment->status === 'completed')
                <span class="badge badge-success"><span class="dot"></span> Success</span>
              @elseif($payment->status === 'pending')
                <span class="badge badge-warn"><span class="dot"></span> Pending</span>
              @else
                <span class="badge badge-danger"><span class="dot"></span> Failed</span>
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" style="text-align: center; color: var(--text3); padding: 20px;">No transaction records found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
