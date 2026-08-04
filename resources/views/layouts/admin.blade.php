<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CoopAdmin — Savings & Loans Management</title>
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
    background: linear-gradient(135deg, var(--accent), #0099cc);
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
  .nav-badge { margin-left: auto; background: var(--danger); color: #fff; font-size: 10px; font-weight: 600; padding: 1px 6px; border-radius: 20px; }
  .sidebar-footer { padding: 12px 8px; border-top: 1px solid var(--border); }
  .admin-card { display:flex; align-items:center; gap:9px; padding:8px 10px; border-radius:var(--r); }
  .avatar { width:28px; height:28px; border-radius:50%; background: linear-gradient(135deg,#667eea,#764ba2); display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:600; color:#fff; flex-shrink:0; }
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
  .search-box {
    display: flex; align-items: center; gap: 8px;
    background: var(--bg3); border: 1px solid var(--border); border-radius: var(--r);
    padding: 6px 12px;
  }
  .search-box input { background:none; border:none; color:var(--text); font-size:var(--fs-sm); width:160px; }
  .search-box input::placeholder { color: var(--text3); }
  .icon-btn { width:32px; height:32px; background:var(--bg3); border:1px solid var(--border); border-radius:var(--r); display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all .15s; color: var(--text2); font-size: 14px; }
  .icon-btn:hover { border-color:var(--border2); color:var(--text); }
  .notif-dot { position:relative; }
  .notif-dot::after { content:''; position:absolute; top:6px; right:6px; width:6px; height:6px; background:var(--danger); border-radius:50%; border:1.5px solid var(--bg2); }

  /* MAIN */
  .main { margin-left: var(--sidebar-w); margin-top: 52px; padding: 20px; min-height: calc(100vh - 52px); }

  /* PAGES */
  .page { display: none; }
  .page.active { display: block; }

  /* CARDS */
  .card { background: var(--bg2); border: 1px solid var(--border); border-radius: var(--r2); padding: 16px; }
  .card-sm { padding: 12px 14px; }
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
  .badge-success { background:var(--success-bg); color:var(--success); }
  .badge-danger { background:var(--danger-bg); color:var(--danger); }
  .badge-warn { background:var(--warn-bg); color:var(--warn); }
  .badge-info { background:var(--info-bg); color:var(--info); }
  .badge-purple { background:var(--purple-bg); color:var(--purple); }
  .badge-neutral { background:var(--bg3); color:var(--text2); }
  .dot { width:6px; height:6px; border-radius:50%; background:currentColor; display:inline-block; }

  /* BUTTONS */
  .btn { display:inline-flex; align-items:center; gap:6px; padding:7px 14px; border-radius:var(--r); font-size:var(--fs); font-weight:500; transition:all .15s; border:1px solid transparent; cursor:pointer; }
  .btn-primary { background:var(--accent); color:#000; border-color:var(--accent); }
  .btn-primary:hover { background:var(--accent2); }
  .btn-outline { background:transparent; color:var(--text2); border-color:var(--border2); }
  .btn-outline:hover { background:var(--bg3); color:var(--text); }
  .btn-danger { background:var(--danger-bg); color:var(--danger); border-color:rgba(248,81,73,0.2); }
  .btn-danger:hover { background:rgba(248,81,73,0.2); }
  .btn-sm { padding:5px 10px; font-size:var(--fs-sm); }
  .btn-xs { padding:3px 8px; font-size:10px; }
  .btn-icon { width:30px; height:30px; padding:0; justify-content:center; }

  /* FILTERS */
  .filters { display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:14px; }
  .filter-input { background:var(--bg3); border:1px solid var(--border); border-radius:var(--r); padding:6px 10px; font-size:var(--fs-sm); color:var(--text); }
  .filter-input:focus { border-color:var(--accent); }

  /* USER AVATAR ROW */
  .user-row { display:flex; align-items:center; gap:9px; }
  .user-avatar { width:26px; height:26px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:600; color:#fff; flex-shrink:0; }

  /* PROGRESS */
  .progress-bar { height:4px; background:var(--bg4); border-radius:4px; overflow:hidden; }
  .progress-fill { height:100%; border-radius:4px; transition:width .3s; }

  /* CHART CONTAINER */
  .chart-container { position:relative; }

  /* LOAN DETAIL CARD */
  .loan-detail { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
  .detail-row { display:flex; flex-direction:column; gap:2px; }
  .detail-label { font-size:10px; color:var(--text3); text-transform:uppercase; letter-spacing:.5px; }
  .detail-value { font-size:var(--fs-md); font-weight:500; }

  /* TIMELINE */
  .timeline { position:relative; padding-left:20px; }
  .timeline::before { content:''; position:absolute; left:6px; top:4px; bottom:4px; width:1px; background:var(--border); }
  .timeline-item { position:relative; padding-bottom:14px; }
  .timeline-dot { position:absolute; left:-17px; top:3px; width:8px; height:8px; border-radius:50%; border:2px solid var(--bg2); }
  .timeline-time { font-size:10px; color:var(--text3); margin-bottom:2px; }
  .timeline-text { font-size:var(--fs-sm); }

  /* DONUT LABEL */
  .donut-center { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); text-align:center; }

  /* SETTINGS */
  .setting-row { display:flex; align-items:center; justify-content:space-between; padding:12px 0; border-bottom:1px solid var(--border); }
  .setting-row:last-child { border-bottom:none; }
  .setting-info { flex:1; }
  .setting-name { font-size:var(--fs-sm); font-weight:500; margin-bottom:2px; }
  .setting-desc { font-size:var(--fs-sm); color:var(--text3); }
  .setting-control { display:flex; align-items:center; gap:8px; }
  .setting-input { background:var(--bg3); border:1px solid var(--border); border-radius:var(--r); padding:5px 10px; font-size:var(--fs-sm); color:var(--text); width:90px; text-align:right; }
  .setting-input:focus { border-color:var(--accent); }
  .toggle { width:36px; height:20px; border-radius:20px; border:none; cursor:pointer; position:relative; transition:background .2s; }
  .toggle.on { background:var(--accent); }
  .toggle.off { background:var(--bg4); border:1px solid var(--border2); }
  .toggle::after { content:''; position:absolute; width:14px; height:14px; border-radius:50%; background:#fff; top:3px; transition:left .2s; }
  .toggle.on::after { left:19px; }
  .toggle.off::after { left:3px; }

  /* NOTIFICATION PANEL */
  .notif-item { display:flex; gap:10px; padding:12px 0; border-bottom:1px solid var(--border); }
  .notif-item:last-child { border-bottom:none; }
  .notif-icon-wrap { width:32px; height:32px; border-radius:var(--r); display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0; }
  .notif-content { flex:1; }
  .notif-title { font-size:var(--fs-sm); font-weight:500; margin-bottom:2px; }
  .notif-msg { font-size:var(--fs-sm); color:var(--text3); }
  .notif-time { font-size:10px; color:var(--text3); margin-top:3px; }

  /* ALERT BANNER */
  .alert-banner { display:flex; align-items:center; gap:10px; padding:10px 14px; border-radius:var(--r); margin-bottom:14px; }
  .alert-banner.danger { background:var(--danger-bg); border:1px solid rgba(248,81,73,0.2); color:var(--danger); }
  .alert-banner.warn { background:var(--warn-bg); border:1px solid rgba(210,153,34,0.2); color:var(--warn); }

  /* MOBILE OVERLAY */
  .sidebar-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:99; }

  /* RESPONSIVE */
  @media (max-width: 900px) {
    .sidebar { transform: translateX(-100%); }
    .sidebar.open { transform: translateX(0); }
    .sidebar-overlay.show { display:block; }
    .topbar { left: 0; }
    .main { margin-left: 0; }
    .hamburger { display:flex; }
    .grid-2, .grid-3 { grid-template-columns:1fr; }
    .col-span-2 { grid-column:span 1; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .search-box { display:none; }
    .loan-detail { grid-template-columns:1fr; }
  }
  @media (max-width: 480px) {
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .main { padding: 12px; }
  }

  /* SCROLLBAR */
  ::-webkit-scrollbar { width:4px; height:4px; }
  ::-webkit-scrollbar-track { background:transparent; }
  ::-webkit-scrollbar-thumb { background:var(--border2); border-radius:4px; }

  /* SWAl override */
  .swal2-popup { font-family:'DM Sans',sans-serif !important; font-size:13px !important; }
</style>
</head>
<body>

        <!-- SweetAlert2 flash messages (admin layout) -->
        @if (session('success'))
            <input type="hidden" data-flash="success" data-message="{{ addslashes(session('success')) }}" />
        @endif
        @if (session('error'))
            <input type="hidden" data-flash="error" data-message="{{ addslashes(session('error')) }}" />
        @endif

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="overlay" onclick="closeSidebar()"></div>


<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon">{{ auth()->check() && auth()->user()->tenant ? strtoupper(substr(auth()->user()->tenant->name, 0, 1)) : 'C' }}</div>
    <div>
      <div class="logo-text" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:140px;">{{ auth()->check() && auth()->user()->tenant ? auth()->user()->tenant->name : 'CoopAdmin' }}</div>
      <div class="logo-sub">Savings & Loans Admin</div>
    </div>
  </div>
  <nav class="sidebar-nav">
    @if(auth()->check() && auth()->user()->role === 'super_admin')
      <div class="nav-section">Global SaaS</div>
      <a href="/super-admin" class="nav-item {{ request()->routeIs('super-admin') ? 'active' : '' }}" wire:navigate><span class="nav-icon">🛡️</span> Super Admin Panel</a>
    @endif

    <div class="nav-section">Overview</div>
    <a href="/dashboard" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" wire:navigate><span class="nav-icon">◈</span> Dashboard</a>

    <div class="nav-section">Management</div>
    <a href="/users" class="nav-item {{ request()->routeIs('users') ? 'active' : '' }}" wire:navigate><span class="nav-icon">⊕</span> Users</a>
    <a href="/books" class="nav-item {{ request()->routeIs('books') ? 'active' : '' }}" wire:navigate><span class="nav-icon">▣</span> Books</a>
    <a href="/contributions" class="nav-item {{ request()->routeIs('contributions') ? 'active' : '' }}" wire:navigate><span class="nav-icon">◎</span> Contributions</a>
    <a href="/loans" class="nav-item {{ request()->routeIs('loans') ? 'active' : '' }}" wire:navigate><span class="nav-icon">◈</span> Loans <span class="nav-badge">4</span></a>

    <div class="nav-section">Finance</div>
    <a href="/payments" class="nav-item {{ request()->routeIs('payments') ? 'active' : '' }}" wire:navigate><span class="nav-icon">▷</span> Payments</a>
    <a href="/defaulters" class="nav-item {{ request()->routeIs('defaulters') ? 'active' : '' }}" wire:navigate><span class="nav-icon">◉</span> Defaulters <span class="nav-badge">7</span></a>
    <a href="/reports" class="nav-item {{ request()->routeIs('reports') ? 'active' : '' }}" wire:navigate><span class="nav-icon">◫</span> Reports</a>

    <div class="nav-section">System</div>
    <a href="/notifications" class="nav-item {{ request()->routeIs('notifications') ? 'active' : '' }}" wire:navigate><span class="nav-icon">◌</span> Notifications</a>
    <a href="/settings" class="nav-item {{ request()->routeIs('settings') ? 'active' : '' }}" wire:navigate><span class="nav-icon">⊛</span> Settings</a>
  </nav>
  <div class="sidebar-footer">
    @auth
    <div class="admin-card">
      <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
      <div style="flex:1;min-width:0;">
        <div class="admin-name" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ auth()->user()->name }}</div>
        <div class="admin-role">{{ ucfirst(auth()->user()->role ?? 'Administrator') }}</div>
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
        'super-admin'    => ['Super Admin Dashboard', 'Global SaaS Metrics & Multi-Tenant Management'],
        'dashboard'      => ['Dashboard', 'Overview & Key Metrics'],
        'users'          => ['User Management', 'View · Search · Block'],
        'users.add'      => ['Add New User', 'Create a new member profile'],
        'books'          => ['Book Management', 'Assign · Deactivate · Monitor'],
        'books.add'      => ['Add Book', 'Create or assign a new passbook'],
        'contributions'  => ['Contributions', 'Weekly Collection Tracker'],
        'contributions.add' => ['Record Contribution', 'Add a new weekly payment'],
        'loans'          => ['Loan Management', 'Approve · Track · Recover'],
        'loans.add'      => ['Issue / Request Loan', 'Create a new loan application'],
        'payments'       => ['Payments & Transactions', 'Audit Trail'],
        'defaulters'     => ['Defaulters', 'Missed Contributions & Overdue Loans'],
        'reports'        => ['Reports & Profit Sharing', 'Year-End Analytics'],
        'notifications'  => ['Notifications', 'SMS & Alerts Panel'],
        'settings'       => ['Settings', 'System Configuration'],
      ];
      $currentRoute = Route::currentRouteName();
      $pageInfo = $routePageMap[$currentRoute] ?? ['CoopAdmin', 'Savings & Loans Management'];
    @endphp
    <div class="page-title" id="topbar-title">{{ $pageInfo[0] }}</div>
    <div class="page-subtitle" id="topbar-sub">{{ $pageInfo[1] }}</div>
  </div>
  <div class="topbar-right">
    <div class="search-box">
      <span style="color:var(--text3);font-size:12px">⌕</span>
      <input type="text" placeholder="Search anything…">
    </div>
    <a href="/notifications" class="icon-btn {{ auth()->user()->unreadNotifications->count() > 0 ? 'notif-dot' : '' }}" wire:navigate style="text-decoration:none; position:relative;">
      🔔
      @if(auth()->user()->unreadNotifications->count() > 0)
        <span style="position:absolute; top:-5px; right:-5px; background:var(--danger); color:#fff; font-size:9px; font-weight:600; min-width:14px; height:14px; border-radius:10px; display:flex; align-items:center; justify-content:center; border:2px solid var(--bg2);">
          {{ auth()->user()->unreadNotifications->count() }}
        </span>
      @endif
    </a>
    <a href="/settings" class="icon-btn" wire:navigate style="text-decoration:none;">⚙</a>
  </div>
</header>

<!-- MAIN CONTENT -->
<main class="main">
    {{ $slot ?? '' }}
    @yield('content')
</main>

<script>
// ── PAGE NAVIGATION ──────────────────────────────────────
const pageTitles = {
  dashboard: ['Dashboard','Overview & Key Metrics'],
  users: ['User Management','View · Search · Block'],
  books: ['Book Management','Assign · Deactivate · Monitor'],
  contributions: ['Contributions','Weekly Collection Tracker'],
  loans: ['Loan Management','Approve · Track · Recover'],
  payments: ['Payments & Transactions','Audit Trail'],
  defaulters: ['Defaulters','Missed Contributions & Overdue Loans'],
  reports: ['Reports & Profit Sharing','Year-End Analytics'],
  notifications: ['Notifications','SMS & Alerts Panel'],
  settings: ['Settings','System Configuration']
};

function showPage(id, el) {
  // Graceful fallback for remaining onclick handlers across the app
  if (typeof Livewire !== 'undefined') {
    Livewire.navigate('/' + id);
  } else {
    window.location.href = '/' + id;
  }
}

function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('overlay').classList.toggle('show');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('overlay').classList.remove('show');
}

// ── TABLE FILTER ─────────────────────────────────────────
function filterTable(tableId, q) {
  const rows = document.querySelectorAll('#' + tableId + ' tbody tr');
  rows.forEach(r => {
    r.style.display = r.textContent.toLowerCase().includes(q.toLowerCase()) ? '' : 'none';
  });
}

// ── TOGGLE ───────────────────────────────────────────────
function toggleBtn(btn) {
  btn.classList.toggle('on'); btn.classList.toggle('off');
}

// ── USER ACTIONS ─────────────────────────────────────────
function viewUser(name) {
  Swal.fire({
    background: '#161b22', color: '#e6edf3',
    title: `<span style="font-size:15px">${name}</span>`,
    html: `<div style="text-align:left;font-size:12px;line-height:1.8">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:8px 0">
        <div style="background:#1c2230;padding:10px;border-radius:8px"><div style="color:#8b949e;font-size:10px">Books Owned</div><div style="font-size:16px;font-weight:600;color:#00d4a8">3</div></div>
        <div style="background:#1c2230;padding:10px;border-radius:8px"><div style="color:#8b949e;font-size:10px">Total Contributions</div><div style="font-size:16px;font-weight:600;color:#3fb950">GH₵ 1,440</div></div>
        <div style="background:#1c2230;padding:10px;border-radius:8px"><div style="color:#8b949e;font-size:10px">Active Loans</div><div style="font-size:16px;font-weight:600;color:#bc8cff">1</div></div>
        <div style="background:#1c2230;padding:10px;border-radius:8px"><div style="color:#8b949e;font-size:10px">Penalties Paid</div><div style="font-size:16px;font-weight:600;color:#f85149">GH₵ 0</div></div>
      </div>
      <div style="margin-top:8px;padding:8px;background:#1c2230;border-radius:8px;color:#8b949e">Last activity: <strong style="color:#e6edf3">Apr 3, 2026 09:14</strong></div>
    </div>`,
    confirmButtonText: 'Close',
    confirmButtonColor: '#00d4a8',
    showCancelButton: true,
    cancelButtonText: 'Block User',
    cancelButtonColor: '#f85149',
  });
}

function blockUser(name) {
  Swal.fire({
    background: '#161b22', color: '#e6edf3',
    title: 'Block User?',
    html: `<span style="font-size:12px">Are you sure you want to block <strong>${name}</strong>? They will not be able to log in.</span>`,
    icon: 'warning', iconColor: '#d29922',
    showCancelButton: true,
    confirmButtonText: 'Yes, Block',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#f85149',
    cancelButtonColor: '#21262d',
  }).then(r => {
    if (r.isConfirmed) Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'success', iconColor:'#3fb950', title:'Blocked', text:`${name} has been blocked.`, confirmButtonColor:'#00d4a8', timer:2000, timerProgressBar:true });
  });
}

function unblockUser(name) {
  Swal.fire({
    background: '#161b22', color: '#e6edf3',
    icon: 'question', iconColor: '#00d4a8',
    title: 'Unblock User?',
    html: `<span style="font-size:12px">Restore access for <strong>${name}</strong>?</span>`,
    showCancelButton: true, confirmButtonText: 'Yes, Unblock', confirmButtonColor: '#00d4a8', cancelButtonColor: '#21262d',
  }).then(r => {
    if (r.isConfirmed) Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'success', iconColor:'#3fb950', title:'Unblocked', text:`${name} can now log in.`, confirmButtonColor:'#00d4a8', timer:2000, timerProgressBar:true });
  });
}

