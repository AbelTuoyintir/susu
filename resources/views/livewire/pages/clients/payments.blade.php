<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\with;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Payment;
use App\Models\Contribution;
use App\Models\LoanPayment;

layout('layouts.client');

state([
    'paymentType' => 'contribution',
    'selectedBookId' => '',
    'selectedLoanId' => '',
    'amountToPay' => 0,
    'welfareToPay' => 0,
    'loanPaymentAmount' => '',
    'paymentMethod' => 'mobile_money',
    'mobileNumber' => '',
    'network' => 'MTN',
    'cardNumber' => '',
    'cardExpiry' => '',
    'cardCvv' => '',
    'nextWeek' => 0,
    'outstandingBalance' => 0,
    'processing' => false,
]);

$updatedPaymentType = function () {
    $this->selectedBookId = '';
    $this->selectedLoanId = '';
    $this->amountToPay = 0;
    $this->welfareToPay = 0;
    $this->loanPaymentAmount = '';
    $this->nextWeek = 0;
    $this->outstandingBalance = 0;
};

$updatedSelectedBookId = function ($val) {
    if (!$val) {
        $this->nextWeek = 0;
        $this->amountToPay = 0;
        $this->welfareToPay = 0;
        return;
    }
    
    $book = Book::find($val);
    if ($book) {
        $this->nextWeek = (Contribution::where('book_id', $book->id)->max('week_number') ?? 0) + 1;
        $this->amountToPay = $book->contribution_amount;
        $this->welfareToPay = round($book->contribution_amount * 0.10, 2);
    }
};

$updatedSelectedLoanId = function ($val) {
    if (!$val) {
        $this->outstandingBalance = 0;
        $this->loanPaymentAmount = '';
        return;
    }
    
    $loan = Loan::find($val);
    if ($loan) {
        $totalOwed = $loan->amount + $loan->interest;
        $this->outstandingBalance = max(0, $totalOwed - $loan->amount_repaid);
        $this->loanPaymentAmount = $this->outstandingBalance;
    }
};

