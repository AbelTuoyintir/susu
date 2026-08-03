<?php

use App\Models\User;
use App\Models\Tenant;
use App\Models\Setting;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Str;

use function Livewire\Volt\layout;
use function Livewire\Volt\rules;
use function Livewire\Volt\state;

layout('layouts.guest');

state([
    'org_name' => '',
    'org_slug' => '',
    'name' => '',
    'email' => '',
    'phone' => '',
    'phoneOne' => '',
    'password' => '',
    'password_confirmation' => ''
]);

rules([
    'org_name' => ['required', 'string', 'max:255'],
    'org_slug' => ['required', 'string', 'lowercase', 'max:255', 'unique:tenants,slug', 'regex:/^[a-z0-9\-]+$/i'],
    'name' => ['required', 'string', 'max:255'],
    'phone' => ['nullable', 'string', 'max:255'],
    'phoneOne' => ['nullable', 'string', 'max:255'],
    'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
    'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
]);

$updatedOrgName = function ($value) {
    $this->org_slug = Str::slug($value);
};

$register = function () {
    $validated = $this->validate();

    // Create the Tenant
    $tenant = Tenant::create([
        'name' => $validated['org_name'],
        'slug' => $validated['org_slug'],
    ]);

    // Seed default settings for the tenant
    $defaultSettings = [
        'base_contribution' => '120',
        'welfare_amount' => '10',
        'penalty_amount' => '6',
        'loan_interest_rate' => '10',
        'allow_loan_extensions' => '0',
        'auto_apply_penalties' => '0',
    ];
    foreach ($defaultSettings as $key => $val) {
        Setting::create([
            'key' => $key,
            'value' => $val,
            'tenant_id' => $tenant->id,
        ]);
    }

    $validated['password'] = Hash::make($validated['password']);

    // Generate a unique member ID (e.g. 6 random digits)
    do {
        $memberId = random_int(100000, 999999);
    } while (User::where('member_id', $memberId)->exists());
    
    $validated['member_id'] = (string) $memberId;
    $validated['role'] = 'admin'; // Mark the creator of the tenant as tenant admin
    $validated['tenant_id'] = $tenant->id;
    $validated['status'] = 'active';

    event(new Registered($user = User::create($validated)));

    Auth::login($user);

    $this->redirect(route('dashboard', absolute: false), navigate: true);
};

?>

