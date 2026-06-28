<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\with;
use function Livewire\Volt\mount;
use App\Models\Setting;

layout('layouts.admin');

state([
    'base_contribution' => '',
    'welfare_amount' => '',
    'penalty_amount' => '',
    'loan_interest_rate' => '',
    'allow_loan_extensions' => false,
    'auto_apply_penalties' => false,
]);

mount(function () {
    $this->base_contribution = Setting::val('base_contribution', '120');
    $this->welfare_amount = Setting::val('welfare_amount', '10');
    $this->penalty_amount = Setting::val('penalty_amount', '6');
    $this->loan_interest_rate = Setting::val('loan_interest_rate', '10');
    $this->allow_loan_extensions = (bool)Setting::val('allow_loan_extensions', false);
    $this->auto_apply_penalties = (bool)Setting::val('auto_apply_penalties', false);
});

$saveSettings = function () {
    Setting::updateOrCreate(['key' => 'base_contribution'], ['value' => $this->base_contribution, 'description' => 'Weekly base contribution GH₵']);
    Setting::updateOrCreate(['key' => 'welfare_amount'], ['value' => $this->welfare_amount, 'description' => 'Weekly welfare deduction GH₵']);
    Setting::updateOrCreate(['key' => 'penalty_amount'], ['value' => $this->penalty_amount, 'description' => 'Penalty charged for late payments']);
    Setting::updateOrCreate(['key' => 'loan_interest_rate'], ['value' => $this->loan_interest_rate, 'description' => 'Default percentage interest rate on loans']);

    session()->flash('success', 'Core Application Settings Saved!');
};

$toggleExtension = function () {
    $this->allow_loan_extensions = !$this->allow_loan_extensions;
    Setting::updateOrCreate(['key' => 'allow_loan_extensions'], ['value' => $this->allow_loan_extensions]);
    session()->flash('success', 'Loan extension policy updated!');
};

$togglePenalties = function () {
    $this->auto_apply_penalties = !$this->auto_apply_penalties;
    Setting::updateOrCreate(['key' => 'auto_apply_penalties'], ['value' => $this->auto_apply_penalties]);
    session()->flash('success', 'Penalty policy updated!');
};

$changePassword = function () {
    // For MVP Simulation
    session()->flash('success', 'Security Settings (Password) Updated!');
};

?>

<div class="page active" id="page-settings">
  
  @if(session()->has('success'))
    <div style="background:var(--success); color:#fff; padding:12px 16px; border-radius:6px; margin-bottom:16px; font-size:13px; font-weight:500;">
        ✓ {{ session('success') }}
    </div>
  @endif

  <div class="grid-2">
    <!-- FINANCIAL SETTINGS -->
    <div class="card">
      <div class="card-header"><div class="card-title">Financial Directives</div></div>
      <form wire:submit="saveSettings">
          <div style="display:flex;flex-direction:column;gap:10px;">
            
            <div class="setting-row">
              <div class="setting-info">
                  <div class="setting-name">Standard Weekly Contribution</div>
                  <div class="setting-desc">Base GH₵ expected per book</div>
              </div>
              <div class="setting-control">
                  GH₵ <input type="number" wire:model="base_contribution" class="setting-input">
              </div>
            </div>
            
            <div class="setting-row">
              <div class="setting-info">
                  <div class="setting-name">Standard Welfare Amount</div>
                  <div class="setting-desc">GH₵ fee directed towards welfare fund</div>
              </div>
              <div class="setting-control">
                  GH₵ <input type="number" wire:model="welfare_amount" class="setting-input">
              </div>
            </div>
            
            <div class="setting-row">
              <div class="setting-info">
                  <div class="setting-name">Default Penalty Amount</div>
                  <div class="setting-desc">GH₵ fined for missed/late contributions</div>
              </div>
              <div class="setting-control">
                  GH₵ <input type="number" wire:model="penalty_amount" class="setting-input">
              </div>
            </div>

            <div class="setting-row">
              <div class="setting-info">
                  <div class="setting-name">Loan Interest Rate</div>
                  <div class="setting-desc">Universal % charged on principal borrowings</div>
              </div>
              <div class="setting-control">
                  % <input type="number" wire:model="loan_interest_rate" class="setting-input">
              </div>
            </div>

            <div style="margin-top:16px; text-align:right;">
                <button type="submit" class="btn btn-primary" style="padding:10px 20px;">
                    <span wire:loading.remove wire:target="saveSettings">Save Configuration</span>
                    <span wire:loading wire:target="saveSettings">Saving...</span>
                </button>
            </div>
            
          </div>
      </form>
    </div>

    <!-- SECURITY & PREFERENCES -->
    <div class="card">
      <div class="card-header"><div class="card-title">Security & Preferences</div></div>
      <div style="display:flex;flex-direction:column;gap:10px">
        
        <div class="setting-row">
          <div class="setting-info">
              <div class="setting-name">Allow Loan Extensions</div>
              <div class="setting-desc">Permit borrowers to extend due dates</div>
          </div>
          <div class="setting-control">
              <button wire:click="toggleExtension" class="toggle {{ $allow_loan_extensions ? 'on' : 'off' }}"></button>
          </div>
        </div>
        
        <div class="setting-row">
          <div class="setting-info">
              <div class="setting-name">Auto-Apply Penalties</div>
              <div class="setting-desc">System applies GH₵ automatically on Sunday PM</div>
          </div>
          <div class="setting-control">
              <button wire:click="togglePenalties" class="toggle {{ $auto_apply_penalties ? 'on' : 'off' }}"></button>
          </div>
        </div>

        <div class="setting-row" style="margin-top:20px;">
          <div class="setting-info">
              <div class="setting-name" style="color:var(--text); font-size:14px;">Change Administrator Password</div>
              <div class="setting-desc">Force log out other active sessions</div>
          </div>
        </div>

        <form wire:submit="changePassword">
            <div style="display:flex;flex-direction:column;gap:8px;">
                <input type="password" class="filter-input" placeholder="Current Password" required>
                <input type="password" class="filter-input" placeholder="New Password" required>
            </div>
            <div style="margin-top:12px; text-align:right;">
                <button type="submit" class="btn btn-outline" style="padding:8px 16px;">Update Password</button>
            </div>
        </form>

      </div>
    </div>
  </div>
</div>
