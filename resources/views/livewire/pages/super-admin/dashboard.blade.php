<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\with;
use App\Models\Tenant;

layout('layouts.admin');

state([
    'editingTenantId' => null,
    'newPlan' => '',
]);

with(function () {
    $tenants = Tenant::all();

    $totalTenants = $tenants->count();
    $freePlanCount = $tenants->where('plan', 'free')->count();
    $premiumPlanCount = $tenants->where('plan', 'premium')->count();
    $enterprisePlanCount = $tenants->where('plan', 'enterprise')->count();

    return [
        'tenants' => $tenants,
        'totalTenants' => $totalTenants,
        'freePlanCount' => $freePlanCount,
        'premiumPlanCount' => $premiumPlanCount,
        'enterprisePlanCount' => $enterprisePlanCount,
    ];
});

$toggleStatus = function ($tenantId) {
    $tenant = Tenant::findOrFail($tenantId);
    $tenant->status = $tenant->status === 'active' ? 'inactive' : 'active';
    $tenant->save();

    session()->flash('success', 'Tenant status updated successfully.');
};

$editPlan = function ($tenantId) {
    $tenant = Tenant::findOrFail($tenantId);
    $this->editingTenantId = $tenant->id;
    $this->newPlan = $tenant->plan;
};

$savePlan = function () {
    $tenant = Tenant::findOrFail($this->editingTenantId);
    $tenant->plan = $this->newPlan;
    $tenant->save();

    $this->editingTenantId = null;
    $this->newPlan = '';

    session()->flash('success', 'Tenant plan updated successfully.');
};

$cancelEditPlan = function () {
    $this->editingTenantId = null;
    $this->newPlan = '';
};

?>

<div class="page active" id="page-super-admin-dashboard">
  <div style="margin-bottom: 24px;">
    <div class="page-title">Super Admin Control Panel</div>
    <div class="page-subtitle">Global SaaS Metrics & Multi-Tenant Management</div>
  </div>

  <!-- Messages -->
  @if (session()->has('success'))
    <div class="card" style="background:var(--success-bg); border-color:var(--success); color:var(--success); padding: 12px; margin-bottom:16px; font-weight:500;">
      ✔️ {{ session('success') }}
    </div>
  @endif

  <!-- SaaS Stats Summary -->
  <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 24px;">
    <div class="stat-card">
      <div class="stat-icon" style="background:var(--accent-dim);color:var(--accent)">🏢</div>
      <div class="stat-label">Total Organizations</div>
      <div class="stat-value" style="color:var(--accent)">{{ $totalTenants }}</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:var(--info-bg);color:var(--info)">🆓</div>
      <div class="stat-label">Free Plan Tenants</div>
      <div class="stat-value" style="color:var(--info)">{{ $freePlanCount }}</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:var(--success-bg);color:var(--success)">🌟</div>
      <div class="stat-label">Premium Plan Tenants</div>
      <div class="stat-value" style="color:var(--success)">{{ $premiumPlanCount }}</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:var(--purple-bg);color:var(--purple)">🚀</div>
      <div class="stat-label">Enterprise Tenants</div>
      <div class="stat-value" style="color:var(--purple)">{{ $enterprisePlanCount }}</div>
    </div>
  </div>

  <!-- Tenants List -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">Registered Tenants / Organizations</div>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Name & Slug</th>
            <th>Billing Plan</th>
            <th>Status</th>
            <th>Usage Stats (Users / Books / Loans)</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($tenants as $tenant)
            @php
              $usersCount = $tenant->getUsage('users');
              $usersLimit = $tenant->getLimit('users');
              $booksCount = $tenant->getUsage('books');
              $booksLimit = $tenant->getLimit('books');
              $loansCount = $tenant->getUsage('loans');
              $loansLimit = $tenant->getLimit('loans');
            @endphp
            <tr>
              <td class="mono" style="color:var(--text3)">#{{ sprintf('TNT-%03d', $tenant->id) }}</td>
              <td>
                <div style="font-weight: 600;">{{ $tenant->name }}</div>
                <div style="font-size: 11px; color: var(--text3); font-family: monospace;">slug: {{ $tenant->slug }}</div>
              </td>
              <td>
                @if($editingTenantId === $tenant->id)
                  <div style="display: flex; gap: 6px; align-items: center;">
                    <select wire:model="newPlan" class="filter-input" style="padding: 4px 8px; font-size: 12px;">
                      <option value="free">Free</option>
                      <option value="premium">Premium</option>
                      <option value="enterprise">Enterprise</option>
                    </select>
                    <button wire:click="savePlan" class="btn btn-primary btn-xs">Save</button>
                    <button wire:click="cancelEditPlan" class="btn btn-outline btn-xs">Cancel</button>
                  </div>
                @else
                  <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="badge {{ $tenant->plan === 'enterprise' ? 'badge-purple' : ($tenant->plan === 'premium' ? 'badge-info' : 'badge-neutral') }}">
                      {{ ucfirst($tenant->plan) }}
                    </span>
                    <button wire:click="editPlan({{ $tenant->id }})" class="btn btn-outline btn-xs" style="padding: 2px 6px; font-size: 10px;">Edit</button>
                  </div>
                @endif
              </td>
              <td>
                @if($tenant->status === 'active')
                  <span class="badge badge-success"><span class="dot"></span> Active</span>
                @else
                  <span class="badge badge-danger"><span class="dot"></span> Suspended</span>
                @endif
              </td>
              <td style="font-size: 12px;">
                <div style="display: flex; flex-direction: column; gap: 2px;">
                  <div>👥 Users: <strong>{{ $usersCount }}</strong> / {{ $usersLimit == 999999 ? '∞' : $usersLimit }}</div>
                  <div>📒 Books: <strong>{{ $booksCount }}</strong> / {{ $booksLimit == 999999 ? '∞' : $booksLimit }}</div>
                  <div>🏦 Loans: <strong>{{ $loansCount }}</strong> / {{ $loansLimit == 999999 ? '∞' : $loansLimit }}</div>
                </div>
              </td>
              <td style="text-align: right;">
                <div style="display: flex; justify-content: flex-end; gap: 6px;">
                  @if($tenant->status === 'active')
                    <button wire:click="toggleStatus({{ $tenant->id }})" class="btn btn-danger btn-xs" wire:confirm="Suspend this organization's access?">
                      Suspend
                    </button>
                  @else
                    <button wire:click="toggleStatus({{ $tenant->id }})" class="btn btn-success btn-xs">
                      Activate
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" style="text-align: center; color: var(--text3); padding: 24px;">No organizations registered in the system.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