function addUser() {
  Swal.fire({
    background: '#161b22', color: '#e6edf3',
    title: '<span style="font-size:15px">Add New User</span>',
    html: `<div style="display:flex;flex-direction:column;gap:8px;text-align:left">
      <input id="swal-name" class="swal2-input" placeholder="Full Name" style="background:#1c2230;border:1px solid rgba(255,255,255,0.1);color:#e6edf3;font-size:12px">
      <input id="swal-phone" class="swal2-input" placeholder="Phone Number" style="background:#1c2230;border:1px solid rgba(255,255,255,0.1);color:#e6edf3;font-size:12px">
      <input id="swal-email" class="swal2-input" placeholder="Email Address" style="background:#1c2230;border:1px solid rgba(255,255,255,0.1);color:#e6edf3;font-size:12px">
    </div>`,
    confirmButtonText: 'Create User', confirmButtonColor: '#00d4a8',
    showCancelButton: true, cancelButtonColor: '#21262d',
    preConfirm: () => {
      const name = document.getElementById('swal-name').value;
      if (!name) { Swal.showValidationMessage('Please enter a name'); return false; }
      return name;
    }
  }).then(r => {
    if (r.isConfirmed) Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'success', iconColor:'#3fb950', title:'User Created!', text:`${r.value} has been added.`, confirmButtonColor:'#00d4a8', timer:2500, timerProgressBar:true });
  });
}

