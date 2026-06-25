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
        $this->welfareToPay = \App\Models\Setting::val('welfare_amount', 10);
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

$initiatePaystack = function () {
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

    $amount = ($this->paymentType === 'contribution')
        ? ($this->amountToPay + $this->welfareToPay)
        : (float)$this->loanPaymentAmount;

    $metadata = [
        'user_id' => auth()->id(),
        'payment_type' => $this->paymentType,
    ];

    if ($this->paymentType === 'contribution') {
        $metadata['book_id'] = $this->selectedBookId;
        $metadata['next_week'] = $this->nextWeek;
        $metadata['amount_to_pay'] = $this->amountToPay;
        $metadata['welfare_to_pay'] = $this->welfareToPay;
    } else {
        $metadata['loan_id'] = $this->selectedLoanId;
        $metadata['loan_payment_amount'] = $this->loanPaymentAmount;
    }

    $this->dispatch('initiate-paystack', [
        'email' => auth()->user()->email,
        'amount' => $amount * 100, // Paystack amount is in kobo/pesewas
        'metadata' => $metadata,
        'key' => config('services.paystack.public_key'),
    ]);
};

$handleSuccess = function ($reference) {
    // This is called via livewire from frontend after paystack success
    // Verification is done on backend by Paystack controller, but we can refresh UI here
    $this->paymentType = 'contribution';
    $this->selectedBookId = '';
    $this->selectedLoanId = '';
    $this->amountToPay = 0;
    $this->welfareToPay = 0;
    $this->loanPaymentAmount = '';
    $this->nextWeek = 0;
    $this->outstandingBalance = 0;

    $this->dispatch('payment-verified', message: "Payment processed successfully! Ref: $reference");
};

with(function () {
    $userId = auth()->id();
    
    $activeBooks = Book::where('user_id', $userId)
        ->where('status', 'active')
        ->get();

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
      <div class="card-title">Online Payment</div>
      <div class="card-sub">Secure payment via Paystack</div>
    </div>

    <div class="card" style="background:var(--info-bg); border-color:var(--info); color:var(--text); padding:10px; margin-bottom:16px; font-size:11px;">
        🔒 <strong>Secure Payment:</strong> We use Paystack for all our transactions. Your card details are never stored on our servers.
    </div>

    <form wire:submit.prevent="initiatePaystack" style="display:flex; flex-direction:column; gap:14px;">
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
        <!-- Submit Simulation CTA -->
        <div style="margin-top:16px;">
          <button type="submit" class="btn btn-primary" style="width:100%; font-size:var(--fs-md); padding:10px;" x-bind:disabled="localProcessing">
             <span x-show="!localProcessing">💳 Pay GH₵ {{ $paymentType === 'contribution' ? number_format($amountToPay + $welfareToPay, 2) : number_format((float)$loanPaymentAmount, 2) }}</span>
             <span x-show="localProcessing" style="display:none;">⏳ Initiating Gateway...</span>
          </button>
        </div>
      @endif
    </form>
  </div>

  <script src="https://js.paystack.co/v1/inline.js"></script>
  <script>
    window.addEventListener('initiate-paystack', event => {
        const data = event.detail[0];
        let handler = PaystackPop.setup({
            key: data.key,
            email: data.email,
            amount: data.amount,
            currency: 'GHS',
            metadata: data.metadata,
            callback: function(response) {
                // Verify on server
                fetch('{{ route('payment.verify') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ reference: response.reference })
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status) {
                        @this.handleSuccess(response.reference);
                    } else {
                        Swal.fire({
                            background: '#161b22',
                            color: '#e6edf3',
                            icon: 'error',
                            title: 'Verification Failed',
                            text: res.message,
                            confirmButtonColor: '#f85149'
                        });
                    }
                });
            },
            onClose: function() {
                // Swal.fire({ icon: 'info', title: 'Transaction Cancelled' });
            }
        });
        handler.openIframe();
    });

    window.addEventListener('payment-verified', event => {
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
