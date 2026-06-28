<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CoopAdmin — Member Portal</title>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  :root {
    --bg: #0d1117;
    --bg2: #161b22;
    --bg3: #1c2230;
    --bg4: #21262d;
    --border: rgba(255,255,255,0.07);
    --border2: rgba(255,255,255,0.12);
    --text: #e6edf3;
    --text2: #8b949e;
    --text3: #6e7681;
    --accent: #00d4a8;
    --accent2: #00b894;
    --accent-glow: rgba(0,212,168,0.15);
    --accent-dim: rgba(0,212,168,0.08);
    --danger: #f85149;
    --danger-bg: rgba(248,81,73,0.1);
    --warn: #d29922;
    --warn-bg: rgba(210,153,34,0.1);
    --info: #388bfd;
    --info-bg: rgba(56,139,253,0.1);
    --success: #3fb950;
    --success-bg: rgba(63,185,80,0.1);
    --purple: #bc8cff;
    --purple-bg: rgba(188,140,255,0.1);
    --sidebar-w: 220px;
    --r: 8px;
    --r2: 12px;
    --fs: 12px;
    --fs-sm: 11px;
    --fs-md: 13px;
    --fs-lg: 15px;
    --fs-xl: 20px;
    --fs-2xl: 26px;
  }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); font-size: var(--fs); line-height: 1.5; overflow-x: hidden; }
  a { color: inherit; text-decoration: none; }
  button { font-family: inherit; cursor: pointer; border: none; outline: none; }
  input, select, textarea { font-family: inherit; outline: none; }

  /* SIDEBAR */
  .sidebar {
    position: fixed; left: 0; top: 0; bottom: 0; width: var(--sidebar-w);
    background: var(--bg2); border-right: 1px solid var(--border);
    display: flex; flex-direction: column; z-index: 100; transition: transform .25s ease;
  }
  .sidebar-logo {
    padding: 18px 16px 14px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 10px;
  }
  .logo-icon {
    width: 30px; height: 30px; border-radius: 8px;
    background: linear-gradient(135deg, var(--accent), #388bfd);
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 700; color: #000; flex-shrink: 0;
  }
  .logo-text { font-size: var(--fs-md); font-weight: 600; letter-spacing: -.3px; }
  .logo-sub { font-size: 10px; color: var(--text3); margin-top: 1px; }
  .sidebar-nav { flex: 1; overflow-y: auto; padding: 10px 8px; }
  .nav-section { font-size: 10px; font-weight: 500; color: var(--text3); text-transform: uppercase; letter-spacing: .8px; padding: 10px 8px 6px; }
  .nav-item {
    display: flex; align-items: center; gap: 9px;
    padding: 8px 10px; border-radius: var(--r); cursor: pointer;
    font-size: var(--fs); color: var(--text2); transition: all .15s;
    margin-bottom: 1px; position: relative;
  }
  .nav-item:hover { background: var(--bg3); color: var(--text); }
  .nav-item.active { background: var(--accent-dim); color: var(--accent); font-weight: 500; }
  .nav-item.active::before { content:''; position:absolute; left:0; top:20%; bottom:20%; width:3px; background:var(--accent); border-radius:0 3px 3px 0; }
  .nav-icon { font-size: 14px; width: 18px; text-align: center; }
  .sidebar-footer { padding: 12px 8px; border-top: 1px solid var(--border); }
  .admin-card { display:flex; align-items:center; gap:9px; padding:8px 10px; border-radius:var(--r); }
  .avatar { width:28px; height:28px; border-radius:50%; background: linear-gradient(135deg,#00d4a8,#388bfd); display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:600; color:#fff; flex-shrink:0; }
  .admin-name { font-size: var(--fs); font-weight:500; }
  .admin-role { font-size: 10px; color: var(--text3); }

  /* TOPBAR */
  .topbar {
    position: fixed; top: 0; left: var(--sidebar-w); right: 0; height: 52px;
    background: var(--bg2); border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 12px; padding: 0 20px; z-index: 90;
  }
  .hamburger { display:none; background:none; color:var(--text2); font-size:18px; padding:4px; }
  .page-title { font-size: var(--fs-md); font-weight: 600; }
  .page-subtitle { font-size: var(--fs-sm); color: var(--text3); }
  .topbar-right { margin-left: auto; display:flex; align-items:center; gap:10px; }
  .icon-btn { width:32px; height:32px; background:var(--bg3); border:1px solid var(--border); border-radius:var(--r); display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all .15s; color: var(--text2); font-size: 14px; }
  .icon-btn:hover { border-color:var(--border2); color:var(--text); }
  .notif-dot { position:relative; }
  .notif-dot::after { content:''; position:absolute; top:6px; right:6px; width:6px; height:6px; background:var(--danger); border-radius:50%; border:1.5px solid var(--bg2); }

  /* MAIN */
  .main { margin-left: var(--sidebar-w); margin-top: 52px; padding: 20px; min-height: calc(100vh - 52px); }

  /* CARDS */
  .card { background: var(--bg2); border: 1px solid var(--border); border-radius: var(--r2); padding: 16px; }
  .card-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; }
  .card-title { font-size: var(--fs-md); font-weight: 600; }
  .card-sub { font-size: var(--fs-sm); color: var(--text3); margin-top:2px; }

  /* STAT CARDS */
  .stats-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(160px,1fr)); gap:12px; margin-bottom:20px; }
  .stat-card { background:var(--bg2); border:1px solid var(--border); border-radius:var(--r2); padding:14px; transition:border-color .15s; }
  .stat-card:hover { border-color: var(--border2); }
  .stat-label { font-size:var(--fs-sm); color:var(--text3); margin-bottom:6px; display:flex; align-items:center; gap:5px; }
  .stat-value { font-size: var(--fs-xl); font-weight:600; font-family:'DM Mono',monospace; letter-spacing:-.5px; }
  .stat-change { font-size:var(--fs-sm); margin-top:4px; display:flex; align-items:center; gap:3px; }
  .up { color: var(--success); } .down { color: var(--danger); }
  .stat-icon { width:28px; height:28px; border-radius:7px; display:flex; align-items:center; justify-content:center; font-size:13px; margin-bottom:10px; }

  /* GRIDS */
  .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
  .grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; }
  .col-span-2 { grid-column: span 2; }

  /* TABLES */
  .table-wrap { overflow-x:auto; }
  table { width:100%; border-collapse:collapse; }
  th { font-size:var(--fs-sm); font-weight:500; color:var(--text3); text-align:left; padding:8px 12px; border-bottom:1px solid var(--border); white-space:nowrap; }
  td { font-size:var(--fs); padding:10px 12px; border-bottom:1px solid var(--border); vertical-align:middle; }
  tr:last-child td { border-bottom:none; }
  tr:hover td { background: rgba(255,255,255,0.02); }
  .mono { font-family:'DM Mono',monospace; }

  /* BADGES */
  .badge { display:inline-flex; align-items:center; gap:4px; font-size:10px; font-weight:500; padding:3px 8px; border-radius:20px; white-space:nowrap; }
  .badge-success { background: var(--success-bg); color: var(--success); }
  .badge-danger { background: var(--danger-bg); color: var(--danger); }
  .badge-info { background: var(--info-bg); color: var(--info); }
  .badge-warn { background: var(--warn-bg); color: var(--warn); }
  .badge-neutral { background: var(--bg4); color: var(--text2); }

  /* BUTTONS */
  .btn { display:inline-flex; align-items:center; justify-content:center; gap:6px; font-weight:500; border-radius:var(--r); transition:all .15s; text-decoration:none; padding:8px 14px; font-size:var(--fs); }
  .btn-sm { padding:6px 10px; font-size:var(--fs-sm); }
  .btn-xs { padding:4px 8px; font-size:10px; border-radius:4px; }
  .btn-primary { background:var(--accent); color:#000; }
  .btn-primary:hover { background:var(--accent2); }
  .btn-outline { background:transparent; border:1px solid var(--border2); color:var(--text); }
  .btn-outline:hover { border-color:var(--text2); background:rgba(255,255,255,0.02); }
  .btn-danger { background:var(--danger-bg); color:var(--danger); border:1px solid rgba(248,81,73,0.15); }
  .btn-danger:hover { background:rgba(248,81,73,0.2); }

  /* FILTERS */
  .filters { display:flex; gap:8px; align-items:center; margin-bottom:16px; flex-wrap:wrap; }
  .filter-input { background:var(--bg2); border:1px solid var(--border); border-radius:var(--r); color:var(--text); padding:6px 12px; font-size:var(--fs-sm); font-family:inherit; }
  .filter-input:focus { border-color:var(--border2); }

  /* FORM STYLING */
  .form-group { display:flex; flex-direction:column; gap:6px; margin-bottom:14px; }
  .form-label { font-size:var(--fs-sm); color:var(--text3); }
  .form-input { width:100%; background:var(--bg3); border:1px solid var(--border); border-radius:var(--r); color:var(--text); padding:8px 12px; font-size:var(--fs); }
  .form-input:focus { border-color:var(--accent); }

  /* PROGRESS BAR */
  .progress-bar { width:100%; height:6px; background:var(--bg4); border-radius:3px; overflow:hidden; }
  .progress-fill { height:100%; border-radius:3px; }

  /* RESPONSIVE LAYOUT */
  @media (max-width: 768px) {
    .sidebar { transform: translateX(-100%); }
    .sidebar.open { transform: translateX(0); }
    .topbar { left: 0; }
    .main { margin-left: 0; }
    .hamburger { display: block; }
    .grid-2 { grid-template-columns: 1fr; }
    .grid-3 { grid-template-columns: 1fr; }
    .sidebar-overlay { display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:95; }
    .sidebar-overlay.open { display:block; }
  }
  @media (max-width: 480px) {
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .main { padding: 12px; }
  }

  ::-webkit-scrollbar { width:4px; height:4px; }
  ::-webkit-scrollbar-track { background:transparent; }
  ::-webkit-scrollbar-thumb { background:var(--border2); border-radius:4px; }
  .swal2-popup { font-family:'DM Sans',sans-serif !important; font-size:13px !important; }
</style>
</head>
<body>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="overlay" onclick="closeSidebar()"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon">M</div>
    <div>
      <div class="logo-text">CoopMember</div>
      <div class="logo-sub">Member Portal</div>
    </div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section">Overview</div>
    <a href="/client/dashboard" class="nav-item {{ request()->routeIs('client.dashboard') ? 'active' : '' }}" wire:navigate><span class="nav-icon">◈</span> Dashboard</a>
    <a href="/client/books" class="nav-item {{ request()->routeIs('client.books') ? 'active' : '' }}" wire:navigate><span class="nav-icon">▣</span> My Passbook</a>

    <div class="nav-section">My Accounts</div>
    <a href="/client/contributions" class="nav-item {{ request()->routeIs('client.contributions') ? 'active' : '' }}" wire:navigate><span class="nav-icon">◎</span> Contributions</a>
    <a href="/client/loans" class="nav-item {{ request()->routeIs('client.loans') ? 'active' : '' }}" wire:navigate><span class="nav-icon">◈</span> Loans & Requests</a>
    <a href="/client/announcements" class="nav-item {{ request()->routeIs('client.announcements') ? 'active' : '' }}" wire:navigate><span class="nav-icon">📣</span> Announcements</a>

    <div class="nav-section">Services</div>
    <a href="/client/payments" class="nav-item {{ request()->routeIs('client.payments') ? 'active' : '' }}" wire:navigate><span class="nav-icon">▷</span> Make Payment</a>
    <a href="/client/settings" class="nav-item {{ request()->routeIs('client.settings') ? 'active' : '' }}" wire:navigate><span class="nav-icon">⊛</span> Settings</a>
  </nav>
  <div class="sidebar-footer">
    @auth
    <div class="admin-card">
      <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
      <div style="flex:1;min-width:0;">
        <div class="admin-name" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ auth()->user()->name }}</div>
        <div class="admin-role">ID: {{ auth()->user()->member_id }}</div>
      </div>
    </div>
    <form method="POST" action="{{ route('logout') }}" style="padding:4px 10px 0;">
      @csrf
      <button type="submit" style="width:100%;background:transparent;color:var(--text3);font-size:11px;padding:5px 0;cursor:pointer;border:none;text-align:left;transition:color .15s;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--text3)'">
        ⟶ Sign Out
      </button>
    </form>
    @endauth
  </div>