// ── BOOK ACTIONS ─────────────────────────────────────────
function viewBook(id) {
  Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'info', iconColor:'#388bfd', title:`Book ${id}`, html:`<span style="font-size:12px">Total Contributions: <strong>GH₵ 480</strong> · Status: Active · Weeks: 4</span>`, confirmButtonColor:'#00d4a8' });
}
function deactivateBook(id) {
  Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'warning', iconColor:'#d29922', title:`Deactivate ${id}?`, html:`<span style="font-size:12px">This will stop future contributions to this book.</span>`, showCancelButton:true, confirmButtonText:'Deactivate', confirmButtonColor:'#f85149', cancelButtonColor:'#21262d' }).then(r => { if(r.isConfirmed) Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'success', iconColor:'#3fb950', title:'Deactivated', confirmButtonColor:'#00d4a8', timer:2000, timerProgressBar:true }); });
}
function activateBook(id) {
  Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'success', iconColor:'#00d4a8', title:`Activate ${id}?`, showCancelButton:true, confirmButtonText:'Activate', confirmButtonColor:'#00d4a8', cancelButtonColor:'#21262d' }).then(r => { if(r.isConfirmed) Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'success', iconColor:'#3fb950', title:'Activated!', confirmButtonColor:'#00d4a8', timer:2000, timerProgressBar:true }); });
}
function assignBook() {
  Swal.fire({ background:'#161b22', color:'#e6edf3', title:'Assign Book', html:`<select class="swal2-select" style="background:#1c2230;border:1px solid rgba(255,255,255,0.1);color:#e6edf3;font-size:12px"><option>Kofi Asante</option><option>Ama Mensah</option><option>Kwame Darko</option></select>`, confirmButtonText:'Assign', confirmButtonColor:'#00d4a8', showCancelButton:true, cancelButtonColor:'#21262d' }).then(r => { if(r.isConfirmed) Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'success', iconColor:'#3fb950', title:'Book Assigned!', confirmButtonColor:'#00d4a8', timer:2000, timerProgressBar:true }); });
}

