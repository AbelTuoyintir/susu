<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\rules;
use function Livewire\Volt\state;
use function Livewire\Volt\with;
use App\Models\User;
use App\Models\Book;
use App\Models\Loan;

layout('layouts.admin');

state([
    'user_id' => '',
    'book_id' => '',
    'amount' => '',
    'interest' => '',
    'due_date' => date('Y-m-d', strtotime('+30 days')),
]);

rules([
    'user_id' => 'required|exists:users,id',
    'book_id' => 'required|exists:books,id',
    'amount' => 'required|numeric|min:1',
    'interest' => 'required|numeric|min:0',
    'due_date' => 'required|date|after:today',
]);

with(function () {
    return [
        'usersList' => User::where('status', 'active')->orderBy('name')->get(),
        // Get books that actually belong to the selected user_id
        'booksList' => $this->user_id 
            ? Book::where('user_id', $this->user_id)->where('status', 'active')->get() 
            : [],
    ];
});

$save = function () {
    $this->validate();

    Loan::create([
        'user_id' => $this->user_id,
        'book_id' => $this->book_id,
        'amount' => $this->amount,
        'interest' => $this->interest,
        'due_date' => $this->due_date,
        'status' => 'pending', // Starts as pending to be approved by admin
    ]);

    return redirect()->route('loans');
};

?>

<div class="page active" id="page-add-loan">
  <div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-header">
      <div class="card-title">Request/Issue New Loan</div>
      <a href="{{ route('loans') }}" class="btn btn-outline btn-sm" wire:navigate>Back to Loans</a>
    </div>

    <form wire:submit="save">
      <div class="grid-2" style="gap: 16px;">
        <!-- Left Column -->
        <div style="display:flex; flex-direction:column; gap:12px;">
          <div>
            <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">Select Borrower *</label>
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
            <select wire:model="book_id" class="filter-input" style="width:100%" required @if(!$user_id) disabled @endif>
                <option value="">-- Choose Book --</option>
                @foreach($booksList as $book)
                    <option value="{{ $book->id }}">#{{ $book->book_number }}</option>
                @endforeach
            </select>
            @error('book_id') <span style="color:var(--danger);font-size:12px;">{{ $message }}</span> @enderror
          </div>

          <div>
            <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">Principal Amount (GH₵) *</label>
            <input type="number" step="0.01" wire:model.live="amount" class="filter-input" style="width:100%" placeholder="e.g. 500" required>
            @error('amount') <span style="color:var(--danger);font-size:12px;">{{ $message }}</span> @enderror
          </div>
        </div>

        <!-- Right Column -->
        <div style="display:flex; flex-direction:column; gap:12px;">
          <div>
            <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">Interest Applied (GH₵) *</label>
            <input type="number" step="0.01" wire:model="interest" class="filter-input" style="width:100%" placeholder="e.g. 50" required>
            @error('interest') <span style="color:var(--danger);font-size:12px;">{{ $message }}</span> @enderror
          </div>

          <div>
            <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">Due Date *</label>
            <input type="date" wire:model="due_date" class="filter-input" style="width:100%" required>
            @error('due_date') <span style="color:var(--danger);font-size:12px;">{{ $message }}</span> @enderror
          </div>
          
          <div style="margin-top:auto; padding: 10px; background:var(--bg); border:1px solid var(--border); border-radius:6px; font-size:13px;">
             <strong>Summary:</strong> Total repayment will be <span class="mono" style="color:var(--danger)">GH₵ {{ number_format(((float)$amount) + ((float)$interest), 2) }}</span>. The loan request will be added as "Pending".
          </div>
        </div>
      </div>
      
      <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border); text-align: right;">
        <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-size: var(--fs-md);">
           <span wire:loading.remove>Submit Loan Request</span>
           <span wire:loading>Processing...</span>
        </button>
      </div>
    </form>
  </div>
</div>
<script>
  document.getElementById('topbar-title').innerText = "Issue/Request Loan";
  document.getElementById('topbar-sub').innerText = "Create a new loan application in the system";
</script>