$submitPayment = function () {
    // Basic Val
    if ($this->paymentType === 'contribution') {
        $this->validate([
            'selectedBookId' => 'required|exists:books,id',
        ]);
    } else {
        $this->validate([
            'selectedLoanId' => 'required|exists:loans,id',
            'loanPaymentAmount' => 'required|numeric|min:1|max:' . $this->outstandingBalance,
        ]);
    }

    if ($this->paymentMethod === 'mobile_money') {
        $this->validate([
            'mobileNumber' => 'required|string|min:9',
            'network' => 'required|string',
        ]);
    } else {
        $this->validate([
            'cardNumber' => 'required|string|min:16',
            'cardExpiry' => 'required|string|min:5',
            'cardCvv' => 'required|string|min:3|max:4',
        ]);
    }

    $this->processing = true;

    // Simulate Network Delay (handled cleanly in front-end state, but we process data now)
    $userId = auth()->id();
    $baseTxnId = 'TXN-' . strtoupper(Str::random(8));

    if ($this->paymentType === 'contribution') {
        $book = Book::find($this->selectedBookId);
        
        // 1. Create Contribution
        Contribution::create([
            'user_id' => $userId,
            'book_id' => $book->id,
            'week_number' => $this->nextWeek,
            'contribution' => $this->amountToPay,
            'welfare' => $this->welfareToPay,
            'penalty' => 0,
            'is_missed' => false,
        ]);

        // 2. Create Contribution Payment
        Payment::create([
            'user_id' => $userId,
            'book_id' => $book->id,
            'payment_type' => 'contribution',
            'transaction_id' => $baseTxnId . '-C',
            'payment_method' => $this->paymentMethod,
            'amount_paid' => $this->amountToPay,
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        // 3. Create Welfare Payment
        Payment::create([
            'user_id' => $userId,
            'book_id' => $book->id,
            'payment_type' => 'welfare',
            'transaction_id' => $baseTxnId . '-W',
            'payment_method' => $this->paymentMethod,
            'amount_paid' => $this->welfareToPay,
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        $msg = "Successfully deposited weekly contribution of GH₵ " . number_format($this->amountToPay, 2) . " and welfare fee of GH₵ " . number_format($this->welfareToPay, 2) . " to Book #" . $book->book_number . " for Week " . $this->nextWeek . ".";
    } else {
        $loan = Loan::find($this->selectedLoanId);
        $amountPaid = (float) $this->loanPaymentAmount;

        // 1. Create Loan Payment details
        LoanPayment::create([
            'loan_id' => $loan->id,
            'amount_paid' => $amountPaid,
        ]);

        // 2. Create General Payment Audit Record
        Payment::create([
            'user_id' => $userId,
            'loan_id' => $loan->id,
            'payment_type' => 'loan_repayment',
            'transaction_id' => $baseTxnId . '-L',
            'payment_method' => $this->paymentMethod,
            'amount_paid' => $amountPaid,
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        // 3. Refresh loan and check if fully settled
        $loan->refresh();
        $totalOwed = $loan->amount + $loan->interest;
        if ($loan->amount_repaid >= $totalOwed) {
            $loan->update(['status' => 'paid']);
        }

        $msg = "Successfully paid GH₵ " . number_format($amountPaid, 2) . " towards Loan #" . sprintf('LN-%04d', $loan->id) . ".";
    }

    $this->processing = false;

    // Reset Form inputs
    $this->selectedBookId = '';
    $this->selectedLoanId = '';
    $this->amountToPay = 0;
    $this->welfareToPay = 0;
    $this->loanPaymentAmount = '';
    $this->nextWeek = 0;
    $this->outstandingBalance = 0;
    $this->mobileNumber = '';
    $this->cardNumber = '';
    $this->cardExpiry = '';
    $this->cardCvv = '';

    // Dispatch custom event for browser SweetAlert
    $this->dispatch('payment-success', message: $msg);
};

with(function () {
    $userId = auth()->id();
    
    // User active books
    $activeBooks = Book::where('user_id', $userId)
        ->where('status', 'active')
        ->get();

    // User outstanding loans
    $activeLoans = Loan::where('user_id', $userId)
        ->whereIn('status', ['active', 'defaulted'])
        ->get();

    return [
        'activeBooks' => $activeBooks,
        'activeLoans' => $activeLoans,
    ];
});

?>

<div class="page active" id="page-client-payments" x-data="{ localProcessing: false }">
  <div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
      <div class="card-title">Online Payment Simulator</div>
      <div class="card-sub">Mock sandbox environment to simulate online payments</div>
    </div>

    <!-- Alert for Sandbox Mode -->
    <div class="card" style="background:var(--info-bg); border-color:var(--info); color:var(--text); padding:10px; margin-bottom:16px; font-size:11px;">
        💡 <strong>Sandbox Simulator:</strong> This form processes instant mock transactions. No real funds are transferred.
    </div>

    <form wire:submit="submitPayment" x-on:submit="localProcessing = true; setTimeout(() => { localProcessing = false; }, 2000)" style="display:flex; flex-direction:column; gap:14px;">
      <!-- Toggle Payment Type -->
      <div class="form-group">
        <label class="form-label">Payment Category</label>
        <div style="display:flex; gap:8px;">
            <label class="btn btn-outline" style="flex:1; cursor:pointer; text-align:center; padding: 10px; {{ $paymentType === 'contribution' ? 'border-color:var(--accent); color:var(--accent); background:var(--accent-dim);' : '' }}">
                <input type="radio" name="paymentType" value="contribution" wire:model.live="paymentType" style="display:none;">
                Susu Contribution
            </label>
            <label class="btn btn-outline" style="flex:1; cursor:pointer; text-align:center; padding: 10px; {{ $paymentType === 'loan' ? 'border-color:var(--accent); color:var(--accent); background:var(--accent-dim);' : '' }}">
                <input type="radio" name="paymentType" value="loan" wire:model.live="paymentType" style="display:none;">
                Loan Repayment
            </label>
        </div>
      </div>

      @if($paymentType === 'contribution')
        <!-- Contribution Option Fields -->
        <div class="form-group">
            <label class="form-label">Select Target Savings Book</label>
            <select wire:model.live="selectedBookId" class="filter-input" style="width:100%" required>
                <option value="">-- Choose Savings Book --</option>
                @foreach($activeBooks as $book)
                    <option value="{{ $book->id }}">Book #{{ $book->book_number }} (GH₵ {{ number_format($book->contribution_amount) }}/wk)</option>
                @endforeach
            </select>
        </div>

        @if($selectedBookId)
          <div style="background:var(--bg3); padding:12px; border-radius:var(--r); border:1px solid var(--border); margin-bottom:4px;">
             <div style="font-weight:600; font-size:var(--fs-md); margin-bottom:8px; border-bottom:1px solid var(--border); padding-bottom:4px; color:var(--accent)">Payment Details (Week {{ $nextWeek }})</div>
             <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                <span style="color:var(--text2)">Weekly Contribution:</span>
                <span class="mono">GH₵ {{ number_format($amountToPay, 2) }}</span>
             </div>
             <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                <span style="color:var(--text2)">Weekly Welfare Fee:</span>
                <span class="mono">GH₵ {{ number_format($welfareToPay, 2) }}</span>
             </div>
             <div style="display:flex; justify-content:space-between; font-weight:600; margin-top:8px; border-top:1px solid var(--border); padding-top:6px; color:var(--success)">
                <span>Total Amount Due:</span>
                <span class="mono">GH₵ {{ number_format($amountToPay + $welfareToPay, 2) }}</span>
             </div>
          </div>
        @endif

      @else
        <!-- Loan Repayment Option Fields -->
        <div class="form-group">
            <label class="form-label">Select Active Loan</label>
            <select wire:model.live="selectedLoanId" class="filter-input" style="width:100%" required>
                <option value="">-- Choose Active Loan --</option>
                @foreach($activeLoans as $loan)
                    <option value="{{ $loan->id }}">Loan #{{ sprintf('LN-%04d', $loan->id) }} (Owed: GH₵ {{ number_format(($loan->amount + $loan->interest) - $loan->amount_repaid) }})</option>
                @endforeach
            </select>
        </div>

        @if($selectedLoanId)
          <div class="form-group">
             <label class="form-label">Enter Repayment Amount (GH₵) *</label>
             <input type="number" step="0.01" max="{{ $outstandingBalance }}" wire:model.live="loanPaymentAmount" class="form-input" required placeholder="Enter amount to pay">
             <span style="font-size:10px; color:var(--text3); margin-top:4px;">Outstanding Loan Balance: <strong>GH₵ {{ number_format($outstandingBalance, 2) }}</strong></span>
             @error('loanPaymentAmount') <span style="color:var(--danger); font-size:11px; display:block; margin-top:4px;">{{ $message }}</span> @enderror
          </div>
        @endif
      @endif

      @if(($paymentType === 'contribution' && $selectedBookId) || ($paymentType === 'loan' && $selectedLoanId))
        <!-- Payment Methods Details Section -->
        <div style="border-top: 1px solid var(--border); padding-top:14px; margin-top:4px;">
            <label class="form-label">Choose Payment Method</label>
            <div style="display:flex; gap:8px; margin-top:6px;">
                <label class="btn btn-outline" style="flex:1; cursor:pointer; text-align:center; {{ $paymentMethod === 'mobile_money' ? 'border-color:var(--accent); color:var(--accent); background:var(--accent-dim);' : '' }}">
                    <input type="radio" name="paymentMethod" value="mobile_money" wire:model.live="paymentMethod" style="display:none;">
                    Mobile Money
                </label>
                <label class="btn btn-outline" style="flex:1; cursor:pointer; text-align:center; {{ $paymentMethod === 'card' ? 'border-color:var(--accent); color:var(--accent); background:var(--accent-dim);' : '' }}">
                    <input type="radio" name="paymentMethod" value="card" wire:model.live="paymentMethod" style="display:none;">
                    Credit/Debit Card
                </label>
            </div>
        </div>

        @if($paymentMethod === 'mobile_money')
          <!-- MoMo Form -->
          <div class="grid-2" style="margin-top:12px; gap: 12px;">
             <div class="form-group">
                <label class="form-label">Network Provider</label>
                <select wire:model="network" class="filter-input" style="width:100%" required>
                    <option>MTN MoMo</option>
                    <option>Telecel Cash</option>
                    <option>AT Money</option>
                </select>
             </div>
             <div class="form-group">
                <label class="form-label">Mobile Number *</label>
                <input type="tel" wire:model="mobileNumber" class="form-input" placeholder="e.g. 0241234567" required>
                @error('mobileNumber') <span style="color:var(--danger); font-size:11px;">{{ $message }}</span> @enderror
             </div>
          </div>
        @else
          <!-- Card Form -->
          <div style="display:flex; flex-direction:column; gap:12px; margin-top:12px;">
             <div class="form-group">
                <label class="form-label">Card Number *</label>
                <input type="text" maxlength="19" wire:model="cardNumber" class="form-input" placeholder="4111 2222 3333 4444" required>
                @error('cardNumber') <span style="color:var(--danger); font-size:11px;">{{ $message }}</span> @enderror
             </div>
             <div class="grid-2" style="gap:12px;">
                <div class="form-group">
                    <label class="form-label">Expiry Date *</label>
                    <input type="text" maxlength="5" wire:model="cardExpiry" class="form-input" placeholder="MM/YY" required>
                    @error('cardExpiry') <span style="color:var(--danger); font-size:11px;">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">CVV Code *</label>
                    <input type="password" maxlength="4" wire:model="cardCvv" class="form-input" placeholder="•••" required>
                    @error('cardCvv') <span style="color:var(--danger); font-size:11px;">{{ $message }}</span> @enderror
                </div>
             </div>
          </div>
        @endif

        <!-- Submit Simulation CTA -->
        <div style="margin-top:16px;">
          <button type="submit" class="btn btn-primary" style="width:100%; font-size:var(--fs-md); padding:10px;" x-bind:disabled="localProcessing">
             <span x-show="!localProcessing">🔒 Securely Pay GH₵ {{ $paymentType === 'contribution' ? number_format($amountToPay + $welfareToPay, 2) : number_format((float)$loanPaymentAmount, 2) }}</span>
             <span x-show="localProcessing" style="display:none;">⏳ Simulating Gateway Authorization...</span>
          </button>
        </div>
      @endif
    </form>
  </div>

  <script>
    window.addEventListener('payment-success', event => {
        Swal.fire({
            background: '#161b22',
            color: '#e6edf3',
            icon: 'success',
            iconColor: '#00d4a8',
            title: 'Payment Successful!',
            html: `<span style="font-size:12.5px; line-height:1.4">${event.detail.message}</span>`,
            confirmButtonColor: '#00d4a8'
        });
    });
  </script>
</div>
