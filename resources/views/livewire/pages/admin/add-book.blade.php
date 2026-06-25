<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\rules;
use function Livewire\Volt\state;
use function Livewire\Volt\with;
use App\Models\User;
use App\Models\Book;
use Illuminate\Support\Str;

layout('layouts.admin');

state([
    'user_id' => '',
    'contribution_amount' => '',
    'duration_weeks' => 55,
    'start_date' => date('Y-m-d'),
    'status' => 'active',
]);

rules([
    'user_id' => 'nullable|exists:users,id',
    'contribution_amount' => 'required|numeric|min:1',
    'duration_weeks' => 'required|integer|min:1',
    'start_date' => 'required|date',
    'status' => 'required|in:active,deactivated,unassigned',
]);

with(function () {
    return [
        'usersList' => User::where('status', 'active')->orderBy('name')->get()
    ];
});

$save = function () {
    $this->validate();

    // Generate unique Book Number
    do {
        $bookNumber = 'BK-' . strtoupper(Str::random(5));
    } while (Book::where('book_number', $bookNumber)->exists());

    Book::create([
        'user_id' => $this->user_id ? $this->user_id : null,
        'book_number' => $bookNumber,
        'contribution_amount' => $this->contribution_amount,
        'duration_weeks' => $this->duration_weeks,
        'start_date' => $this->start_date,
        // Override status to unassigned if no user_id is provided
        'status' => $this->user_id ? $this->status : 'unassigned',
    ]);

    return redirect()->route('books');
};

?>

<div class="page active" id="page-add-book">
  <div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-header">
      <div class="card-title">Add/Assign New Book</div>
      <a href="{{ route('books') }}" class="btn btn-outline btn-sm" wire:navigate>Back to Books</a>
    </div>

    <form wire:submit="save">
      <div class="grid-2" style="gap: 16px;">
        <!-- Left Column -->
        <div style="display:flex; flex-direction:column; gap:12px;">
          <div>
            <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">Assign To User</label>
            <select wire:model="user_id" class="filter-input" style="width:100%">
                <option value="">-- Leave Unassigned --</option>
                @foreach($usersList as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->member_id }})</option>
                @endforeach
            </select>
            @error('user_id') <span style="color:var(--danger);font-size:12px;">{{ $message }}</span> @enderror
          </div>

          <div>
            <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">Weekly Contribution Amt (GH₵) *</label>
            <input type="number" step="0.01" wire:model="contribution_amount" class="filter-input" style="width:100%" placeholder="e.g. 50">
            @error('contribution_amount') <span style="color:var(--danger);font-size:12px;">{{ $message }}</span> @enderror
          </div>

          <div>
            <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">Start Date *</label>
            <input type="date" wire:model="start_date" class="filter-input" style="width:100%">
            @error('start_date') <span style="color:var(--danger);font-size:12px;">{{ $message }}</span> @enderror
          </div>
        </div>

        <!-- Right Column -->
        <div style="display:flex; flex-direction:column; gap:12px;">
          <div>
            <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">Duration (Weeks) *</label>
            <input type="number" wire:model="duration_weeks" class="filter-input" style="width:100%">
            @error('duration_weeks') <span style="color:var(--danger);font-size:12px;">{{ $message }}</span> @enderror
          </div>

          <div>
            <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">Initial Status</label>
            <select wire:model="status" class="filter-input" style="width:100%">
                <option value="active">Active</option>
                <option value="deactivated">Deactivated</option>
                <option value="unassigned">Unassigned</option>
            </select>
            <div style="font-size: 11px; color: var(--text3); margin-top: 4px;">If no user is selected, this will automatically default to Unassigned.</div>
            @error('status') <span style="color:var(--danger);font-size:12px;">{{ $message }}</span> @enderror
          </div>
        </div>
      </div>
      
      <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border); text-align: right;">
        <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-size: var(--fs-md);">
           <span wire:loading.remove>Save Book</span>
           <span wire:loading>Processing...</span>
        </button>
      </div>
    </form>
  </div>
</div>
<script>
  document.getElementById('topbar-title').innerText = "Add Book";
  document.getElementById('topbar-sub').innerText = "Create or assign a new passbook";
</script>
