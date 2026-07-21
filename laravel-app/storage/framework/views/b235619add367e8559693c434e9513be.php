<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>WonderPark System | <?php echo $__env->yieldContent('title', 'Dashboard'); ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    
    <?php echo $__env->yieldPushContent('head'); ?>

    <style>
        :root {
            --pink: #FF5C85;
            --pink-dark: #D63E63;
            --pink-deep: #B82850;
            --pink-light: #FFE3EB;
            --pink-pale: #FFF2F6;
            --ink: #1A1523;
            --ink-soft: #635C72;
            --muted: #9C94AB;
            --line: #EEE8F0;
            --line-strong: #DCD3E2;
            --bg: #F3EFF3;
            --card: #FFFFFF;
            --green: #1FAE9E;
            --green-light: #E4F8F4;
            --amber: #F2932A;
            --amber-light: #FFF3E2;
            --present: #1FAE9E;
            --present-soft: #E3F6F3;
            --deduct: #D63E63;
            --deduct-soft: #FFE3EB;
            --rest: #C9C2D6;
            --shadow-sm: 0 1px 3px rgba(26, 21, 35, .06), 0 1px 2px rgba(26, 21, 35, .04);
            --shadow-md: 0 10px 28px rgba(26, 21, 35, .09);
            --shadow-pink: 0 16px 36px rgba(214, 62, 99, .28);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: var(--bg);
            font-family: 'Inter', sans-serif;
            color: var(--ink);
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .app-shell {
            display: grid;
            grid-template-columns: 236px 1fr;
            min-height: 100vh;
        }

        /* ===== MOBILE TOPBAR ===== */
        .mobile-topbar {
            display: none;
            align-items: center;
            justify-content: space-between;
            background: var(--ink);
            padding: 14px 18px;
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .mobile-topbar img {
            height: 32px;
        }

        .hamburger-btn {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: rgba(255, 255, 255, .08);
            border: none;
            color: #fff;
            font-size: 1.05rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(26, 21, 35, .5);
            z-index: 45;
        }

        .sidebar-overlay.active {
            display: block;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            background: var(--ink);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .sidebar-brand {
            padding: 20px 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-brand .mark {
            width: 210px;
            height: 84px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2px;
            flex-shrink: 0;
        }

        .sidebar-brand .mark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 10px 14px;
            display: flex;
            flex-direction: column;
            gap: 1px;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 0;
            height: 0;
            display: none;
        }

        .nav-label {
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .09em;
            text-transform: uppercase;
            color: #6E6680;
            padding: 16px 12px 6px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 12px;
            border-radius: 10px;
            color: #C3BDD0;
            font-weight: 500;
            font-size: .86rem;
            transition: .15s;
        }

        .sidebar-nav a i {
            width: 16px;
            text-align: center;
            font-size: .82rem;
            color: #7A7290;
            transition: .15s;
        }

        .sidebar-nav a:hover {
            background: rgba(255, 92, 133, .12);
            color: #FFB3C6;
        }

        .sidebar-nav a:hover i {
            color: var(--pink);
        }

        .sidebar-nav a.active {
            background: rgba(255, 92, 133, .14);
            color: #fff;
            font-weight: 700;
        }

        .sidebar-nav a.active i {
            color: var(--pink);
        }

        /* ===== NESTED SUBMENUS (People / Manpower / Sales Forecast groups) ===== */
        .nav-group {
            display: flex;
            flex-direction: column;
        }

        .nav-parent-row {
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .nav-parent-row .nav-parent-link {
            flex: 1;
        }

        .nav-caret-btn {
            width: 30px;
            height: 30px;
            flex-shrink: 0;
            border: none;
            background: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            color: #7A7290;
            transition: .15s;
        }

        .nav-caret-btn:hover {
            background: rgba(255, 92, 133, .12);
            color: var(--pink);
        }

        .nav-caret-btn i {
            transition: transform .18s ease;
        }

        .nav-caret-btn.open i {
            transform: rotate(180deg);
        }

        .nav-submenu {
            max-height: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            gap: 1px;
            transition: max-height .2s ease;
        }

        .nav-submenu.open {
            max-height: 220px;
        }

        .nav-submenu a {
            padding-left: 38px;
            font-size: .82rem;
        }

        .nav-submenu a i {
            font-size: .76rem;
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid rgba(255, 255, 255, .08);
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 8px 6px 14px;
        }

        .sidebar-user .avatar {
            width: 36px;
            height: 36px;
            border-radius: 11px;
            background: linear-gradient(135deg, var(--pink) 0%, var(--pink-deep) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: .88rem;
            flex-shrink: 0;
        }

        .sidebar-user .name {
            color: #fff;
            font-weight: 600;
            font-size: .82rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 130px;
        }

        .sidebar-user .role {
            color: #8A8299;
            font-size: .72rem;
            text-transform: capitalize;
        }

        .sidebar-logout {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            color: #C3BDD0;
            font-weight: 500;
            font-size: .85rem;
            transition: .15s;
        }

        .sidebar-logout i {
            width: 16px;
            text-align: center;
            color: #7A7290;
        }

        .sidebar-logout:hover {
            background: rgba(255, 92, 133, .12);
            color: #FFB3C6;
        }

        .sidebar-logout:hover i {
            color: var(--pink);
        }
        .logout-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(26, 21, 35, .55);
    z-index: 100;
    align-items: center;
    justify-content: center;
}

.logout-modal-overlay.active {
    display: flex;
}

.logout-modal {
    background: var(--card);
    border-radius: 16px;
    padding: 32px 28px;
    width: 90%;
    max-width: 360px;
    text-align: center;
    box-shadow: var(--shadow-md);
    animation: modalPop .18s ease;
}

@keyframes modalPop {
    from { transform: scale(.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.logout-modal-icon {
    width: 56px;
    height: 56px;
    margin: 0 auto 16px;
    border-radius: 50%;
    background: var(--pink-light);
    color: var(--pink-deep);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
}

.logout-modal h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--ink);
    margin-bottom: 8px;
}

.logout-modal p {
    font-size: .88rem;
    color: var(--ink-soft);
    margin-bottom: 22px;
}

.logout-modal-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.logout-modal-actions button,
.logout-modal-actions a {
    flex: 1;
    padding: 10px 16px;
    border-radius: 10px;
    font-weight: 600;
    font-size: .86rem;
    cursor: pointer;
    border: none;
    display: inline-block;
}

.btn-cancel {
    background: var(--pink-pale);
    color: var(--ink-soft);
}

.btn-cancel:hover {
    background: var(--line);
}

.btn-confirm-logout {
    background: var(--pink);
    color: #fff;
}

.btn-confirm-logout:hover {
    background: var(--pink-dark);
}

        .content-body {
            padding: clamp(20px, 3vw, 40px);
            max-width: 1320px;
        }

        @media(max-width:900px) {
            .app-shell {
                display: block;
            }

            .mobile-topbar {
                display: flex;
            }

            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                width: 260px;
                transform: translateX(-100%);
                transition: transform .25s ease;
                z-index: 50;
                box-shadow: 0 0 40px rgba(0, 0, 0, .3);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .content-body {
                padding: 18px;
            }
        }

        @media(max-width:520px) {
            .sidebar-brand .mark {
                width: 170px;
                height: 68px;
            }
        }
    </style>

    
    <?php echo $__env->yieldContent('styles'); ?>
</head>

<body>

    <div class="mobile-topbar no-print">
        <img src="<?php echo e(asset('images/wonderpark1logo.png')); ?>" alt="WonderPark">
        <button class="hamburger-btn" id="hamburgerBtn" aria-label="Open menu"><i class="fa-solid fa-bars"></i></button>
    </div>

    <div class="sidebar-overlay no-print" id="sidebarOverlay"></div>

    <div class="app-shell">

        <aside class="sidebar no-print" id="sidebar">
            <div class="sidebar-brand">
                <div class="mark"><img src="<?php echo e(asset('images/wonderpark1logo.png')); ?>" alt="REKS"></div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-label">Operations</div>

                <?php if(session('role') === 'cashier'): ?>
                    <a href="/pos" class="<?php echo e(request()->is('pos') ? 'active' : ''); ?>">
                        <i class="fa-solid fa-cash-register"></i> Point of Sale
                    </a>
                    <a href="/inventory" class="<?php echo e(request()->is('inventory') ? 'active' : ''); ?>">
                        <i class="fa-solid fa-boxes-stacked"></i> Inventory
                    </a>
                <?php else: ?>
                    <a href="/inventory" class="<?php echo e(request()->is('inventory') ? 'active' : ''); ?>">
                        <i class="fa-solid fa-boxes-stacked"></i> Inventory
                    </a>

                    <div class="nav-label">People</div>

                    <?php
                        $isOnVisitorRoute = request()->routeIs('visitor-summary') || request()->routeIs('reservations.*');
                        $isOnManpowerRoute = request()->routeIs('attendance') || request()->routeIs('salary') || request()->routeIs('payroll-history');
                    ?>

                    
                    <div class="nav-group">
                        <div class="nav-parent-row">
                            <a href="javascript:void(0)" class="nav-parent-link <?php echo e($isOnVisitorRoute ? 'active' : ''); ?>">
                                <i class="fa-solid fa-users"></i> Visitor Login and Booking Summary
                            </a>
                            <button type="button" class="nav-caret-btn <?php echo e($isOnVisitorRoute ? 'open' : ''); ?>"
                                id="peopleSubmenuToggle" aria-label="Toggle Visitor Login and Booking Summary submenu">
                                <i class="fa-solid fa-chevron-down"></i>
                            </button>
                        </div>
                        <div class="nav-submenu <?php echo e($isOnVisitorRoute ? 'open' : ''); ?>" id="peopleSubmenu">
                            <a href="<?php echo e(route('visitor-summary')); ?>" class="<?php echo e(request()->routeIs('visitor-summary') ? 'active' : ''); ?>">
                                <i class="fa-solid fa-user-clock"></i> Summary
                            </a>
                            <a href="<?php echo e(route('reservations.index')); ?>" class="<?php echo e(request()->routeIs('reservations.*') ? 'active' : ''); ?>">
                                <i class="fa-solid fa-ticket"></i> Reservation
                            </a>
                        </div>
                    </div>

                    
                    <div class="nav-group">
                        <div class="nav-parent-row">
                            <a href="javascript:void(0)" class="nav-parent-link <?php echo e($isOnManpowerRoute ? 'active' : ''); ?>">
                                <i class="fa-solid fa-clipboard-check"></i> Manpower
                            </a>
                            <button type="button" class="nav-caret-btn <?php echo e($isOnManpowerRoute ? 'open' : ''); ?>"
                                id="manpowerSubmenuToggle" aria-label="Toggle Manpower submenu">
                                <i class="fa-solid fa-chevron-down"></i>
                            </button>
                        </div>
                        <div class="nav-submenu <?php echo e($isOnManpowerRoute ? 'open' : ''); ?>" id="manpowerSubmenu">
                            <a href="<?php echo e(route('attendance')); ?>" class="<?php echo e(request()->routeIs('attendance') ? 'active' : ''); ?>">
                                <i class="fa-solid fa-clipboard-check"></i> Attendance
                            </a>
                            <a href="<?php echo e(route('salary')); ?>" class="<?php echo e(request()->routeIs('salary') ? 'active' : ''); ?>">
                                <i class="fa-solid fa-money-check-dollar"></i> Salary and Deductions
                            </a>
                            <a href="<?php echo e(route('payroll-history')); ?>" class="<?php echo e(request()->routeIs('payroll-history') ? 'active' : ''); ?>">
                                <i class="fa-solid fa-file-invoice-dollar"></i> Payroll and Payslip
                            </a>
                        </div>
                    </div>

                    <a href="/accounts/create" class="<?php echo e(request()->is('accounts/create') ? 'active' : ''); ?>">
                        <i class="fa-solid fa-user-plus"></i> Account Management
                    </a>

                    <div class="nav-label">Insights</div>

                    <?php
                        $zoneRoutes = ['fieldOfRides', 'RollerFever', 'DinoAdventure'];
                        $isOnZoneRoute = request()->is($zoneRoutes);
                        $isOnForecastRoute = request()->is('ml-forecast');
                        $submenuOpen = $isOnZoneRoute || $isOnForecastRoute;
                    ?>

                    <?php if(session('role') === 'admin'): ?>
                        
                        <div class="nav-group">
                            <div class="nav-parent-row">
                                <a href="/ml-forecast" class="nav-parent-link <?php echo e($isOnForecastRoute ? 'active' : ''); ?>">
                                    <i class="fa-solid fa-brain"></i> Sales Forecast
                                </a>
                                <button type="button" class="nav-caret-btn <?php echo e($submenuOpen ? 'open' : ''); ?>"
                                    id="forecastSubmenuToggle" aria-label="Toggle Sales Forecast submenu">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                            </div>
                            <div class="nav-submenu <?php echo e($submenuOpen ? 'open' : ''); ?>" id="forecastSubmenu">
                                <a href="/fieldOfRides" class="<?php echo e(request()->is('fieldOfRides') ? 'active' : ''); ?>">
                                    <i class="fa-solid fa-ferris-wheel"></i> Field of Rides
                                </a>
                                <a href="/RollerFever" class="<?php echo e(request()->is('RollerFever') ? 'active' : ''); ?>">
                                    <i class="fa-solid fa-bolt"></i> Roller Fever
                                </a>
                                <a href="/DinoAdventure" class="<?php echo e(request()->is('DinoAdventure') ? 'active' : ''); ?>">
                                    <i class="fa-solid fa-dragon"></i> Dino Adventure
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        
                        <a href="/fieldOfRides" class="<?php echo e(request()->is('fieldOfRides') ? 'active' : ''); ?>">
                            <i class="fa-solid fa-ferris-wheel"></i> Field of Rides
                        </a>
                        <a href="/RollerFever" class="<?php echo e(request()->is('RollerFever') ? 'active' : ''); ?>">
                            <i class="fa-solid fa-bolt"></i> Roller Fever
                        </a>
                        <a href="/DinoAdventure" class="<?php echo e(request()->is('DinoAdventure') ? 'active' : ''); ?>">
                            <i class="fa-solid fa-dragon"></i> Dino Adventure
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="avatar"><?php echo e(strtoupper(substr(session('fullname', 'U'), 0, 1))); ?></div>
                    <div>
                        <div class="name"><?php echo e(session('fullname')); ?></div>
                        <div class="role"><?php echo e(session('role')); ?></div>
                    </div>
                </div>
                <button type="button" class="sidebar-logout" onclick="openLogoutModal()" style="width:100%; background:none; border:none; cursor:pointer; text-align:left; font-family:inherit;">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>
            </div>
        </aside>

        <div class="main-content">
            <div class="content-body">
                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </div>

    </div>
    <!-- Logout Confirmation Modal -->
<div class="logout-modal-overlay" id="logoutModalOverlay">
    <div class="logout-modal">
        <div class="logout-modal-icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        <h3>Confirm Logout</h3>
        <p>Are you sure you want to logout?</p>
        <div class="logout-modal-actions">
            <button type="button" class="btn-cancel" onclick="closeLogoutModal()">Cancel</button>
            <a href="/logout" class="btn-confirm-logout">Yes, Logout</a>
        </div>
    </div>
</div>

    <script>
        // Mobile hamburger open/close
        (function() {
            const hamburgerBtn = document.getElementById('hamburgerBtn');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (!hamburgerBtn) return;

            function openSidebar() {
                sidebar.classList.add('open');
                overlay.classList.add('active');
            }

            function closeSidebar() {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
            }

            hamburgerBtn.addEventListener('click', openSidebar);
            overlay.addEventListener('click', closeSidebar);
            document.querySelectorAll('.sidebar-nav a').forEach(link => link.addEventListener('click', closeSidebar));
        })();

        // Sales Forecast submenu toggle (admin only — element only exists for admins)
        (function() {
            const toggleBtn = document.getElementById('forecastSubmenuToggle');
            const submenu = document.getElementById('forecastSubmenu');
            if (!toggleBtn || !submenu) return;

            toggleBtn.addEventListener('click', () => {
                submenu.classList.toggle('open');
                toggleBtn.classList.toggle('open');
            });
        })();

        // Generic reusable toggle for the People / Manpower groups
        (function() {
            function wireGroup(toggleId, submenuId) {
                const toggleBtn = document.getElementById(toggleId);
                const submenu = document.getElementById(submenuId);
                if (!toggleBtn || !submenu) return;

                const parentLink = toggleBtn.closest('.nav-parent-row')?.querySelector('.nav-parent-link');

                function toggle() {
                    submenu.classList.toggle('open');
                    toggleBtn.classList.toggle('open');
                }

                toggleBtn.addEventListener('click', toggle);
                parentLink?.addEventListener('click', toggle);
            }

            wireGroup('peopleSubmenuToggle', 'peopleSubmenu');
            wireGroup('manpowerSubmenuToggle', 'manpowerSubmenu');
        })();
        function openLogoutModal() {
    document.getElementById('logoutModalOverlay').classList.add('active');
        }

        function closeLogoutModal() {
            document.getElementById('logoutModalOverlay').classList.remove('active');
        }

        // Optional: close modal pag nag-click sa labas
        document.getElementById('logoutModalOverlay')?.addEventListener('click', function(e) {
            if (e.target === this) closeLogoutModal();
        });
    </script>

    
    <?php echo $__env->yieldPushContent('scripts'); ?>

</body>

</html><?php /**PATH C:\xampp\htdocs\REKS\REKS\laravel-app\resources\views/layouts/sidebar.blade.php ENDPATH**/ ?>