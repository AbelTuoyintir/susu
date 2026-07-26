<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\with;
use function Livewire\Volt\mount;
use Illuminate\Support\Facades\Hash;

layout('layouts.client');

state([
    'phone' => '',
    'phoneOne' => '',
    'country' => '',
    'city' => '',
    'state' => '',
    'zip' => '',
    'current_password' => '',
    'new_password' => '',
    'new_password_confirmation' => '',
]);

mount(function () {
    $user = auth()->user();
    $this->phone = $user->phone;
    $this->phoneOne = $user->phoneOne;
    $this->country = $user->country;
    $this->city = $user->city;
    $this->state = $user->state;
    $this->zip = $user->zip;
});

$saveProfile = function () {
    $this->validate([
        'phone' => 'required|string',
        'phoneOne' => 'nullable|string',
        'country' => 'nullable|string',
        'city' => 'nullable|string',
        'state' => 'nullable|string',
        'zip' => 'nullable|string',
    ]);
    
    auth()->user()->update([
        'phone' => $this->phone,
        'phoneOne' => $this->phoneOne,
        'country' => $this->country,
        'city' => $this->city,
        'state' => $this->state,
        'zip' => $this->zip,
    ]);
    
    session()->flash('success_profile', 'Profile settings updated successfully.');
};

$updatePassword = function () {
    $this->validate([
        'current_password' => 'required|string',
        'new_password' => 'required|string|min:6|confirmed',
    ]);
    
    if (!Hash::check($this->current_password, auth()->user()->password)) {
        $this->addError('current_password', 'The current password provided does not match our records.');
        return;
    }
    
    auth()->user()->update([
        'password' => Hash::make($this->new_password),
    ]);
    
    $this->current_password = '';
    $this->new_password = '';
    $this->new_password_confirmation = '';
    
    session()->flash('success_password', 'Password changed successfully.');
};

?>

<div class="page active" id="page-client-settings">
  <div class="grid-2">
    <!-- Profile Info Card -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">Profile Information</div>
        <div class="card-sub">Update your contact information</div>
      </div>
      
      @if (session()->has('success_profile'))
        <div style="background:var(--success-bg); border-color:var(--success); color:var(--success); padding: 8px 12px; border-radius:var(--r); margin-bottom:12px; font-weight:500;">
            ✔️ {{ session('success_profile') }}
        </div>
      @endif

      <form wire:submit="saveProfile" style="display:flex; flex-direction:column; gap:12px;">
        <div class="form-group">
            <label class="form-label">Full Name (Read Only)</label>
            <input type="text" class="form-input" value="{{ auth()->user()->name }}" disabled style="background:var(--bg4); color:var(--text3); border-color:transparent;">
        </div>

        <div class="form-group">
            <label class="form-label">Member ID (Read Only)</label>
            <input type="text" class="form-input mono" value="{{ auth()->user()->member_id }}" disabled style="background:var(--bg4); color:var(--text3); border-color:transparent;">
        </div>

        <div class="form-group">
            <label class="form-label">Primary Phone Number *</label>
            <input type="text" wire:model="phone" class="form-input" required>
            @error('phone') <span style="color:var(--danger); font-size:11px;">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Alternate Phone Number</label>
            <input type="text" wire:model="phoneOne" class="form-input">
            @error('phoneOne') <span style="color:var(--danger); font-size:11px;">{{ $message }}</span> @enderror
        </div>

        <div class="grid-2" style="gap:12px;">
            <div class="form-group">
                <label class="form-label">City</label>
                <input type="text" wire:model="city" class="form-input">
                @error('city') <span style="color:var(--danger); font-size:11px;">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">State / Region</label>
                <input type="text" wire:model="state" class="form-input">
                @error('state') <span style="color:var(--danger); font-size:11px;">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid-2" style="gap:12px;">
            <div class="form-group">
                <label class="form-label">Country</label>
                <input type="text" wire:model="country" class="form-input">
                @error('country') <span style="color:var(--danger); font-size:11px;">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">ZIP / Postal Code</label>
                <input type="text" wire:model="zip" class="form-input">
                @error('zip') <span style="color:var(--danger); font-size:11px;">{{ $message }}</span> @enderror
            </div>
        </div>

        <div style="text-align:right; margin-top:8px;">
            <button type="submit" class="btn btn-primary">Save Profile Info</button>
        </div>
      </form>
    </div>

    <!-- Security settings / password card -->
    <div class="card" style="height:fit-content;">
      <div class="card-header">
        <div class="card-title">Change Password</div>
        <div class="card-sub">Secure your account with a strong password</div>
      </div>

      @if (session()->has('success_password'))
        <div style="background:var(--success-bg); border-color:var(--success); color:var(--success); padding: 8px 12px; border-radius:var(--r); margin-bottom:12px; font-weight:500;">
            ✔️ {{ session('success_password') }}
        </div>
      @endif

      <form wire:submit="updatePassword" style="display:flex; flex-direction:column; gap:12px;">
        <div class="form-group">
            <label class="form-label">Current Password *</label>
            <input type="password" wire:model="current_password" class="form-input" required>
            @error('current_password') <span style="color:var(--danger); font-size:11px;">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">New Password *</label>
            <input type="password" wire:model="new_password" class="form-input" required>
            @error('new_password') <span style="color:var(--danger); font-size:11px;">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Confirm New Password *</label>
            <input type="password" wire:model="new_password_confirmation" class="form-input" required>
        </div>

        <div style="text-align:right; margin-top:8px;">
            <button type="submit" class="btn btn-danger" style="color:var(--danger); background:transparent; border-color:var(--danger);">Update Password</button>
        </div>
      </form>
    </div>
  </div>
</div>