// ── CONTRIBUTION ACTIONS ──────────────────────────────────
function recordContrib() {
  Swal.fire({ background:'#161b22', color:'#e6edf3', title:'Record Contribution', html:`<div style="display:flex;flex-direction:column;gap:8px"><select class="swal2-select" style="background:#1c2230;border:1px solid rgba(255,255,255,0.1);color:#e6edf3;font-size:12px"><option>Kofi Asante — BK-001</option><option>Ama Mensah — BK-003</option></select><input class="swal2-input" value="120" style="background:#1c2230;border:1px solid rgba(255,255,255,0.1);color:#e6edf3;font-size:12px"></div>`, confirmButtonText:'Record', confirmButtonColor:'#00d4a8', showCancelButton:true, cancelButtonColor:'#21262d' }).then(r => { if(r.isConfirmed) Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'success', iconColor:'#3fb950', title:'Contribution Recorded!', confirmButtonColor:'#00d4a8', timer:2000, timerProgressBar:true }); });
}

// ── LOAN ACTIONS ──────────────────────────────────────────
function viewLoan(id) {
  Swal.fire({ background:'#161b22', color:'#e6edf3', title:`Loan ${id}`, html:`<div style="text-align:left;font-size:12px;display:grid;grid-template-columns:1fr 1fr;gap:8px"><div style="background:#1c2230;padding:10px;border-radius:8px"><div style="color:#8b949e;font-size:10px">Principal</div><div style="color:#bc8cff;font-weight:600">GH₵ 1,200</div></div><div style="background:#1c2230;padding:10px;border-radius:8px"><div style="color:#8b949e;font-size:10px">Interest (10%)</div><div style="color:#d29922;font-weight:600">GH₵ 120</div></div><div style="background:#1c2230;padding:10px;border-radius:8px"><div style="color:#8b949e;font-size:10px">Total Due</div><div style="color:#e6edf3;font-weight:600">GH₵ 1,320</div></div><div style="background:#1c2230;padding:10px;border-radius:8px"><div style="color:#8b949e;font-size:10px">Repaid</div><div style="color:#3fb950;font-weight:600">GH₵ 528</div></div></div>`, confirmButtonColor:'#00d4a8' });
}
function approveLoan(id, name) {
  Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'question', iconColor:'#00d4a8', title:`Approve ${id}?`, html:`<span style="font-size:12px">Approve loan for <strong>${name}</strong>?</span>`, showCancelButton:true, confirmButtonText:'Approve ✓', confirmButtonColor:'#00d4a8', cancelButtonColor:'#21262d' }).then(r => { if(r.isConfirmed) Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'success', iconColor:'#3fb950', title:'Loan Approved!', text:`Funds will be disbursed to ${name}.`, confirmButtonColor:'#00d4a8', timer:3000, timerProgressBar:true }); });
}
function rejectLoan(id, name) {
  Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'warning', iconColor:'#f85149', title:`Reject ${id}?`, input:'textarea', inputPlaceholder:'Reason for rejection…', inputAttributes:{style:'background:#1c2230;color:#e6edf3;border:1px solid rgba(255,255,255,0.1);font-size:12px'}, showCancelButton:true, confirmButtonText:'Reject', confirmButtonColor:'#f85149', cancelButtonColor:'#21262d' }).then(r => { if(r.isConfirmed) Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'error', iconColor:'#f85149', title:'Loan Rejected', text:`${name} has been notified.`, confirmButtonColor:'#00d4a8', timer:2500, timerProgressBar:true }); });
}
function markOverdue(id) {
  Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'warning', iconColor:'#d29922', title:'Send Reminder?', html:`<span style="font-size:12px">Send overdue reminder for ${id}?</span>`, showCancelButton:true, confirmButtonText:'Send SMS', confirmButtonColor:'#00d4a8', cancelButtonColor:'#21262d' }).then(r => { if(r.isConfirmed) Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'success', iconColor:'#3fb950', title:'Reminder Sent!', confirmButtonColor:'#00d4a8', timer:2000, timerProgressBar:true }); });
}