</aside>

<!-- TOPBAR -->
<header class="topbar">
  <button class="hamburger" onclick="toggleSidebar()">☰</button>
  <div>
    @php
      $routePageMap = [
        'client.dashboard'     => ['My Dashboard', 'Personal Financial Overview'],
        'client.books'         => ['My Passbooks', 'Active Susu Savings Accounts'],
        'client.contributions' => ['My Contributions', 'Savings History & Statement'],
        'client.loans'         => ['My Loans & Requests', 'Apply & Track Repayments'],
        'client.payments'      => ['Self-Service Payment', 'Pay Contribution or Loan Repayment Online'],
        'client.announcements' => ['Announcements', 'Latest Updates & Messages'],
        'client.settings'      => ['Account Settings', 'Update Profile & Security Details'],
      ];
      $currentRoute = Route::currentRouteName();
      $pageInfo = $routePageMap[$currentRoute] ?? ['CoopMember', 'Member Portal'];
    @endphp
    <div class="page-title" id="topbar-title">{{ $pageInfo[0] }}</div>
    <div class="page-subtitle" id="topbar-sub">{{ $pageInfo[1] }}</div>
  </div>
  <div class="topbar-right">
    <div style="position:relative;">
        <a href="/client/announcements" class="icon-btn {{ auth()->user()->unreadNotifications->count() > 0 ? 'notif-dot' : '' }}" wire:navigate style="text-decoration:none;">
          🔔
          @if(auth()->user()->unreadNotifications->count() > 0)
            <span style="position:absolute; top:-5px; right:-5px; background:var(--danger); color:#fff; font-size:9px; font-weight:600; min-width:14px; height:14px; border-radius:10px; display:flex; align-items:center; justify-content:center; border:2px solid var(--bg2);">
              {{ auth()->user()->unreadNotifications->count() }}
            </span>
          @endif
        </a>
    </div>
    <a href="/client/settings" class="icon-btn" wire:navigate style="text-decoration:none;">⚙</a>
  </div>
</header>

<!-- MAIN CONTENT -->
<main class="main">
    {{ $slot ?? '' }}
    @yield('content')
</main>

<script>
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('overlay').classList.toggle('open');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('overlay').classList.remove('open');
}

// Intercept Livewire navigates to close sidebar automatically on mobile
document.addEventListener('livewire:navigated', () => {
  closeSidebar();
});
</script>
</body>
</html>
