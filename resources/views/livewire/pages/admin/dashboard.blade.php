<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\state;

layout('layouts.admin');

state([
    // Add any component properties here if needed for dynamic data in the future
]);

?>

<div class="page active" id="page-dashboard">
    <div>
        <div class="page-title" id="topbar-title">Dashboard</div>
        <div class="page-subtitle" id="topbar-sub">{{ now()->format('l, j F Y') }}</div>
    </div>
    <div class="stats-grid">
        <div class="stat-card">
        <div class="stat-icon" style="background:var(--accent-dim);color:var(--accent)">👥</div>
        <div class="stat-label">Total Users</div>
        <div class="stat-value" style="color:var(--accent)">248</div>
        <div class="stat-change up">↑ 12 this month</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:var(--info-bg);color:var(--info)">📒</div>
      <div class="stat-label">Total Books</div>
      <div class="stat-value" style="color:var(--info)">612</div>
      <div class="stat-change" style="color:var(--text3)">Avg 2.4 per user</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:var(--success-bg);color:var(--success)">💰</div>
      <div class="stat-label">Contributions</div>
      <div class="stat-value" style="color:var(--success)">GH₵ 48.2K</div>
      <div class="stat-change up">↑ 8.4% vs last wk</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:var(--purple-bg);color:var(--purple)">🏦</div>
      <div class="stat-label">Loans Given</div>
      <div class="stat-value" style="color:var(--purple)">GH₵ 91.5K</div>
      <div class="stat-change" style="color:var(--text3)">34 active loans</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:var(--warn-bg);color:var(--warn)">📈</div>
      <div class="stat-label">Total Profit</div>
      <div class="stat-value" style="color:var(--warn)">GH₵ 7.3K</div>
      <div class="stat-change up">↑ Interest earned</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:var(--danger-bg);color:var(--danger)">⏳</div>
      <div class="stat-label">Pending Loans</div>
      <div class="stat-value" style="color:var(--danger)">4</div>
      <div class="stat-change down">Needs approval</div>
    </div>
    <div class="stat-card col-span-2">
      <div class="stat-icon" style="background:var(--danger-bg);color:var(--danger)">⚠️</div>
      <div class="stat-label">Missed Contributions</div>
      <div class="stat-value" style="color:var(--danger)">7</div>
      <div class="stat-change down">↑ 3 since last week</div>
    </div>
  </div>

  <div class="grid-2" style="margin-bottom:16px">
    <div class="card">
      <div class="card-header">
        <div>
          <div class="card-title">Weekly Contributions</div>
          <div class="card-sub">Past 8 weeks</div>
        </div>
        <span class="badge badge-success"><span class="dot"></span> Live</span>
      </div>
      <div class="chart-container" style="height:200px">
        <canvas id="contribChart"></canvas>
      </div>
    </div>
    <div class="card">
      <div class="card-header">
        <div>
          <div class="card-title">Fund Breakdown</div>
          <div class="card-sub">Welfare · Loans · Penalties</div>
        </div>
      </div>
      <div class="chart-container" style="height:200px; position:relative;">
        <canvas id="donutChart"></canvas>
        <div class="donut-center">
          <div style="font-size:18px;font-weight:600;color:var(--accent)">GH₵ 48.2K</div>
          <div style="font-size:10px;color:var(--text3)">Total Pool</div>
        </div>
      </div>
    </div>
  </div>

  <div class="grid-2">
    <div class="card">
      <div class="card-header">
        <div class="card-title">Recent Transactions</div>
        <button class="btn btn-outline btn-sm" onclick="showPage('payments', document.querySelector('[onclick*=payments]'))">View all</button>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>User</th><th>Type</th><th>Amount</th><th>Status</th></tr></thead>
          <tbody>
            <tr><td><div class="user-row"><div class="user-avatar" style="background:#00b894">KA</div>Kofi Asante</div></td><td><span class="badge badge-info">Contribution</span></td><td class="mono">GH₵ 120</td><td><span class="badge badge-success">Paid</span></td></tr>
            <tr><td><div class="user-row"><div class="user-avatar" style="background:#6c5ce7">AM</div>Ama Mensah</div></td><td><span class="badge badge-purple">Loan</span></td><td class="mono">GH₵ 800</td><td><span class="badge badge-success">Active</span></td></tr>
            <tr><td><div class="user-row"><div class="user-avatar" style="background:#fd79a8">YO</div>Yaw Osei</div></td><td><span class="badge badge-info">Contribution</span></td><td class="mono">GH₵ 120</td><td><span class="badge badge-warn">Pending</span></td></tr>
            <tr><td><div class="user-row"><div class="user-avatar" style="background:#e17055">AB</div>Abena Boateng</div></td><td><span class="badge badge-danger">Penalty</span></td><td class="mono">GH₵ 25</td><td><span class="badge badge-success">Paid</span></td></tr>
            <tr><td><div class="user-row"><div class="user-avatar" style="background:#0984e3">FK</div>Fiifi Kumi</div></td><td><span class="badge badge-purple">Loan Repay</span></td><td class="mono">GH₵ 450</td><td><span class="badge badge-success">Paid</span></td></tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="card">
      <div class="card-header">
        <div class="card-title">Activity Feed</div>
      </div>
      <div class="timeline">
        <div class="timeline-item">
          <div class="timeline-dot" style="background:var(--success)"></div>
          <div class="timeline-time">Today 09:14</div>
          <div class="timeline-text">Kofi Asante made a weekly contribution of <strong>GH₵ 120</strong></div>
        </div>
        <div class="timeline-item">
          <div class="timeline-dot" style="background:var(--purple)"></div>
          <div class="timeline-time">Today 08:52</div>
          <div class="timeline-text">Loan #LN-2024 approved for Ama Mensah</div>
        </div>
        <div class="timeline-item">
          <div class="timeline-dot" style="background:var(--danger)"></div>
          <div class="timeline-time">Yesterday 17:00</div>
          <div class="timeline-text">3 users missed this week's contribution — reminder sent</div>
        </div>
        <div class="timeline-item">
          <div class="timeline-dot" style="background:var(--warn)"></div>
          <div class="timeline-time">Yesterday 14:30</div>
          <div class="timeline-text">Penalty of GH₵ 25 applied to Abena Boateng</div>
        </div>
        <div class="timeline-item">
          <div class="timeline-dot" style="background:var(--info)"></div>
          <div class="timeline-time">Mon 11:00</div>
          <div class="timeline-text">New user registered: Kwame Darko (Book #BK-241 assigned)</div>
        </div>
      </div>
    </div>
  </div>
</div>