// ── DEFAULTER ACTIONS ────────────────────────────────────
function sendReminder(name) {
  Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'info', iconColor:'#388bfd', title:'Send Reminder', html:`<span style="font-size:12px">Send SMS reminder to <strong>${name}</strong>?</span>`, showCancelButton:true, confirmButtonText:'Send Now', confirmButtonColor:'#00d4a8', cancelButtonColor:'#21262d' }).then(r => { if(r.isConfirmed) Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'success', iconColor:'#3fb950', title:'Reminder Sent!', text:`SMS delivered to ${name}.`, confirmButtonColor:'#00d4a8', timer:2000, timerProgressBar:true }); });
}
function applyPenalty(name) {
  Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'warning', iconColor:'#f85149', title:'Apply Penalty?', html:`<span style="font-size:12px">Apply a <strong>5% penalty</strong> to <strong>${name}</strong> for missed contribution?</span>`, showCancelButton:true, confirmButtonText:'Apply Penalty', confirmButtonColor:'#f85149', cancelButtonColor:'#21262d' }).then(r => { if(r.isConfirmed) Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'success', iconColor:'#3fb950', title:'Penalty Applied', text:`GH₵ 6 penalty added to ${name}'s account.`, confirmButtonColor:'#00d4a8', timer:2500, timerProgressBar:true }); });
}
function sendBulkReminders() {
  Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'question', iconColor:'#00d4a8', title:'Send Bulk Reminders?', html:`<span style="font-size:12px">This will send an SMS reminder to all <strong>7 defaulters</strong>. Continue?</span>`, showCancelButton:true, confirmButtonText:'Send to All', confirmButtonColor:'#00d4a8', cancelButtonColor:'#21262d' }).then(r => { if(r.isConfirmed) Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'success', iconColor:'#3fb950', title:'Reminders Sent!', text:'7 SMS messages dispatched successfully.', confirmButtonColor:'#00d4a8', timer:3000, timerProgressBar:true }); });
}

