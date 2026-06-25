<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\with;
use App\Models\Book;

layout('layouts.admin');

state([
    'search' => '',
    'statusFilter' => 'All Status'
]);

with(function () {
    $query = Book::with(['user', 'ledgers', 'loans']);

    if ($this->search) {
        $query->where(function($q) {
            $q->where('book_number', 'like', '%' . $this->search . '%')
              ->orWhereHas('user', function ($uq) {
                  $uq->where('name', 'like', '%' . $this->search . '%');
              });
        });
    }

    if ($this->statusFilter !== 'All Status') {
        $query->where('status', strtolower($this->statusFilter));
    }

    return [
        'books' => $query->latest()->get(),
        'totalBooks' => Book::count(),
        'activeBooks' => Book::where('status', 'active')->count(),
        'deactivatedBooks' => Book::where('status', 'deactivated')->count(),
        'unassignedBooks' => Book::where('status', 'unassigned')->orWhereNull('user_id')->count(),
    ];
});

$deactivateBook = function ($id) {
    Book::findOrFail($id)->update(['status' => 'deactivated']);
};

$activateBook = function ($id) {
    Book::findOrFail($id)->update(['status' => 'active']);
};

?>

<!-- ═══════════════════════════════════════════
     PAGE 3: BOOKS
═══════════════════════════════════════════ -->
<div class="page active" id="page-books">
  <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:16px">
    <div class="stat-card"><div class="stat-label">Total Books</div><div class="stat-value" style="color:var(--info)">{{ $totalBooks }}</div></div>
    <div class="stat-card"><div class="stat-label">Active</div><div class="stat-value" style="color:var(--success)">{{ $activeBooks }}</div></div>
    <div class="stat-card"><div class="stat-label">Deactivated</div><div class="stat-value" style="color:var(--danger)">{{ $deactivatedBooks }}</div></div>
    <div class="stat-card"><div class="stat-label">Unassigned</div><div class="stat-value" style="color:var(--warn)">{{ $unassignedBooks }}</div></div>
  </div>
  <div class="filters">
    <input wire:model.live="search" class="filter-input" type="text" placeholder="🔍  Search book or user…" style="width:200px">
    <select wire:model.live="statusFilter" class="filter-input">
        <option>All Status</option>
        <option>Active</option>
        <option>Deactivated</option>
        <option>Unassigned</option>
    </select>
    <a href="{{ route('books.add') }}" class="btn btn-primary btn-sm" wire:navigate>+ Assign Book</a>
  </div>
  <div class="card">
    <div class="table-wrap">
      <table id="bookTable">
        <thead><tr><th>Book ID</th><th>Owner</th><th>Contributions</th><th>Balance</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          @forelse ($books as $book)
          <tr>
            <td class="mono" style="color:var(--accent)">{{ $book->book_number }}</td>
            <td>
              @if($book->user)
              <div class="user-row">
                <div class="user-avatar" style="background:#00b894">{{ strtoupper(substr($book->user->name, 0, 2)) }}</div>
                {{ $book->user->name }}
              </div>
              @else
              <span class="badge badge-warn">Unassigned</span>
              @endif
            </td>
            <td class="mono">GH₵ {{ number_format($book->total_contributions ?? 0, 2) }}</td>
            <td class="mono" style="color:var(--success)">GH₵ {{ number_format($book->balance ?? 0, 2) }}</td>
            <td>
              @if (strtolower($book->status) === 'active')
                  <span class="badge badge-success">Active</span>
              @elseif (strtolower($book->status) === 'deactivated')
                  <span class="badge badge-danger">Deactivated</span>
              @elseif (strtolower($book->status) === 'unassigned')
                  <span class="badge badge-warn">Unassigned</span>
              @else
                  <span class="badge badge-neutral">{{ ucfirst($book->status) }}</span>
              @endif
            </td>
            <td>
              <div style="display:flex;gap:4px">
                <button class="btn btn-outline btn-xs" onclick="viewBook('{{ $book->book_number }}')">View</button>
                @if (strtolower($book->status) === 'active')
                  <button class="btn btn-danger btn-xs" wire:click="deactivateBook({{ $book->id }})" wire:confirm="Are you sure you want to deactivate this book?">Deactivate</button>
                @elseif (strtolower($book->status) === 'deactivated')
                  <button class="btn btn-primary btn-xs" wire:click="activateBook({{ $book->id }})" wire:confirm="Are you sure you want to activate this book?">Activate</button>
                @endif
              </div>
            </td>
          </tr>
          @empty
          <tr>
             <td colspan="6" style="text-align: center; color: var(--text3); padding: 20px;">No books found matching your filtering criteria.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