<div class="space-y-5">
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-white">Create SaaS Organization</h2>
        <p class="text-sm text-indigo-200/60">Set up your Susu organization and admin account</p>
    </div>

    <form wire:submit="register" class="space-y-4">
        <!-- Organization details block -->
        <div class="p-4 bg-white/5 border border-white/10 rounded-2xl space-y-4">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-indigo-300">Organization Settings</h3>

            <!-- Organization Name -->
            <div>
                <label for="org_name" class="block text-sm font-medium text-indigo-100 mb-2">
                    Organization / Susu Group Name
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        🏢
                    </div>
                    <input wire:model.live="org_name" id="org_name" type="text"
                        class="block w-full pl-10 pr-3 py-2.5 bg-white/10 border border-white/20 rounded-xl text-white placeholder-indigo-200/50 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-all duration-200"
                        placeholder="My Savings Association" required autofocus>
                </div>
                @error('org_name')
                    <p class="mt-1 text-sm text-rose-300 animate-fade-in">{{ $message }}</p>
                @enderror
            </div>

            <!-- Organization Slug / Subdomain -->
            <div>
                <label for="org_slug" class="block text-sm font-medium text-indigo-100 mb-2">
                    Slug / Unique Subdomain
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        🔗
                    </div>
                    <input wire:model="org_slug" id="org_slug" type="text"
                        class="block w-full pl-10 pr-3 py-2.5 bg-white/10 border border-white/20 rounded-xl text-white placeholder-indigo-200/50 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-all duration-200"
                        placeholder="my-savings-association" required>
                </div>
                @error('org_slug')
                    <p class="mt-1 text-sm text-rose-300 animate-fade-in">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="p-4 bg-white/5 border border-white/10 rounded-2xl space-y-4">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-indigo-300">Administrator Details</h3>

            <!-- Full Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-indigo-100 mb-2">
                    Full Name
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-indigo-300 group-focus-within:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z" />
                        </svg>
                    </div>
                    <input wire:model="name" id="name" type="text"
                        class="block w-full pl-10 pr-3 py-2.5 bg-white/10 border border-white/20 rounded-xl text-white placeholder-indigo-200/50 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-all duration-200"
                        placeholder="John Doe" required autocomplete="name">
                </div>
                @error('name')
                    <p class="mt-1 text-sm text-rose-300 animate-fade-in">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-sm font-medium text-indigo-100 mb-2">
                    Email Address
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-indigo-300 group-focus-within:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8L12 13L21 8M5 19H19C20.1046 19 21 18.1046 21 17V7C21 5.89543 20.1046 5 19 5H5C3.89543 5 3 5.89543 3 7V17C3 18.1046 3.89543 19 5 19Z" />
                        </svg>
                    </div>
                    <input wire:model="email" id="email" type="email"
                        class="block w-full pl-10 pr-3 py-2.5 bg-white/10 border border-white/20 rounded-xl text-white placeholder-indigo-200/50 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-all duration-200"
                        placeholder="john@example.com" required autocomplete="username">
                </div>
                @error('email')
                    <p class="mt-1 text-sm text-rose-300 animate-fade-in">{{ $message }}</p>
                @enderror
            </div>

            <!-- Phone Number -->
            <div>
                <label for="phone" class="block text-sm font-medium text-indigo-100 mb-2">
                    Phone Number
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-indigo-300 group-focus-within:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5C3 3.89543 3.89543 3 5 3H8.27924C8.70967 3 9.09181 3.27543 9.22786 3.68377L10.7257 8.17721C10.8831 8.64932 10.6694 9.16531 10.2243 9.38787L7.96701 10.5165C9.06925 12.9612 11.0388 14.9308 13.4835 16.033L14.6121 13.7757C14.8347 13.3306 15.3507 13.1169 15.8228 13.2743L20.3162 14.7721C20.7246 14.9082 21 15.2903 21 15.7208V19C21 20.1046 20.1046 21 19 21H17C9.26801 21 3 14.732 3 7V5Z" />
                        </svg>
                    </div>
                    <input wire:model="phone" id="phone" type="tel"
                        class="block w-full pl-10 pr-3 py-2.5 bg-white/10 border border-white/20 rounded-xl text-white placeholder-indigo-200/50 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-all duration-200"
                        placeholder="+1 (555) 000-0000">
                </div>
                @error('phone')
                    <p class="mt-1 text-sm text-rose-300 animate-fade-in">{{ $message }}</p>
                @enderror
            </div>

            <!-- Emergency Contact -->
            <div>
                <label for="phoneOne" class="block text-sm font-medium text-indigo-100 mb-2">
                    Emergency Contact
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-indigo-300 group-focus-within:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <input wire:model="phoneOne" id="phoneOne" type="tel"
                        class="block w-full pl-10 pr-3 py-2.5 bg-white/10 border border-white/20 rounded-xl text-white placeholder-indigo-200/50 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-all duration-200"
                        placeholder="+1 (555) 000-0000">
                </div>
                @error('phoneOne')
                    <p class="mt-1 text-sm text-rose-300 animate-fade-in">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-indigo-100 mb-2">
                    Password
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-indigo-300 group-focus-within:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15V17M6 21H18C19.1046 21 20 20.1046 20 19V11C20 9.89543 19.1046 9 18 9H6C4.89543 9 4 9.89543 4 11V19C4 20.1046 4.89543 21 6 21Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9V7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7V9" />
                        </svg>
                    </div>
                    <input wire:model="password" id="password" type="password"
                        class="block w-full pl-10 pr-3 py-2.5 bg-white/10 border border-white/20 rounded-xl text-white placeholder-indigo-200/50 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-all duration-200"
                        placeholder="Create a strong password" required autocomplete="new-password">
                </div>
                @error('password')
                    <p class="mt-1 text-sm text-rose-300 animate-fade-in">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-indigo-200/50">Minimum 8 characters, 1 uppercase, 1 number</p>
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-indigo-100 mb-2">
                    Confirm Password
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-indigo-300 group-focus-within:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" />
                        </svg>
                    </div>
                    <input wire:model="password_confirmation" id="password_confirmation" type="password"
                        class="block w-full pl-10 pr-3 py-2.5 bg-white/10 border border-white/20 rounded-xl text-white placeholder-indigo-200/50 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-all duration-200"
                        placeholder="Confirm your password" required autocomplete="new-password">
                </div>
                @error('password_confirmation')
                    <p class="mt-1 text-sm text-rose-300 animate-fade-in">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Password Strength Indicator (Optional enhancement) -->
        <div class="mt-2" x-data="{ password: $wire.entangle('password') }" x-init="$watch('password', value => updateStrength(value))">
            <div class="h-1 w-full bg-white/10 rounded-full overflow-hidden">
                <div class="h-full transition-all duration-300 rounded-full" 
                     :class="{
                         'w-0 bg-white/10': !password,
                         'w-1/4 bg-red-500': password && password.length > 0 && password.length < 4,
                         'w-2/4 bg-yellow-500': password && password.length >= 4 && password.length < 8,
                         'w-3/4 bg-blue-500': password && password.length >= 8 && !/(?=.*[A-Z])(?=.*[0-9])/.test(password),
                         'w-full bg-emerald-500': password && password.length >= 8 && /(?=.*[A-Z])(?=.*[0-9])/.test(password)
                     }"
                     :style="{ width: getStrengthWidth() }">
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('login') }}" wire:navigate 
                class="inline-flex items-center gap-2 text-sm text-indigo-300 hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Already registered?
            </a>

            <button type="submit" 
                class="px-6 py-2.5 bg-gradient-to-r from-indigo-500 to-blue-600 hover:from-indigo-600 hover:to-blue-700 text-white font-semibold rounded-xl shadow-lg transform transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 focus:ring-offset-transparent">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    Create Account
                </span>
            </button>
        </div>

        <!-- Terms & Privacy -->
        <div class="mt-4 pt-3 border-t border-white/10 text-center">
            <p class="text-xs text-indigo-200/40">
                By creating an account, you agree to our 
                <a href="#" class="text-indigo-300 hover:text-white transition-colors">Terms of Service</a> and 
                <a href="#" class="text-indigo-300 hover:text-white transition-colors">Privacy Policy</a>
            </p>
        </div>
    </form>
</div>

<script>
    function updateStrength(password) {
        return password;
    }
    
    function getStrengthWidth() {
        return '0%';
    }
</script>