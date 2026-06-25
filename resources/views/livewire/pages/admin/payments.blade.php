<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\with;
use App\Models\Payment;
use Carbon\Carbon;

layout('layouts.admin');

state([
    'startDate' => Carbon::now()->startOfMonth()->format('Y-m-d'),
    'endDate' => Carbon::now()->endOfMonth()->format('Y-m-d'),
    'typeFilter' => 'All Types',
    'methodFilter' => 'All Methods',
]);

with(function () {
    $query = Payment::with(['user']);

    if ($this->startDate) {
        $query->whereDate('paid_at', '>=', $this->startDate);
    }
    
    if ($this->endDate) {
        $query->whereDate('paid_at', '<=', $this->endDate);
    }

    if ($this->typeFilter !== 'All Types') {
        // Map UI labels to database enum
        $typeMapping = [
            'Contribution' => 'contribution',
            'Loan' => 'loan_repayment',
            'Penalty' => 'penalty',
            'Repayment' => 'loan_repayment',
            'Welfare' => 'welfare',
        ];
        
        if (isset($typeMapping[$this->typeFilter])) {
            $query->where('payment_type', $typeMapping[$this->typeFilter]);
        }
    }

    if ($this->methodFilter !== 'All Methods') {
        $methodMapping = [
            'MoMo' => 'mobile_money',
            'Cash' => 'cash',
            'Bank Transfer' => 'card',
        ];
        
        if (isset($methodMapping[$this->methodFilter])) {
            $query->where('payment_method', $methodMapping[$this->methodFilter]);
        }
    }

    $payments = $query->latest('paid_at')->get();

    return [
        'payments' => $payments,
        'totalVolume' => Payment::where('status', 'completed')->sum('amount_paid'),
        'monthVolume' => Payment::where('status', 'completed')
                                ->whereMonth('paid_at', date('m'))
                                ->whereYear('paid_at', date('Y'))
                                ->sum('amount_paid'),
        'transactionsCount' => Payment::count(),
    ];
});

?>

<!-- ═══════════════════════════════════════════
     PAGE 6: PAYMENTS
═══════════════════════════════════════════ -->
<div class="page active" id="page-payments">
  <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:16px">
    <div class="stat-card"><div class="stat-label">Total Volume</div><div class="stat-value" style="color:var(--accent)">GH₵ {{ number_format($totalVolume, 2) }}</div></div>
    <div class="stat-card"><div class="stat-label">This Month</div><div class="stat-value" style="color:var(--success)">GH₵ {{ number_format($monthVolume, 2) }}</div></div>
    <div class="stat-card"><div class="stat-label">Transactions</div><div class="stat-value">{{ number_format($transactionsCount) }}</div></div>
  </div>
  <div class="filters">
    <input wire:model.live="startDate" class="filter-input" type="date">
    <input wire:model.live="endDate" class="filter-input" type="date">
    <select wire:model.live="typeFilter" class="filter-input">
        <option>All Types</option>
        <option>Contribution</option>
        <option>Loan</option>
        <option>Penalty</option>
        <option>Repayment</option>
        <option>Welfare</option>
    </select>
    <select wire:model.live="methodFilter" class="filter-input">
        <option>All Methods</option>
        <option>MoMo</option>
        <option>Cash</option>
        <option>Bank Transfer</option>
    </select>
  </div>
  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr><th>Txn ID</th><th>User</th><th>Type</th><th>Amount</th><th>Method</th><th>Date</th><th>Status</th></tr></thead>
        <tbody>
          @forelse($payments as $payment)
          <tr>
            <td class="mono" style="color:var(--text3);font-size:10px">{{ $payment->transaction_id }}</td>
            <td>
              @if($payment->user)
              <div class="user-row">
                <div class="user-avatar" style="background:#00b894">{{ strtoupper(substr($payment->user->name, 0, 2)) }}</div>
                {{ $payment->user->name }}
              </div>
              @else
                <span class="mono">Guest/Deleted</span>
              @endif
            </td>
            <td>
                @if($payment->payment_type === 'contribution' || $payment->payment_type === 'welfare')
                    <span class="badge badge-info">{{ ucfirst($payment->payment_type) }}</span>
                @elseif($payment->payment_type === 'penalty')
                    <span class="badge badge-danger">Penalty</span>
                @elseif($payment->payment_type === 'loan_repayment')
                    <span class="badge badge-purple">Repayment</span>
                @endif
            </td>
            <td class="mono">GH₵ {{ number_format($payment->amount_paid, 2) }}</td>
            <td>
                @if($payment->payment_method === 'mobile_money')
                    <span class="badge badge-neutral">📱 MoMo</span>
                @elseif($payment->payment_method === 'card')
                    <span class="badge badge-neutral">🏦 Bank</span>
                @else
                    <span class="badge badge-neutral">💵 Cash</span>
                @endif
            </td>
            <td style="color:var(--text3)">{{ \Carbon\Carbon::parse($payment->paid_at)->format('d M Y H:i') }}</td>
            <td>
                @if($payment->status === 'completed')
                    <span class="badge badge-success">Success</span>
                @elseif($payment->status === 'failed')
                    <span class="badge badge-danger">Failed</span>
                @else
                    <span class="badge badge-warn">Pending</span>
                @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" style="text-align: center; color: var(--text3); padding: 20px;">No transactions found for the selected dates/filters.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