// ── REPORT ACTIONS ───────────────────────────────────────
function generatePDF() {
  Swal.fire({ background:'#161b22', color:'#e6edf3', title:'Generating PDF…', html:`<span style="font-size:12px">Your payout report is being prepared.</span>`, timer:2000, timerProgressBar:true, didOpen: () => Swal.showLoading(), showConfirmButton:false }).then(() => Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'success', iconColor:'#3fb950', title:'PDF Ready!', text:'Download has started.', confirmButtonColor:'#00d4a8', timer:2000, timerProgressBar:true }));
}
function exportCSV() {
  Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'success', iconColor:'#3fb950', title:'CSV Exported!', text:'File saved to downloads.', confirmButtonColor:'#00d4a8', timer:2000, timerProgressBar:true });
}

// ── NOTIFICATION ACTIONS ─────────────────────────────────
function setTemplate(val) {
  const templates = {
    weekly: 'Dear member, this is a reminder to make your weekly contribution of GH₵ 120. Kindly pay before Sunday. Thank you.',
    overdue: 'Dear member, your loan repayment is overdue. Please make payment immediately to avoid further penalties. Thank you.',
    penalty: 'Dear member, a penalty has been applied to your account for a missed contribution. Please contact admin for details.',
    payout: 'Dear member, the year-end profit sharing has been calculated. Please visit your nearest branch or contact admin for your payout details.'
  };
  document.getElementById('msgArea').value = templates[val] || '';
}
function sendBulkSMS() {
  Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'question', iconColor:'#00d4a8', title:'Send Notification?', html:`<span style="font-size:12px">This will send the message to the selected recipients. Confirm?</span>`, showCancelButton:true, confirmButtonText:'Send Now', confirmButtonColor:'#00d4a8', cancelButtonColor:'#21262d' }).then(r => { if(r.isConfirmed) Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'success', iconColor:'#3fb950', title:'Sent!', text:'Your notification has been dispatched.', confirmButtonColor:'#00d4a8', timer:2500, timerProgressBar:true }); });
}
function scheduleMsg() {
  Swal.fire({ background:'#161b22', color:'#e6edf3', title:'Schedule Message', input:'datetime-local', inputAttributes:{style:'background:#1c2230;color:#e6edf3;border:1px solid rgba(255,255,255,0.1);font-size:12px'}, showCancelButton:true, confirmButtonText:'Schedule', confirmButtonColor:'#00d4a8', cancelButtonColor:'#21262d' }).then(r => { if(r.isConfirmed && r.value) Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'success', iconColor:'#3fb950', title:'Scheduled!', text:`Message scheduled for ${new Date(r.value).toLocaleString()}.`, confirmButtonColor:'#00d4a8', timer:3000, timerProgressBar:true }); });
}

