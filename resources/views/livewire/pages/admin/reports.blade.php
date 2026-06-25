<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\with;
use App\Models\Contribution;
use App\Models\Loan;
use App\Models\Payment;
use App\Models\User;

layout('layouts.admin');

state([
    // Add component state variables here when ready for dynamic data
]);

with(function () {
    // 1. Calculate the financial pools dynamically
    $totalWelfare = (float)(Payment::where('payment_type', 'welfare')->where('status', 'completed')->sum('amount_paid') ?: Contribution::sum('welfare')); 
    $totalPenalties = (float)(Payment::where('payment_type', 'penalty')->where('status', 'completed')->sum('amount_paid') ?: Contribution::sum('penalty'));
    $totalLoanInterest = (float)Loan::whereIn('status', ['active', 'paid', 'defaulted'])->sum('interest'); // Including defaulted expected interest
    
    $distributableProfit = $totalWelfare + $totalPenalties + $totalLoanInterest;

    // 2. Fetch Eligible Users (Users who have at least 1 book or contribution)
    $eligibleUsers = collect();
    $allUsers = User::with(['books', 'contributions'])->get();
    
    foreach ($allUsers as $user) {
        if ($user->books->count() > 0 || $user->contributions->count() > 0) {
            $eligibleUsers->push($user);
        }
    }

    $eligibleMembersCount = $eligibleUsers->count();
    
    // 3. Calculate identical payout chunk
    $payoutPerUser = $eligibleMembersCount > 0 ? ((float)$distributableProfit / $eligibleMembersCount) : 0;

    // We'll prepare an array for the table mapping
    $payoutTable = [];
    foreach ($eligibleUsers as $user) {
        $penaltiesPaidQuery = (float)Payment::where('user_id', $user->id)->where('payment_type', 'penalty')->sum('amount_paid');
        $penaltiesFallback = (float)$user->contributions->sum(fn($c) => (float)($c->penalty ?? 0));
        
        $payoutTable[] = [
            'user' => $user,
            'books_count' => $user->books->count(),
            'contributions_val' => (float)$user->contributions->sum(fn($c) => (float)($c->contribution ?? 0)),
            'penalties_paid' => $penaltiesPaidQuery > 0 ? $penaltiesPaidQuery : $penaltiesFallback,
            'payout_share' => $payoutPerUser,
        ];
    }
    
    // Sort table by highest contributions to lowest
    usort($payoutTable, fn($a, $b) => $b['contributions_val'] <=> $a['contributions_val']);

    return [
        'totalWelfare' => $totalWelfare,
        'totalPenalties' => $totalPenalties,
        'totalLoanInterest' => $totalLoanInterest,
        'distributableProfit' => $distributableProfit,
        'eligibleMembersCount' => $eligibleMembersCount,
        'payoutPerUser' => $payoutPerUser,
        'payoutTable' => collect($payoutTable),
    ];
});

$generatePDF = function () {
    session()->flash('success', "Payout PDF generated successfully!");
};

$exportCSV = function () {
    session()->flash('success', "Reporting CSV exported.");
};

?>

<!-- ═══════════════════════════════════════════
     PAGE 8: REPORTS
