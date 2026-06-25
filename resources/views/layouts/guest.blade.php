<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>CoopAdmin — Savings & Loans Management</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            /* Custom smooth scroll behavior */
            html {
                scroll-behavior: smooth;
            }
            
            /* Custom focus ring for better accessibility */
            *:focus-visible {
                outline: 2px solid #6366f1;
                outline-offset: 2px;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <!-- Animated background with financial theme -->
        <div class="fixed inset-0 bg-gradient-to-br from-slate-900 via-indigo-900 to-slate-900 overflow-hidden">
            <!-- Animated grid pattern for financial feel -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0" style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 40px 40px;"></div>
            </div>
            
            <!-- Floating animated particles -->
            <div class="absolute top-20 left-10 w-72 h-72 bg-indigo-500/20 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-20 right-10 w-80 h-80 bg-blue-500/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl"></div>
            
            <!-- Moving gradient line (financial chart vibe) -->
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-indigo-400 to-transparent animate-pulse"></div>
        </div>

        <!-- Main Content Container -->
        <div class="relative min-h-screen flex flex-col items-center justify-center p-4 sm:p-6 lg:p-8">
            <!-- Logo / Brand Header -->
            <div class="text-center mb-8 animate-fade-in">
                <div class="inline-flex items-center justify-center w-14 h-14 bg-gradient-to-br from-teal-400 to-cyan-600 rounded-2xl shadow-xl mb-4 transform transition-transform hover:scale-105" style="font-size:22px;font-weight:700;color:#000;font-family:sans-serif;">
                    C
                </div>
                <h1 class="text-4xl font-bold text-white tracking-tight">CoopAdmin</h1>
                <p class="text-indigo-200/80 text-sm mt-2">Savings & Loans Management</p>
            </div>

            <!-- Card Container -->
            <div class="w-full sm:max-w-md">
                <div class="bg-white/10 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/20 p-6 sm:p-8 transition-all duration-300 hover:shadow-indigo-500/10">
                    {{ $slot }}
                </div>
                
                <!-- Footer -->
                <div class="text-center mt-6">
                    <p class="text-xs text-indigo-200/40">
                        © {{ date('Y') }} CoopAdmin. Cooperative Savings & Loans Platform.
                    </p>
                </div>
            </div>
        </div>

        <!-- Custom Animation Keyframes (add to your CSS or keep here) -->
        <style>
            @keyframes fade-in {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .animate-fade-in {
                animation: fade-in 0.6s ease-out;
            }
            
            @keyframes pulse {
                0%, 100% {
                    opacity: 0.3;
                    transform: scale(1);
                }
                50% {
                    opacity: 0.5;
                    transform: scale(1.05);
                }
            }
            
            .delay-1000 {
                animation-delay: 1s;
            }
        </style>
    </body>
</html>