// ── SETTINGS ACTIONS ─────────────────────────────────────
function saveSettings() {
  Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'success', iconColor:'#3fb950', title:'Settings Saved!', text:'Your changes have been applied.', confirmButtonColor:'#00d4a8', timer:2000, timerProgressBar:true });
}
function changePassword() {
  Swal.fire({ background:'#161b22', color:'#e6edf3', title:'Change Password', html:`<div style="display:flex;flex-direction:column;gap:8px"><input type="password" class="swal2-input" placeholder="Current password" style="background:#1c2230;border:1px solid rgba(255,255,255,0.1);color:#e6edf3;font-size:12px"><input type="password" class="swal2-input" placeholder="New password" style="background:#1c2230;border:1px solid rgba(255,255,255,0.1);color:#e6edf3;font-size:12px"></div>`, showCancelButton:true, confirmButtonText:'Update', confirmButtonColor:'#00d4a8', cancelButtonColor:'#21262d' }).then(r => { if(r.isConfirmed) Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'success', iconColor:'#3fb950', title:'Password Updated!', confirmButtonColor:'#00d4a8', timer:2000, timerProgressBar:true }); });
}
function confirmLogout() {
  Swal.fire({ background:'#161b22', color:'#e6edf3', icon:'question', iconColor:'#d29922', title:'Logout?', html:`<span style="font-size:12px">Are you sure you want to end your session?</span>`, showCancelButton:true, confirmButtonText:'Logout', confirmButtonColor:'#f85149', cancelButtonColor:'#21262d' }).then(r => { if(r.isConfirmed) Swal.fire({ background:'#161b22', color:'#e6edf3', title:'Logged out.', timer:1500, showConfirmButton:false, timerProgressBar:true }); });
}

