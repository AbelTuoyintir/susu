<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\with;
use App\Models\User;

layout('layouts.admin');

state([
    'search' => '',
    'statusFilter' => 'All Status'
]);

with(function () {
    $query = User::with(['books', 'contributions', 'loans']);

    if ($this->search) {
        $query->where(function ($q) {
            $q->where('name', 'like', '%' . $this->search . '%')
              ->orWhere('member_id', 'like', '%' . $this->search . '%')
              ->orWhere('phone', 'like', '%' . $this->search . '%');
        });
    }

    if ($this->statusFilter !== 'All Status') {
        $query->where('status', strtolower($this->statusFilter));
    }

    return [
        'users' => $query->latest()->get()
    ];
});

$blockUser = function ($id) {
    User::findOrFail($id)->update(['status' => 'blocked']);
};

$unblockUser = function ($id) {
    User::findOrFail($id)->update(['status' => 'active']);
};

?>

<!-- ═══════════════════════════════════════════
     PAGE 2: USERS
═══════════════════════════════════════════ -->
<div class="page active" id="page-users">
  <div class="filters">
    <input wire:model.live="search" class="filter-input" type="text" placeholder="🔍  Search user…" style="width:200px">
    <select wire:model.live="statusFilter" class="filter-input">
      <option>All Status</option><option>Active</option><option>Blocked</option>
    </select>
    <a href="{{ route('users.add') }}" class="btn btn-primary btn-sm" wire:navigate>+ Add User</a>
  </div>
  <div class="card">
    <div class="table-wrap">
      <table id="userTable">
        <thead><tr><th>#</th><th>User</th><th>Phone</th><th>Books</th><th>Contributions</th><th>Loans</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          @forelse ($users as $user)
          <tr>
            <td class="mono" style="color:var(--text3)">{{ $user->member_id }}</td>
            <td>
              <div class="user-row">
                <div class="user-avatar" style="background:#00b894">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
                <div>
                  <div>{{ $user->name }}</div>
                  <div style="font-size:10px;color:var(--text3)">{{ $user->email }}</div>
                </div>
              </div>
            </td>
            <td>{{ $user->phone }}</td>
            <td><span class="badge badge-info">{{ $user->books->count() }} Books</span></td>
            <td class="mono">GH₵ {{ number_format($user->contributions->sum('amount') ?? 0, 2) }}</td>
            <td class="mono">GH₵ {{ number_format($user->loans->sum('amount') ?? 0, 2) }}</td>
            <td>
              @if ($user->status === 'active')
                <span class="badge badge-success"><span class="dot"></span> Active</span>
              @elseif ($user->status === 'blocked')
                <span class="badge badge-danger"><span class="dot"></span> Blocked</span>
              @else
                <span class="badge badge-neutral">{{ ucfirst($user->status) }}</span>
              @endif
            </td>
            <td>
              <div style="display:flex;gap:4px">
                <button class="btn btn-outline btn-xs" onclick="viewUser('{{ $user->name }}')">View</button>
                @if ($user->status === 'active')
                  <button class="btn btn-danger btn-xs" wire:click="blockUser({{ $user->id }})" wire:confirm="Block {{ $user->name }}?">Block</button>
                @else
                  <button class="btn btn-primary btn-xs" wire:click="unblockUser({{ $user->id }})">Unblock</button>
                @endif
              </div>
            </td>
          </tr> 
          @empty
          <tr>
            <td colspan="8" style="text-align: center; color: var(--text3); padding: 20px;">No users found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
