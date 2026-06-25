<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\with;
use App\Models\Loan;

layout('layouts.admin');

state([
    'statusFilter' => 'All Loans',
    'search' => '',
]);

with(function () {
    $query = Loan::with(['user', 'payments']);

    if ($this->search) {
        $query->whereHas('user', function($q) {
            $q->where('name', 'like', '%' . $this->search . '%')
              ->orWhere('member_id', 'like', '%' . $this->search . '%');
        });
    }

    if ($this->statusFilter !== 'All Loans') {
        $query->where('status', strtolower($this->statusFilter));
    }

    $loans = $query->latest()->get();

    return [
        'loans' => $loans,
        'totalDisbursed' => Loan::whereIn('status', ['active', 'paid'])->sum('amount'),
        'activeLoansCount' => Loan::where('status', 'active')->count(),
        'overdueLoansCount' => Loan::where('status', 'defaulted')->count(),
        'interestEarned' => Loan::whereIn('status', ['active', 'paid'])->sum('interest'),
        'pendingLoansCount' => Loan::where('status', 'pending')->count(),
    ];
});

$approveLoan = function ($id) {
    Loan::findOrFail($id)->update(['status' => 'active']);
};

$rejectLoan = function ($id) {
    Loan::findOrFail($id)->delete();
};

$markOverdue = function ($id) {
    Loan::findOrFail($id)->update(['status' => 'defaulted']);
};

?>

<!-- ═══════════════════════════════════════════
     PAGE 5: LOANS
═══════════════════════════════════════════ -->
<div class="page active" id="page-loans">
  <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:16px">
    <div class="stat-card"><div class="stat-label">Total Disbursed</div><div class="stat-value" style="color:var(--purple)">GH₵ {{ number_format($totalDisbursed, 2) }}</div></div>
    <div class="stat-card"><div class="stat-label">Active Loans</div><div class="stat-value" style="color:var(--success)">{{ $activeLoansCount }}</div></div>
    <div class="stat-card"><div class="stat-label">Overdue/Defaulted</div><div class="stat-value" style="color:var(--danger)">{{ $overdueLoansCount }}</div></div>
    <div class="stat-card"><div class="stat-label">Expected Interest</div><div class="stat-value" style="color:var(--warn)">GH₵ {{ number_format($interestEarned, 2) }}</div></div>
  </div>
  
  @if($pendingLoansCount > 0)
  <div class="alert-banner danger">🔴 <strong>{{ $pendingLoansCount }} loan request(s)</strong> are awaiting your approval.</div>
  @endif

  <div class="filters">
    <select wire:model.live="statusFilter" class="filter-input">
        <option>All Loans</option>
        <option>Pending</option>
        <option>Active</option>
        <option>Defaulted</option>
        <option>Paid</option>
    </select>
    <input wire:model.live="search" class="filter-input" type="text" placeholder="Search user…" style="width:180px">
    <a href="{{ route('loans.add') }}" class="btn btn-primary btn-sm" wire:navigate>+ Request Loan</a>
  </div>
  
  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr><th>Loan ID</th><th>Borrower</th><th>Amount</th><th>Interest</th><th>Due Date</th><th>Repaid</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          @forelse($loans as $loan)
          <tr>
            <td class="mono" style="color:var(--accent)">#{{ sprintf('LN-%04d', $loan->id) }}</td>
            <td>
              @if($loan->user)
              <div class="user-row">
                <div class="user-avatar" style="background:#6c5ce7">{{ strtoupper(substr($loan->user->name, 0, 2)) }}</div>
                {{ $loan->user->name }}
              </div>
              @endif
            </td>
            <td class="mono">GH₵ {{ number_format($loan->amount, 2) }}</td>
            <td class="mono">GH₵ {{ number_format($loan->interest, 2) }}</td>
            <td style="color:var(--text3)">{{ \Carbon\Carbon::parse($loan->due_date)->format('M j, Y') }}</td>
            <td>
              @if($loan->status === 'pending')
                 <span style="color:var(--text3)">—</span>
              @else
                <div style="width:80px">
                    @php $progress = $loan->progress_percentage; @endphp
                    @if($progress >= 100)
                        <div class="progress-bar"><div class="progress-fill" style="width:100%;background:var(--success)"></div></div>
                        <div style="font-size:10px;color:var(--success);margin-top:2px">Fully Paid</div>
                    @elseif($loan->status === 'defaulted')
                        <div class="progress-bar"><div class="progress-fill" style="width:{{ $progress }}%;background:var(--danger)"></div></div>
                        <div style="font-size:10px;color:var(--danger);margin-top:2px">{{ $progress }}% — Overdue</div>
                    @else
                        <div class="progress-bar"><div class="progress-fill" style="width:{{ $progress }}%;background:var(--info)"></div></div>
                        <div style="font-size:10px;color:var(--text3);margin-top:2px">{{ $progress }}%</div>
                    @endif
                </div>
              @endif
            </td>
            <td>
                @if($loan->status === 'active')
                    <span class="badge badge-info">Active</span>
                @elseif($loan->status === 'paid')
                    <span class="badge badge-success">Paid</span>
                @elseif($loan->status === 'defaulted')
                    <span class="badge badge-danger">Defaulted</span>
                @elseif($loan->status === 'pending')
                    <span class="badge badge-warn">Pending</span>
                @endif
            </td>
            <td>
                @if($loan->status === 'pending')
                    <div style="display:flex;gap:4px">
                        <button class="btn btn-primary btn-xs" wire:click="approveLoan({{ $loan->id }})" wire:confirm="Approve this loan?">Approve</button>
                        <button class="btn btn-danger btn-xs" wire:click="rejectLoan({{ $loan->id }})" wire:confirm="Reject and delete this request?">Reject</button>
                    </div>
                @elseif($loan->status === 'active')
                    <div style="display:flex;gap:4px">
                        <button class="btn btn-outline btn-xs">Pay</button>
                        <button class="btn btn-danger btn-xs" wire:click="markOverdue({{ $loan->id }})" wire:confirm="Mark as overdue/defaulted?">Overdue</button>
                    </div>
                @elseif($loan->status === 'defaulted')
                    <div style="display:flex;gap:4px">
                        <button class="btn btn-outline btn-xs">Record Pay</button>
                    </div>
                @else
                    <button class="btn btn-outline btn-xs">Details</button>
                @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="8" style="text-align: center; color: var(--text3); padding: 20px;">No loans found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