// ── CHARTS ───────────────────────────────────────────────
let contribChartInst, donutChartInst, reportChartInst;

function initCharts() {
  if (contribChartInst) return;
  const el = document.getElementById('contribChart');
  if (!el) return;

  Chart.defaults.color = '#8b949e';
  Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';

  const ctxC = el.getContext('2d');
  contribChartInst = new Chart(ctxC, {
    type: 'bar',
    data: {
      labels: ['Wk 7','Wk 8','Wk 9','Wk 10','Wk 11','Wk 12','Wk 13','Wk 14'],
      datasets: [{
        label: 'Contributions',
        data: [5400, 5760, 6120, 5880, 6240, 6000, 5760, 6240],
        backgroundColor: 'rgba(0,212,168,0.25)',
        borderColor: '#00d4a8',
        borderWidth: 1.5,
        borderRadius: 4,
      },{
        label: 'Welfare',
        data: [540,576,612,588,624,600,576,624],
        backgroundColor: 'rgba(56,139,253,0.15)',
        borderColor: '#388bfd',
        borderWidth: 1.5,
        borderRadius: 4,
      }]
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ labels:{ font:{size:10}, boxWidth:10 } } }, scales:{ y:{ ticks:{ font:{size:10}, callback: v => 'GH₵ '+v/1000+'K' } }, x:{ ticks:{ font:{size:10} } } } }
  });

  const elD = document.getElementById('donutChart');
  if (!elD) return;
  const ctxD = elD.getContext('2d');
  donutChartInst = new Chart(ctxD, {
    type: 'doughnut',
    data: {
      labels: ['Contributions','Welfare','Penalties'],
      datasets: [{ data:[39000,4820,375], backgroundColor:['rgba(0,212,168,0.7)','rgba(56,139,253,0.7)','rgba(248,81,73,0.7)'], borderColor:['#00d4a8','#388bfd','#f85149'], borderWidth:1.5, hoverOffset:4 }]
    },
    options: { responsive:true, maintainAspectRatio:false, cutout:'65%', plugins:{ legend:{ position:'bottom', labels:{ font:{size:10}, boxWidth:10, padding:8 } } } }
  });
}

function initReportChart() {
  if (reportChartInst) return;
  const el = document.getElementById('reportChart');
  if (!el) return;
  const ctx = el.getContext('2d');
  reportChartInst = new Chart(ctx, {
    type: 'line',
    data: {
      labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
      datasets: [{
        label: 'Contributions',
        data: [4200,4440,4680,4800,4920,5160,5400,5640,5760,5880,6000,6240],
        borderColor:'#00d4a8', backgroundColor:'rgba(0,212,168,0.08)', tension:.4, fill:true, borderWidth:2, pointRadius:3
      },{
        label: 'Loan Interest',
        data: [300,320,440,500,560,620,700,760,800,840,880,900],
        borderColor:'#d29922', backgroundColor:'rgba(210,153,34,0.08)', tension:.4, fill:true, borderWidth:2, pointRadius:3
      }]
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ labels:{ font:{size:10}, boxWidth:10 } } }, scales:{ y:{ ticks:{ font:{size:10}, callback: v => 'GH₵ '+v/1000+'K' } }, x:{ ticks:{ font:{size:10} } } } }
  });
}

// Init on load
window.onload = () => { initCharts(); };
</script>
</body>
</html>