<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\rules;
use function Livewire\Volt\state;
use function Livewire\Volt\with;
use App\Models\User;
use App\Models\Book;
use App\Models\Contribution;

layout('layouts.admin');

state([
    'user_id' => '',
    'book_id' => '',
    'week_number' => 1,
    'contribution' => '',
    'welfare' => '',
    'penalty' => '0',
    'is_missed' => false,
]);

rules([
    'user_id' => 'required|exists:users,id',
    'book_id' => 'required|exists:books,id',
    'week_number' => 'required|integer|min:1|max:60',
    'contribution' => 'required|numeric|min:0',
    'welfare' => 'required|numeric|min:0',
    'penalty' => 'nullable|numeric|min:0',
    'is_missed' => 'boolean',
]);

with(function () {
    return [
        'usersList' => User::where('status', 'active')->orderBy('name')->get(),
        'booksList' => $this->user_id 
            ? Book::where('user_id', $this->user_id)->where('status', 'active')->get() 
            : [],
    ];
});

$updatedBookId = function ($val) {
    if (!$val) {
        $this->week_number = 1;
        $this->contribution = '';
        $this->welfare = '';
        return;
    }

    $book = Book::find($val);
    if ($book) {
        $this->week_number = (Contribution::where('book_id', $book->id)->max('week_number') ?? 0) + 1;
        $this->contribution = $book->contribution_amount;
        $this->welfare = \App\Models\Setting::val('welfare_amount', 10);
    }
};

$save = function () {
    $this->validate();

    // 1. Create the base contribution tracker
    $newContrib = Contribution::create([
        'user_id' => $this->user_id,
        'book_id' => $this->book_id,
        'week_number' => $this->week_number,
        'contribution' => $this->contribution,
        'welfare' => $this->welfare,
        'penalty' => $this->penalty ?? 0,
        'is_missed' => $this->is_missed,
    ]);

    // 2. If it is NOT missed, record the physical monetary transactions into the unified payments ledger
    if (!$this->is_missed) {
        $baseTxnId = 'TXN-' . rand(1000, 9999);
        
        if ($this->contribution > 0) {
            \App\Models\Payment::create([
                'user_id' => $this->user_id,
                'book_id' => $this->book_id,
                'payment_type' => 'contribution',
                'transaction_id' => $baseTxnId . '-C',
                'payment_method' => 'cash', // Defaulting to cash
                'amount_paid' => $this->contribution,
                'status' => 'completed',
                'paid_at' => now(),
            ]);
        }
        
        if ($this->welfare > 0) {
            \App\Models\Payment::create([
                'user_id' => $this->user_id,
                'book_id' => $this->book_id,
                'payment_type' => 'welfare',
                'transaction_id' => $baseTxnId . '-W',
                'payment_method' => 'cash',
                'amount_paid' => $this->welfare,
                'status' => 'completed',
                'paid_at' => now(),
            ]);
        }
        
        if ($this->penalty > 0) {
            \App\Models\Payment::create([
                'user_id' => $this->user_id,
                'book_id' => $this->book_id,
                'payment_type' => 'penalty',
                'transaction_id' => $baseTxnId . '-P',
                'payment_method' => 'cash',
                'amount_paid' => $this->penalty,
                'status' => 'completed',
                'paid_at' => now(),
            ]);
        }
    }

    return redirect()->route('contributions');
};

?>

<div class="page active" id="page-add-contribution">
  <div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-header">
      <div class="card-title">Record Contribution</div>
      <a href="{{ route('contributions') }}" class="btn btn-outline btn-sm" wire:navigate>Back to Contributions</a>
    </div>

    @if ($errors->has('book_id') && str_contains($errors->first('book_id'), 'unique'))
        <div class="alert-banner danger">This book already has a recorded contribution for Week {{ $week_number }}. Please choose a different week.</div>
    @endif

    <form wire:submit="save">
      <div class="grid-2" style="gap: 16px;">
        <!-- Left Column -->
        <div style="display:flex; flex-direction:column; gap:12px;">
          <div>
            <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">Select Member *</label>
            <select wire:model.live="user_id" class="filter-input" style="width:100%" required>
                <option value="">-- Choose User --</option>
                @foreach($usersList as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->member_id }})</option>
                @endforeach
            </select>
            @error('user_id') <span style="color:var(--danger);font-size:12px;">{{ $message }}</span> @enderror
          </div>

          <div>
            <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">Select Target Book *</label>
            <select wire:model.live="book_id" class="filter-input" style="width:100%" required @if(!$user_id) disabled @endif>
                <option value="">-- Choose Book --</option>
                @foreach($booksList as $book)
                    <option value="{{ $book->id }}">#{{ $book->book_number }}</option>
                @endforeach
            </select>
            @error('book_id') <span style="color:var(--danger);font-size:12px;">{{ $message }}</span> @enderror
          </div>

          <div>
            <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">Week Number *</label>
            <input type="number" wire:model="week_number" class="filter-input" style="width:100%" min="1" max="60" required>
            @error('week_number') <span style="color:var(--danger);font-size:12px;">{{ $message }}</span> @enderror
          </div>
          
          <div style="margin-top: 5px;">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input type="checkbox" wire:model="is_missed" style="transform: scale(1.2);">
                <span style="font-size: 14px; font-weight: 500; color: var(--danger)">Mark as Missed (Defaulter)</span>
            </label>
          </div>
        </div>

        <!-- Right Column -->
        <div style="display:flex; flex-direction:column; gap:12px;">
          <div>
            <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">Main Contribution (GH₵) *</label>
            <input type="number" step="0.01" wire:model="contribution" class="filter-input" style="width:100%" placeholder="e.g. 50" required>
            @error('contribution') <span style="color:var(--danger);font-size:12px;">{{ $message }}</span> @enderror
          </div>

          <div>
            <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">Welfare (GH₵) *</label>
            <input type="number" step="0.01" wire:model="welfare" class="filter-input" style="width:100%" placeholder="e.g. 10" required>
            @error('welfare') <span style="color:var(--danger);font-size:12px;">{{ $message }}</span> @enderror
          </div>

          <div>
            <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">Penalty (GH₵)</label>
            <input type="number" step="0.01" wire:model="penalty" class="filter-input" style="width:100%" placeholder="e.g. 5">
            @error('penalty') <span style="color:var(--danger);font-size:12px;">{{ $message }}</span> @enderror
          </div>
        </div>
      </div>
      
      <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border); text-align: right;">
        <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-size: var(--fs-md);">
           <span wire:loading.remove>Save Record</span>
           <span wire:loading>Processing...</span>
        </button>
      </div>
    </form>
  </div>
</div>
<script>
  document.getElementById('topbar-title').innerText = "Record Contribution";
  document.getElementById('topbar-sub').innerText = "Add a new weekly payment or mark a missed one";
</script>
