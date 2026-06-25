<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CoopAdmin — Savings & Loans Management System</title>
    <meta name="description" content="CoopAdmin is a modern, secure cooperative savings and loans management platform. Track contributions, manage books, approve loans, and generate reports.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,300&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0d1117;
            --bg2: #161b22;
            --bg3: #1c2230;
            --border: rgba(255,255,255,0.07);
            --border2: rgba(255,255,255,0.12);
            --text: #e6edf3;
            --text2: #8b949e;
            --text3: #6e7681;
            --accent: #00d4a8;
            --accent2: #00b894;
            --accent-glow: rgba(0,212,168,0.2);
            --accent-dim: rgba(0,212,168,0.08);
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            overflow-x: hidden;
        }
        a { text-decoration: none; color: inherit; }

        /* ── NOISE OVERLAY ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 0;
            opacity: .4;
        }

        /* ── GLOW ORBS ── */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
            z-index: 0;
            animation: orb-float 8s ease-in-out infinite;
        }
        .orb-1 { width: 500px; height: 500px; background: radial-gradient(circle, rgba(0,212,168,0.12), transparent 70%); top: -100px; left: -100px; }
        .orb-2 { width: 400px; height: 400px; background: radial-gradient(circle, rgba(56,139,253,0.08), transparent 70%); bottom: 100px; right: -50px; animation-delay: -4s; }
        @keyframes orb-float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-30px) scale(1.05); }
        }

        /* ── NAV ── */
        .nav {
            position: fixed; top: 0; left: 0; right: 0;
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 48px;
            background: rgba(13,17,23,0.8);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
            z-index: 100;
            transition: all .3s;
        }
        .nav-logo { display: flex; align-items: center; gap: 10px; }
        .logo-icon {
            width: 32px; height: 32px; border-radius: 8px;
            background: linear-gradient(135deg, var(--accent), #0099cc);
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 700; color: #000;
        }
        .logo-text { font-size: 15px; font-weight: 600; letter-spacing: -.3px; }
        .logo-sub { font-size: 10px; color: var(--text3); }
        .nav-links { display: flex; align-items: center; gap: 8px; }
        .btn-nav {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 18px; border-radius: 8px;
            font-size: 13px; font-weight: 500; cursor: pointer;
            transition: all .2s; border: 1px solid transparent;
        }
        .btn-ghost { color: var(--text2); border-color: var(--border2); background: transparent; }
        .btn-ghost:hover { background: var(--bg2); color: var(--text); }
        .btn-accent { background: var(--accent); color: #000; border-color: var(--accent); }
        .btn-accent:hover { background: var(--accent2); transform: translateY(-1px); box-shadow: 0 8px 24px rgba(0,212,168,0.3); }

        /* ── HERO ── */
        .hero {
            position: relative;
            min-height: 100vh;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            text-align: center;
            padding: 120px 24px 80px;
            z-index: 1;
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--accent-dim); border: 1px solid rgba(0,212,168,0.2);
            border-radius: 40px; padding: 6px 14px;
            font-size: 12px; font-weight: 500; color: var(--accent);
            margin-bottom: 28px; animation: fade-up .6s ease both;
        }
        .badge-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--accent); animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .4; } }

        .hero-title {
            font-size: clamp(40px, 7vw, 76px);
            font-weight: 700;
            line-height: 1.08;
            letter-spacing: -2px;
            max-width: 820px;
            animation: fade-up .7s ease .1s both;
        }
        .hero-title .gradient-text {
            background: linear-gradient(135deg, var(--accent) 0%, #5eead4 40%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero-sub {
            max-width: 540px;
            margin: 20px auto 36px;
            font-size: 16px;
            color: var(--text2);
            line-height: 1.7;
            animation: fade-up .7s ease .2s both;
        }
        .hero-cta {
            display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
            justify-content: center;
            animation: fade-up .7s ease .3s both;
        }
        .btn-hero {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 13px 28px; border-radius: 10px;
            font-size: 14px; font-weight: 600; cursor: pointer;
            transition: all .2s; border: 1px solid transparent;
        }
        .btn-hero-primary {
            background: var(--accent); color: #000; border-color: var(--accent);
        }
        .btn-hero-primary:hover {
            background: var(--accent2);
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(0,212,168,0.35);
        }
        .btn-hero-secondary {
            background: var(--bg2); color: var(--text);
            border-color: var(--border2);
        }
        .btn-hero-secondary:hover { background: var(--bg3); transform: translateY(-1px); }

        /* ── DASHBOARD PREVIEW ── */
        .preview-wrap {
            position: relative;
            margin: 60px auto 0;
            max-width: 900px;
            animation: fade-up .9s ease .4s both;
        }
        .preview-glow {
            position: absolute;
            inset: -40px;
            background: radial-gradient(ellipse at 50% 100%, rgba(0,212,168,0.15), transparent 60%);
            pointer-events: none;
        }
        .preview-frame {
            border-radius: 12px;
            border: 1px solid var(--border2);
            background: var(--bg2);
            overflow: hidden;
            box-shadow: 0 40px 80px rgba(0,0,0,0.5);
        }
        .preview-bar {
            display: flex; align-items: center; gap: 6px;
            padding: 10px 14px;
            background: var(--bg3);
            border-bottom: 1px solid var(--border);
        }
        .bar-dot { width: 10px; height: 10px; border-radius: 50%; }
        .preview-inner { display: flex; height: 380px; }
        .preview-sidebar {
            width: 160px; background: var(--bg2); border-right: 1px solid var(--border);
            padding: 12px 8px; flex-shrink: 0;
        }
        .pnav-item {
            display: flex; align-items: center; gap: 8px;
            padding: 7px 9px; border-radius: 6px;
            font-size: 11px; color: var(--text3); margin-bottom: 2px;
        }
        .pnav-item.active { background: var(--accent-dim); color: var(--accent); font-weight: 500; }
        .preview-main { flex: 1; padding: 16px; overflow: hidden; }
        .pmain-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 8px; margin-bottom: 12px; }
        .pstat {
            background: var(--bg3); border: 1px solid var(--border);
            border-radius: 8px; padding: 10px 12px;
        }
        .pstat-label { font-size: 9px; color: var(--text3); margin-bottom: 4px; }
        .pstat-val { font-size: 16px; font-weight: 700; font-family: 'DM Mono', monospace; }
        .pcharts { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px; }
        .pchart-box {
            background: var(--bg3); border: 1px solid var(--border);
            border-radius: 8px; padding: 10px; height: 120px;
            display: flex; flex-direction: column;
        }
        .pchart-title { font-size: 10px; font-weight: 500; margin-bottom: 6px; }
        .pchart-bars { display: flex; align-items: flex-end; gap: 4px; flex: 1; }
        .pbar { border-radius: 3px 3px 0 0; flex: 1; }
        .ptable-head { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 6px; padding: 6px 10px; }
        .ptable-cell { font-size: 9px; color: var(--text3); }
        .ptable-row { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 6px; padding: 6px 10px; border-top: 1px solid var(--border); }
        .ptable-val { font-size: 9px; font-family: 'DM Mono', monospace; }

        /* ── STATS STRIP ── */
        .stats-strip {
            position: relative; z-index: 1;
            display: flex; justify-content: center;
            flex-wrap: wrap; gap: 40px;
            padding: 48px 24px;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            background: linear-gradient(135deg, rgba(0,212,168,0.03), transparent);
        }
        .stat-item { text-align: center; }
        .stat-num { font-size: 36px; font-weight: 700; font-family: 'DM Mono', monospace; color: var(--accent); letter-spacing: -1px; }
        .stat-desc { font-size: 12px; color: var(--text3); margin-top: 4px; }

        /* ── FEATURES ── */
        .features {
            position: relative; z-index: 1;
            max-width: 1100px; margin: 0 auto;
            padding: 80px 24px;
        }
        .section-tag {
            font-size: 11px; font-weight: 600; color: var(--accent); text-transform: uppercase;
            letter-spacing: 1.5px; margin-bottom: 12px;
        }
        .section-title {
            font-size: clamp(28px, 4vw, 42px); font-weight: 700;
            letter-spacing: -1px; line-height: 1.15; margin-bottom: 14px;
        }
        .section-sub { font-size: 15px; color: var(--text2); max-width: 540px; line-height: 1.7; }
        .features-grid {
            display: grid; grid-template-columns: repeat(3,1fr); gap: 16px; margin-top: 48px;
        }
        .feature-card {
            background: var(--bg2); border: 1px solid var(--border);
            border-radius: 12px; padding: 24px;
            transition: all .25s; cursor: default;
            position: relative; overflow: hidden;
        }
        .feature-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 2px;
            background: linear-gradient(90deg, var(--accent), transparent);
            opacity: 0; transition: opacity .25s;
        }
        .feature-card:hover { border-color: var(--border2); transform: translateY(-3px); box-shadow: 0 20px 40px rgba(0,0,0,0.3); }
        .feature-card:hover::before { opacity: 1; }
        .feature-icon {
            width: 40px; height: 40px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; margin-bottom: 14px;
        }
        .feature-title { font-size: 14px; font-weight: 600; margin-bottom: 8px; }
        .feature-desc { font-size: 13px; color: var(--text2); line-height: 1.6; }

        /* ── HOW IT WORKS ── */
        .hiw {
            position: relative; z-index: 1;
            background: var(--bg2); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);
            padding: 80px 24px;
        }
        .hiw-inner { max-width: 1000px; margin: 0 auto; }
        .hiw-steps { display: grid; grid-template-columns: repeat(4,1fr); gap: 24px; margin-top: 48px; position: relative; }
        .hiw-steps::before {
            content: '';
            position: absolute; top: 20px; left: calc(12.5% + 20px); right: calc(12.5% + 20px);
            height: 1px; background: linear-gradient(90deg, var(--accent), transparent 50%, var(--accent));
            opacity: .3;
        }
        .hiw-step { text-align: center; }
        .hiw-num {
            width: 40px; height: 40px; border-radius: 50%;
            background: var(--accent-dim); border: 1px solid rgba(0,212,168,0.3);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 14px;
            font-size: 14px; font-weight: 700; color: var(--accent);
            font-family: 'DM Mono', monospace;
        }
        .hiw-title { font-size: 13px; font-weight: 600; margin-bottom: 6px; }
        .hiw-desc { font-size: 12px; color: var(--text3); line-height: 1.6; }

        /* ── CTA BAND ── */
        .cta-band {
            position: relative; z-index: 1;
            padding: 80px 24px;
            text-align: center;
        }
        .cta-card {
            max-width: 640px; margin: 0 auto;
            background: linear-gradient(135deg, var(--bg2), var(--bg3));
            border: 1px solid var(--border2);
            border-radius: 20px;
            padding: 48px 40px;
            position: relative; overflow: hidden;
        }
        .cta-card::before {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(ellipse at 50% 0%, rgba(0,212,168,0.08), transparent 60%);
            pointer-events: none;
        }
        .cta-title { font-size: 30px; font-weight: 700; letter-spacing: -1px; margin-bottom: 12px; }
        .cta-sub { font-size: 14px; color: var(--text2); margin-bottom: 28px; }

        /* ── FOOTER ── */
        .footer {
            position: relative; z-index: 1;
            border-top: 1px solid var(--border);
            padding: 32px 48px;
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;
        }
        .footer-text { font-size: 12px; color: var(--text3); }

        /* ── ANIMATIONS ── */
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .nav { padding: 14px 20px; }
            .features-grid { grid-template-columns: 1fr; }
            .hiw-steps { grid-template-columns: 1fr 1fr; }
            .hiw-steps::before { display: none; }
            .stats-strip { gap: 24px; }
            .footer { flex-direction: column; text-align: center; }
            .pmain-grid { grid-template-columns: repeat(2,1fr); }
            .pcharts { grid-template-columns: 1fr; }
        }
        @media (max-width: 480px) {
            .preview-wrap { display: none; }
            .hiw-steps { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- GLOW ORBS -->
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<!-- ── NAV ── -->
<nav class="nav" id="topnav">
    <div class="nav-logo">
        <div class="logo-icon">C</div>
        <div>
            <div class="logo-text">CoopAdmin</div>
            <div class="logo-sub">Savings & Loans</div>
        </div>
    </div>
    <div class="nav-links">
        @if (Route::has('login'))
            @auth
                <a href="{{ url('/dashboard') }}" class="btn-nav btn-ghost">Dashboard →</a>
            @else
                <a href="{{ route('login') }}" class="btn-nav btn-ghost">Sign In</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn-nav btn-accent">Get Started</a>
                @endif
            @endauth
        @endif
    </div>
</nav>

<!-- ── HERO ── -->
<section class="hero">
    <div class="hero-badge">
        <span class="badge-dot"></span>
        Built for Cooperative Societies
    </div>
    <h1 class="hero-title">
        Manage Your <span class="gradient-text">Savings & Loans</span><br>With Confidence
    </h1>
    <p class="hero-sub">
        CoopAdmin is the all-in-one platform for cooperative groups to track weekly contributions, manage passbooks, disburse loans, and generate year-end payout reports — all in real time.
    </p>
    <div class="hero-cta">
        @auth
            <a href="{{ url('/dashboard') }}" class="btn-hero btn-hero-primary">
                Go to Dashboard →
            </a>
        @else
            <a href="{{ route('login') }}" class="btn-hero btn-hero-primary">
                Sign In to Dashboard →
            </a>
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="btn-hero btn-hero-secondary">
                    Create Account
                </a>
            @endif
        @endauth
    </div>

    <!-- Dashboard Preview Mock -->
    <div class="preview-wrap">
        <div class="preview-glow"></div>
        <div class="preview-frame">
            <div class="preview-bar">
                <div class="bar-dot" style="background:#f85149"></div>
                <div class="bar-dot" style="background:#d29922"></div>
                <div class="bar-dot" style="background:#3fb950"></div>
                <div style="flex:1;background:rgba(255,255,255,0.05);border-radius:4px;height:16px;margin-left:8px;"></div>
            </div>
            <div class="preview-inner">
                <!-- Sidebar -->
                <div class="preview-sidebar">
                    <div style="font-size:9px;color:var(--text3);padding:4px 9px 8px;text-transform:uppercase;letter-spacing:.5px;">Overview</div>
                    <div class="pnav-item active">◈ Dashboard</div>
                    <div style="font-size:9px;color:var(--text3);padding:8px 9px 4px;text-transform:uppercase;letter-spacing:.5px;">Management</div>
                    <div class="pnav-item">⊕ Users</div>
                    <div class="pnav-item">▣ Books</div>
                    <div class="pnav-item">◎ Contributions</div>
                    <div class="pnav-item">◈ Loans</div>
                    <div style="font-size:9px;color:var(--text3);padding:8px 9px 4px;text-transform:uppercase;letter-spacing:.5px;">Finance</div>
                    <div class="pnav-item">▷ Payments</div>
                    <div class="pnav-item">◉ Defaulters</div>
                    <div class="pnav-item">◫ Reports</div>
                </div>
                <!-- Main Content -->
                <div class="preview-main">
                    <div class="pmain-grid">
                        <div class="pstat">
                            <div class="pstat-label">Total Users</div>
                            <div class="pstat-val" style="color:var(--accent)">248</div>
                        </div>
                        <div class="pstat">
                            <div class="pstat-label">Contributions</div>
                            <div class="pstat-val" style="color:#3fb950">48.2K</div>
                        </div>
                        <div class="pstat">
                            <div class="pstat-label">Loans Given</div>
                            <div class="pstat-val" style="color:#bc8cff">91.5K</div>
                        </div>
                        <div class="pstat">
                            <div class="pstat-label">Profit</div>
                            <div class="pstat-val" style="color:#d29922">7.3K</div>
                        </div>
                    </div>
                    <div class="pcharts">
                        <div class="pchart-box">
                            <div class="pchart-title">Weekly Contributions</div>
                            <div class="pchart-bars">
                                <div class="pbar" style="height:60%;background:rgba(0,212,168,0.4);border:1px solid var(--accent)"></div>
                                <div class="pbar" style="height:70%;background:rgba(0,212,168,0.4);border:1px solid var(--accent)"></div>
                                <div class="pbar" style="height:80%;background:rgba(0,212,168,0.4);border:1px solid var(--accent)"></div>
                                <div class="pbar" style="height:65%;background:rgba(0,212,168,0.4);border:1px solid var(--accent)"></div>
                                <div class="pbar" style="height:90%;background:rgba(0,212,168,0.5);border:1px solid var(--accent)"></div>
                                <div class="pbar" style="height:75%;background:rgba(0,212,168,0.4);border:1px solid var(--accent)"></div>
                                <div class="pbar" style="height:85%;background:rgba(0,212,168,0.4);border:1px solid var(--accent)"></div>
                                <div class="pbar" style="height:100%;background:rgba(0,212,168,0.5);border:1px solid var(--accent)"></div>
                            </div>
                        </div>
                        <div class="pchart-box">
                            <div class="pchart-title">Recent Activity</div>
                            <div style="display:flex;flex-direction:column;gap:6px;margin-top:4px">
                                <div style="display:flex;align-items:center;gap:6px">
                                    <div style="width:6px;height:6px;border-radius:50%;background:var(--accent);flex-shrink:0"></div>
                                    <span style="font-size:9px;color:var(--text3)">Kofi A. · Contribution · GH₵120</span>
                                </div>
                                <div style="display:flex;align-items:center;gap:6px">
                                    <div style="width:6px;height:6px;border-radius:50%;background:#bc8cff;flex-shrink:0"></div>
                                    <span style="font-size:9px;color:var(--text3)">Ama M. · Loan Approved</span>
                                </div>
                                <div style="display:flex;align-items:center;gap:6px">
                                    <div style="width:6px;height:6px;border-radius:50%;background:#f85149;flex-shrink:0"></div>
                                    <span style="font-size:9px;color:var(--text3)">3 users · Missed contribution</span>
                                </div>
                                <div style="display:flex;align-items:center;gap:6px">
                                    <div style="width:6px;height:6px;border-radius:50%;background:#3fb950;flex-shrink:0"></div>
                                    <span style="font-size:9px;color:var(--text3)">Fiifi K. · Loan repayment</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="background:var(--bg3);border:1px solid var(--border);border-radius:8px;overflow:hidden">
                        <div class="ptable-head">
                            <div class="ptable-cell">User</div>
                            <div class="ptable-cell">Type</div>
                            <div class="ptable-cell">Amount</div>
                            <div class="ptable-cell">Status</div>
                        </div>
                        <div class="ptable-row">
                            <div class="ptable-val">Kofi Asante</div>
                            <div class="ptable-val" style="color:#388bfd">Contribution</div>
                            <div class="ptable-val">GH₵120</div>
                            <div class="ptable-val" style="color:#3fb950">Paid</div>
                        </div>
                        <div class="ptable-row">
                            <div class="ptable-val">Ama Mensah</div>
                            <div class="ptable-val" style="color:#bc8cff">Loan</div>
                            <div class="ptable-val">GH₵800</div>
                            <div class="ptable-val" style="color:#3fb950">Active</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── STATS STRIP ── -->
<div class="stats-strip">
    <div class="stat-item">
        <div class="stat-num">248+</div>
        <div class="stat-desc">Members Tracked</div>
    </div>
    <div class="stat-item">
        <div class="stat-num">GH₵91K+</div>
        <div class="stat-desc">Loans Disbursed</div>
    </div>
    <div class="stat-item">
        <div class="stat-num">612</div>
        <div class="stat-desc">Passbooks Managed</div>
    </div>
    <div class="stat-item">
        <div class="stat-num">100%</div>
        <div class="stat-desc">Transparent Reporting</div>
    </div>
</div>

<!-- ── FEATURES ── -->
<section class="features">
    <div style="max-width:600px;">
        <div class="section-tag">Everything You Need</div>
        <h2 class="section-title">A Complete Cooperative<br>Management Suite</h2>
        <p class="section-sub">Built specifically for Susu and cooperative savings groups. Every feature from passbook management to profit-sharing calculations is included out of the box.</p>
    </div>
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon" style="background:rgba(0,212,168,0.1)">💰</div>
            <div class="feature-title">Weekly Contributions</div>
            <div class="feature-desc">Record and track weekly member contributions per passbook. Mark missed payments, apply penalties automatically, and monitor totals in real time.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:rgba(188,140,255,0.1)">🏦</div>
            <div class="feature-title">Loan Management</div>
            <div class="feature-desc">Approve or reject loan requests, track repayment progress with visual bars, mark overdue loans, and send SMS reminders to borrowers.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:rgba(56,139,253,0.1)">📒</div>
            <div class="feature-title">Passbook System</div>
            <div class="feature-desc">Assign books to members, track their balance and contribution history, and deactivate books that are no longer in use.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:rgba(63,185,80,0.1)">📊</div>
            <div class="feature-title">Reports & Profit Sharing</div>
            <div class="feature-desc">Auto-calculate year-end distributable profit from welfare, penalties, and loan interest. Generate PDF payout sheets per member.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:rgba(248,81,73,0.1)">⚠️</div>
            <div class="feature-title">Defaulter Tracking</div>
            <div class="feature-desc">Instantly see who has missed contributions or overdue loans. Send individual or bulk SMS reminders with a single click.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:rgba(210,153,34,0.1)">🔔</div>
            <div class="feature-title">SMS Notifications</div>
            <div class="feature-desc">Send bulk SMS messages with pre-built templates for contribution reminders, penalty notices, and year-end payout announcements.</div>
        </div>
    </div>
</section>

<!-- ── HOW IT WORKS ── -->
<section class="hiw">
    <div class="hiw-inner">
        <div style="text-align:center;">
            <div class="section-tag">Simple Setup</div>
            <h2 class="section-title">Up and Running in Minutes</h2>
        </div>
        <div class="hiw-steps">
            <div class="hiw-step">
                <div class="hiw-num">01</div>
                <div class="hiw-title">Add Members</div>
                <div class="hiw-desc">Register cooperative members with their names, contacts, and assign unique Member IDs automatically.</div>
            </div>
            <div class="hiw-step">
                <div class="hiw-num">02</div>
                <div class="hiw-title">Assign Books</div>
                <div class="hiw-desc">Create and assign passbooks to members. Set weekly contribution amounts and track balances per book.</div>
            </div>
            <div class="hiw-step">
                <div class="hiw-num">03</div>
                <div class="hiw-title">Record Weekly</div>
                <div class="hiw-desc">Record weekly contributions, mark missed payments, and manage loan disbursements all from one dashboard.</div>
            </div>
            <div class="hiw-step">
                <div class="hiw-num">04</div>
                <div class="hiw-title">Generate Reports</div>
                <div class="hiw-desc">At year-end, calculate profit sharing automatically and generate PDF payout reports for each eligible member.</div>
            </div>
        </div>
    </div>
</section>

<!-- ── CTA BAND ── -->
<section class="cta-band">
    <div class="cta-card">
        <h2 class="cta-title">Ready to Modernize Your Cooperative?</h2>
        <p class="cta-sub">Join cooperative groups already managing their savings and loans with CoopAdmin. Get started with your admin account today.</p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            @auth
                <a href="{{ url('/dashboard') }}" class="btn-hero btn-hero-primary">Open Dashboard →</a>
            @else
                <a href="{{ route('login') }}" class="btn-hero btn-hero-primary">Sign In Now →</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn-hero btn-hero-secondary">Create Account</a>
                @endif
            @endauth
        </div>
    </div>
</section>

<!-- ── FOOTER ── -->
<footer class="footer">
    <div class="nav-logo">
        <div class="logo-icon">C</div>
        <div>
            <div class="logo-text">CoopAdmin</div>
            <div class="logo-sub">Savings & Loans</div>
        </div>
    </div>
    <div class="footer-text">
        &copy; {{ date('Y') }} CoopAdmin. Built with Laravel {{ Illuminate\Foundation\Application::VERSION }} · Livewire Volt
    </div>
</footer>

<script>
    // Shrink nav on scroll
    window.addEventListener('scroll', () => {
        document.getElementById('topnav').style.padding = window.scrollY > 50 ? '10px 48px' : '16px 48px';
    });
</script>
</body>
</html>