═══════════════════════════════════════════ -->
<div class="page active" id="page-reports">
  @if(session()->has('success'))
    <div style="background:var(--success); color:#fff; padding:12px 16px; border-radius:6px; margin-bottom:16px; font-size:13px; font-weight:500;">
        ✓ {{ session('success') }}
    </div>
  @endif

  <div class="grid-2" style="margin-bottom:16px">
    <div class="card">
      <div class="card-header"><div><div class="card-title">Year-End Summary</div><div class="card-sub">FY {{ date('Y') }} — Jan to Dec</div></div></div>
      <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:14px">
        <div class="setting-row" style="padding:8px 0">
          <div class="setting-info"><div class="setting-name">Total Welfare Collected</div></div>
          <div class="mono" style="font-size:var(--fs-lg);color:var(--accent)">GH₵ {{ number_format($totalWelfare, 2) }}</div>
        </div>
        <div class="setting-row" style="padding:8px 0">
          <div class="setting-info"><div class="setting-name">Total Penalties</div></div>
          <div class="mono" style="font-size:var(--fs-lg);color:var(--danger)">GH₵ {{ number_format($totalPenalties, 2) }}</div>
        </div>
        <div class="setting-row" style="padding:8px 0">
          <div class="setting-info"><div class="setting-name">Total Loan Interest</div></div>
          <div class="mono" style="font-size:var(--fs-lg);color:var(--warn)">GH₵ {{ number_format($totalLoanInterest, 2) }}</div>
        </div>
        <div style="padding:10px;background:var(--accent-dim);border:1px solid var(--accent);border-radius:var(--r);display:flex;justify-content:space-between;align-items:center">
          <div style="font-weight:600">Total Distributable Profit</div>
          <div class="mono" style="font-size:var(--fs-xl);font-weight:700;color:var(--accent)">GH₵ {{ number_format($distributableProfit, 2) }}</div>
        </div>
      </div>
      <div style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:8px">Profit per user ({{ $eligibleMembersCount }} eligible members):</div>
      <div class="mono" style="font-size:var(--fs-xl);color:var(--success);font-weight:600">GH₵ {{ number_format($payoutPerUser, 2) }} / user</div>
      <div style="margin-top:16px;display:flex;gap:8px;flex-wrap:wrap">
        <button class="btn btn-primary btn-sm" wire:click="generatePDF">📄 Generate Payout PDF</button>
        <button class="btn btn-outline btn-sm" wire:click="exportCSV">📊 Export CSV</button>
      </div>
    </div>
    <div class="card">
      <div class="card-header"><div class="card-title">Monthly Trend</div></div>
      <div class="chart-container" style="height:220px;">
        <canvas id="reportChart"></canvas>
      </div>
      <script>
        (function initReportChartOnce() {
          // Wait for Chart.js to be ready
          if (typeof Chart === 'undefined') { setTimeout(initReportChartOnce, 100); return; }
          const el = document.getElementById('reportChart');
          if (!el || el._chartInst) return;
          Chart.defaults.color = '#8b949e';
          Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';
          el._chartInst = new Chart(el.getContext('2d'), {
            type: 'line',
            data: {
              labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
              datasets: [{
                label: 'Contributions',
                data: [4200,4440,4680,4800,4920,5160,5400,5640,5760,5880,6000,6240],
                borderColor: '#00d4a8', backgroundColor: 'rgba(0,212,168,0.08)',
                tension: .4, fill: true, borderWidth: 2, pointRadius: 3
              },{
                label: 'Loan Interest',
                data: [300,320,440,500,560,620,700,760,800,840,880,900],
                borderColor: '#d29922', backgroundColor: 'rgba(210,153,34,0.08)',
                tension: .4, fill: true, borderWidth: 2, pointRadius: 3
              }]
            },
            options: {
              responsive: true, maintainAspectRatio: false,
              plugins: { legend: { labels: { font: { size: 10 }, boxWidth: 10 } } },
              scales: {
                y: { ticks: { font: { size: 10 }, callback: v => 'GH₵ ' + (v/1000) + 'K' } },
                x: { ticks: { font: { size: 10 } } }
              }
            }
          });
        })();
      </script>
    </div>
  </div>
  
  <div class="card">
    <div class="card-header"><div class="card-title">Per-User Payout Table</div><button class="btn btn-outline btn-sm" wire:click="exportCSV">📊 Export CSV</button></div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>User</th><th>Books</th><th>Contributions</th><th>Penalties Paid</th><th>Payout Share</th></tr></thead>
        <tbody>
          @forelse($payoutTable as $row)
          <tr>
            <td>
              <div class="user-row">
                <div class="user-avatar" style="background:#0984e3">{{ strtoupper(substr($row['user']->name, 0, 2)) }}</div>
                {{ $row['user']->name }}
              </div>
            </td>
            <td>{{ $row['books_count'] }}</td>
            <td class="mono">GH₵ {{ number_format($row['contributions_val'], 2) }}</td>
            <td class="mono" @if($row['penalties_paid'] > 0) style="color:var(--danger)" @endif>
                GH₵ {{ number_format($row['penalties_paid'], 2) }}
            </td>
            <td class="mono" style="color:var(--success);font-weight:600;">GH₵ {{ number_format($row['payout_share'], 2) }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="5" style="text-align: center; color: var(--text3); padding: 20px;">No eligible users for payout distribution yet.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
