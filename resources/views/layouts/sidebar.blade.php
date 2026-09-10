<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>REKS System | @yield('title', 'Dashboard')</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

{{-- Page-specific extra <link> tags (extra fonts, etc.) go here --}}
@stack('head')

<style>
  :root{
    --pink:#FF5C85;
    --pink-dark:#D63E63;
    --pink-deep:#B82850;
    --pink-light:#FFE3EB;
    --pink-pale:#FFF2F6;
    --ink:#1A1523;
    --ink-soft:#635C72;
    --muted:#9C94AB;
    --line:#EEE8F0;
    --line-strong:#DCD3E2;
    --bg:#F3EFF3;
    --card:#FFFFFF;
    --green:#1FAE9E;
    --green-light:#E4F8F4;
    --amber:#F2932A;
    --amber-light:#FFF3E2;
    --present:#1FAE9E;
    --present-soft:#E3F6F3;
    --deduct:#D63E63;
    --deduct-soft:#FFE3EB;
    --rest:#C9C2D6;
    --shadow-sm:0 1px 3px rgba(26,21,35,.06), 0 1px 2px rgba(26,21,35,.04);
    --shadow-md:0 10px 28px rgba(26,21,35,.09);
    --shadow-pink:0 16px 36px rgba(214,62,99,.28);
  }
  *{box-sizing:border-box;margin:0;padding:0;}
  body{background:var(--bg);font-family:'Inter',sans-serif;color:var(--ink);}
  a{text-decoration:none;color:inherit;}

  .app-shell{display:grid;grid-template-columns:236px 1fr;min-height:100vh;}

  /* ===== MOBILE TOPBAR ===== */
  .mobile-topbar{
    display:none;
    align-items:center;justify-content:space-between;
    background:var(--ink);padding:14px 18px;
    position:sticky;top:0;z-index:40;
  }
  .mobile-topbar img{height:32px;}
  .hamburger-btn{
    width:38px;height:38px;border-radius:10px;
    background:rgba(255,255,255,.08);border:none;
    color:#fff;font-size:1.05rem;
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;
  }

  .sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(26,21,35,.5);z-index:45;}
  .sidebar-overlay.active{display:block;}

  /* ===== SIDEBAR ===== */
  .sidebar{
    background:var(--ink);
    display:flex;flex-direction:column;
    position:sticky;top:0;height:100vh;
  }
  .sidebar-brand{padding:20px 10px;display:flex;align-items:center;justify-content:center;}
  .sidebar-brand .mark{
    width:210px;height:84px;border-radius:12px;
    display:flex;align-items:center;justify-content:center;
    padding:2px;flex-shrink:0;
  }
  .sidebar-brand .mark img{width:100%;height:100%;object-fit:contain;}

  .sidebar-nav{flex:1;overflow-y:auto;padding:10px 14px;display:flex;flex-direction:column;gap:1px;scrollbar-width:none;-ms-overflow-style:none;}
  .sidebar-nav::-webkit-scrollbar{width:0;height:0;display:none;}
  .nav-label{font-size:.65rem;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:#6E6680;padding:16px 12px 6px;}
  .sidebar-nav a{
    display:flex;align-items:center;gap:11px;
    padding:10px 12px;border-radius:10px;
    color:#C3BDD0;font-weight:500;font-size:.86rem;
    transition:.15s;
  }
  .sidebar-nav a i{width:16px;text-align:center;font-size:.82rem;color:#7A7290;transition:.15s;}
  .sidebar-nav a:hover{background:rgba(255,92,133,.12);color:#FFB3C6;}
  .sidebar-nav a:hover i{color:var(--pink);}
  .sidebar-nav a.active{background:rgba(255,92,133,.14);color:#fff;font-weight:700;}
  .sidebar-nav a.active i{color:var(--pink);}

  .sidebar-footer{padding:16px;border-top:1px solid rgba(255,255,255,.08);}
  .sidebar-user{display:flex;align-items:center;gap:11px;padding:8px 6px 14px;}
  .sidebar-user .avatar{
    width:36px;height:36px;border-radius:11px;
    background:linear-gradient(135deg, var(--pink) 0%, var(--pink-deep) 100%);
    display:flex;align-items:center;justify-content:center;
    color:#fff;font-weight:700;font-size:.88rem;flex-shrink:0;
  }
  .sidebar-user .name{color:#fff;font-weight:600;font-size:.82rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:130px;}
  .sidebar-user .role{color:#8A8299;font-size:.72rem;text-transform:capitalize;}
  .sidebar-logout{
    display:flex;align-items:center;gap:10px;padding:10px 12px;
    border-radius:10px;color:#C3BDD0;font-weight:500;font-size:.85rem;transition:.15s;
  }
  .sidebar-logout i{width:16px;text-align:center;color:#7A7290;}
  .sidebar-logout:hover{background:rgba(255,92,133,.12);color:#FFB3C6;}
  .sidebar-logout:hover i{color:var(--pink);}

  .content-body{padding:clamp(20px, 3vw, 40px);max-width:1320px;}

  @media(max-width:900px){
    .app-shell{display:block;}
    .mobile-topbar{display:flex;}
    .sidebar{
      position:fixed;top:0;left:0;height:100vh;width:260px;
      transform:translateX(-100%);transition:transform .25s ease;
      z-index:50;box-shadow:0 0 40px rgba(0,0,0,.3);
    }
    .sidebar.open{transform:translateX(0);}
    .content-body{padding:18px;}
  }
  @media(max-width:520px){
    .sidebar-brand .mark{width:170px;height:68px;}
  }
</style>

{{-- Page-specific styles (metrics grid, tables, modals, etc.) --}}
@yield('styles')
</head>
<body>

<div class="mobile-topbar no-print">
  <img src="{{ asset('images/reks-logo-white.png') }}" alt="REKS">
  <button class="hamburger-btn" id="hamburgerBtn" aria-label="Open menu"><i class="fa-solid fa-bars"></i></button>
</div>

<div class="sidebar-overlay no-print" id="sidebarOverlay"></div>

<div class="app-shell">

  <aside class="sidebar no-print" id="sidebar">
    <div class="sidebar-brand">
      <div class="mark"><img src="{{ asset('images/reks-logo-white.png') }}" alt="REKS"></div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-label">Operations</div>
      @if(session('role') === 'cashier')
        <a href="/pos" class="{{ request()->is('pos') ? 'active' : '' }}"><i class="fa-solid fa-cash-register"></i> Point of Sale</a>
        <a href="/inventory" class="{{ request()->is('inventory') ? 'active' : '' }}"><i class="fa-solid fa-boxes-stacked"></i> Inventory</a>
      @else
        <a href="/inventory" class="{{ request()->is('inventory') ? 'active' : '' }}"><i class="fa-solid fa-boxes-stacked"></i> Inventory</a>

        <div class="nav-label">People</div>
        <a href="/customer" class="{{ request()->is('customer') ? 'active' : '' }}"><i class="fa-solid fa-users"></i> Customer Management</a>
        <a href="{{ route('payment-verification') }}" class="{{ request()->routeIs('payment-verification') ? 'active' : '' }}"><i class="fa-solid fa-money-check"></i> Payment Verification</a>
        <a href="{{ route('attendance') }}" class="{{ request()->routeIs('attendance') ? 'active' : '' }}"><i class="fa-solid fa-clipboard-check"></i> Attendance</a>
        <a href="{{ route('salary') }}" class="{{ request()->routeIs('salary') ? 'active' : '' }}"><i class="fa-solid fa-money-check-dollar"></i> Salary and Deductions</a>
        <a href="{{ route('payroll-history') }}" class="{{ request()->routeIs('payroll-history') ? 'active' : '' }}"><i class="fa-solid fa-file-invoice-dollar"></i> Payroll and Payslip</a>
        <a href="/accounts/create" class="{{ request()->is('accounts/create') ? 'active' : '' }}"><i class="fa-solid fa-user-plus"></i> Account Management</a>

        <div class="nav-label">Insights</div>
        <a href="/fieldOfRides" class="{{ request()->is('fieldOfRides') ? 'active' : '' }}"><i class="fa-solid fa-ferris-wheel"></i> Field of Rides</a>
        <a href="/RollerFever" class="{{ request()->is('RollerFever') ? 'active' : '' }}"><i class="fa-solid fa-bolt"></i> Roller Fever</a>
        <a href="/DinoAdventure" class="{{ request()->is('DinoAdventure') ? 'active' : '' }}"><i class="fa-solid fa-dragon"></i> Dino Adventure</a>
        @if(session('role') === 'admin')
          <a href="/ml-forecast" class="{{ request()->is('ml-forecast') ? 'active' : '' }}"><i class="fa-solid fa-brain"></i> Sales Forecast</a>
        @endif
      @endif
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="avatar">{{ strtoupper(substr(session('fullname', 'U'), 0, 1)) }}</div>
        <div>
          <div class="name">{{ session('fullname') }}</div>
          <div class="role">{{ session('role') }}</div>
        </div>
      </div>
      <a href="/logout" class="sidebar-logout">
        <i class="fas fa-sign-out-alt"></i> Log out
      </a>
    </div>
  </aside>

  <div class="main-content">
    <div class="content-body">
      @yield('content')
    </div>
  </div>

</div>

<script>
  (function(){
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if(!hamburgerBtn) return;

    function openSidebar(){
      sidebar.classList.add('open');
      overlay.classList.add('active');
    }
    function closeSidebar(){
      sidebar.classList.remove('open');
      overlay.classList.remove('active');
    }
    hamburgerBtn.addEventListener('click', openSidebar);
    overlay.addEventListener('click', closeSidebar);
    document.querySelectorAll('.sidebar-nav a').forEach(link => link.addEventListener('click', closeSidebar));
  })();
</script>

{{-- Page-specific scripts --}}
@stack('scripts')

</body>
</html>