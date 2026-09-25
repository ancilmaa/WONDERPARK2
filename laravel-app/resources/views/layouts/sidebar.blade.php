<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WonderPark System | @yield('title', 'Dashboard')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- Page-specific extra <link> tags (extra fonts, etc.) go here --}}
    @stack('head')

    <style>
        :root {
            --pink: #FF4778;
            --pink-dark: #E13560;
            --pink-deep: #C22750;
            --pink-light: #FFE1EA;
            --pink-pale: #FFF5F8;
            --ink: #14101C;
            --ink-soft: #6B6478;
            --muted: #A39CB0;
            --line: #ECE7F0;
            --line-strong: #DAD2E3;
            --bg: #F5F2F7;
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
            --shadow-sm: 0 1px 3px rgba(20, 16, 28, .06), 0 1px 2px rgba(20, 16, 28, .04);
            --shadow-md: 0 20px 44px rgba(20, 16, 28, .12);
            --shadow-pink: 0 16px 32px rgba(226, 53, 96, .30);
            --radius-sm: 10px;
            --radius-md: 14px;
            --radius-lg: 20px;

            /* sidebar-specific tokens */
            --sb-bg: #0E0B15;
            --sb-panel: #191322;
            --sb-panel-soft: rgba(255, 255, 255, .035);
            --sb-line: rgba(255, 255, 255, .07);
            --sb-text: #B7B0C6;
            --sb-text-dim: #6C6480;
            --sb-text-bright: #FFFFFF;
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
            -webkit-font-smoothing: antialiased;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .app-shell {
            display: grid;
            grid-template-columns: 264px 1fr;
            min-height: 100vh;
        }

        /* ===== MOBILE TOPBAR ===== */
        .mobile-topbar {
            display: none;
            align-items: center;
            justify-content: space-between;
            background: var(--sb-bg);
            padding: 13px 18px;
            position: sticky;
            top: 0;
            z-index: 40;
            border-bottom: 1px solid var(--sb-line);
        }

        .mobile-topbar img {
            height: 26px;
        }

        .mobile-topbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .hamburger-btn {
            width: 38px;
            height: 38px;
            border-radius: var(--radius-sm);
            background: var(--sb-panel-soft);
            border: 1px solid var(--sb-line);
            color: #fff;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .15s, transform .1s;
        }

        .hamburger-btn:active {
            transform: scale(.94);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(14, 11, 21, .55);
            backdrop-filter: blur(2px);
            z-index: 45;
        }

        .sidebar-overlay.active {
            display: block;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            background: var(--sb-bg);
            background-image:
                radial-gradient(560px 320px at 0% 0%, rgba(255, 71, 120, .10), transparent 60%),
                radial-gradient(420px 260px at 100% 100%, rgba(255, 71, 120, .06), transparent 55%);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            border-right: 1px solid var(--sb-line);
        }

        /* --- Brand: compact header instead of a big empty logo block --- */
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 20px 18px;
            border-bottom: 1px solid var(--sb-line);
            flex-shrink: 0;
        }

        .sidebar-brand .mark {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-sm);
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5px;
            flex-shrink: 0;
            box-shadow: 0 6px 16px rgba(0, 0, 0, .25);
        }

        .sidebar-brand .mark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .sidebar-brand .brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
            overflow: hidden;
            flex: 1;
            min-width: 0;
        }

        .sidebar-brand .brand-text .brand-name {
            color: var(--sb-text-bright);
            font-weight: 800;
            font-size: .96rem;
            letter-spacing: -.01em;
            white-space: nowrap;
        }

        .sidebar-brand .brand-text .brand-sub {
            color: var(--sb-text-dim);
            font-weight: 500;
            font-size: .68rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 1px;
        }

        /* --- Notification bell + dropdown --- */
        .notif-trigger {
            position: relative;
            flex-shrink: 0;
        }

        .notif-btn {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--sb-line);
            background: var(--sb-panel-soft);
            color: var(--sb-text);
            font-size: .9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            transition: background .15s, color .15s, border-color .15s;
        }

        .notif-btn:hover,
        .notif-btn.open {
            background: rgba(255, 71, 120, .14);
            border-color: rgba(255, 71, 120, .3);
            color: var(--pink);
        }

        .notif-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            min-width: 17px;
            height: 17px;
            padding: 0 4px;
            border-radius: 9px;
            background: var(--pink);
            border: 2px solid var(--sb-bg);
            color: #fff;
            font-size: .58rem;
            font-weight: 800;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 0 1px rgba(255, 71, 120, .35);
        }

        .notif-panel {
            display: none;
            position: absolute;
            top: calc(100% + 12px);
            left: 0;
            width: 336px;
            max-width: 82vw;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            z-index: 10000;
        }

        .notif-panel.open {
            display: block;
            animation: notifPanelIn .16s ease;
        }

        @keyframes notifPanelIn {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .notif-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            border-bottom: 1px solid var(--line);
        }

        .notif-panel-header h4 {
            font-size: .88rem;
            font-weight: 800;
            color: var(--ink);
            letter-spacing: -.01em;
        }

        .notif-mark-read {
            background: var(--pink-pale);
            border: none;
            color: var(--pink-dark);
            font-size: .68rem;
            font-weight: 700;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 999px;
            transition: background .12s;
        }

        .notif-mark-read:hover {
            background: var(--pink-light);
        }

        .notif-list {
            max-height: 320px;
            overflow-y: auto;
        }

        .notif-item {
            display: flex;
            gap: 11px;
            width: 100%;
            padding: 12px 16px;
            border-bottom: 1px solid var(--line);
            border-left: none;
            background: none;
            font: inherit;
            text-align: left;
            cursor: pointer;
            transition: background .12s;
        }

        .notif-item:last-child {
            border-bottom: none;
        }

        .notif-item:hover,
        .notif-item:focus-visible {
            background: var(--pink-pale);
            outline: none;
        }

        .notif-item.unread {
            background: var(--pink-pale);
        }

        .notif-icon {
            width: 34px;
            height: 34px;
            border-radius: var(--radius-sm);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .82rem;
        }

        .notif-icon.type-reservation,
        .notif-icon.type-booking {
            background: var(--pink-light);
            color: var(--pink-deep);
        }

        .notif-icon.type-low_stock,
        .notif-icon.type-inventory {
            background: var(--amber-light);
            color: var(--amber);
        }

        .notif-icon.type-pos {
            background: #E3F0FF;
            color: #2563EB;
        }

        .notif-body {
            flex: 1;
            min-width: 0;
        }

        .notif-title-row {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .notif-title {
            font-size: .81rem;
            font-weight: 700;
            color: var(--ink);
        }

        .notif-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--pink);
            flex-shrink: 0;
        }

        .notif-message {
            font-size: .75rem;
            color: var(--ink-soft);
            margin-top: 2px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .notif-time {
            font-size: .66rem;
            color: var(--muted);
            margin-top: 4px;
        }

        .notif-empty {
            padding: 32px 16px;
            text-align: center;
            font-size: .8rem;
            color: var(--ink-soft);
        }

        .notif-empty-text {
            font-size: .8rem;
            color: var(--ink-soft);
        }

        .notif-panel-footer {
            padding: 10px 16px;
            text-align: center;
            border-top: 1px solid var(--line);
        }

        .notif-panel-footer a {
            font-size: .77rem;
            font-weight: 700;
            color: var(--pink-dark);
        }

        /* --- Nav --- */
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 14px 14px 16px;
            display: flex;
            flex-direction: column;
            gap: 2px;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 0;
            height: 0;
            display: none;
        }

        .nav-section {
            display: flex;
            flex-direction: column;
            gap: 2px;
            padding: 6px 2px;
            border-radius: var(--radius-md);
            margin-bottom: 4px;
        }

        .nav-section + .nav-section {
            border-top: 1px solid var(--sb-line);
            padding-top: 16px;
            margin-top: 6px;
        }

        .nav-label {
            font-size: .64rem;
            font-weight: 700;
            letter-spacing: .09em;
            text-transform: uppercase;
            color: var(--sb-text-dim);
            padding: 4px 10px 8px;
        }

        .nav-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-right: 6px;
            border-radius: 8px;
            transition: background .12s, color .12s;
        }

        a.nav-label-row:hover {
            background: rgba(255, 71, 120, .1);
        }

        a.nav-label-row:hover span:first-child {
            color: #FFB3C6;
        }

        .nav-label-count {
            background: rgba(255, 71, 120, .18);
            color: var(--pink);
            font-size: .62rem;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 999px;
            line-height: 1.4;
        }

        .nav-label-count.is-zero {
            display: none;
        }

        .nav-notif-list {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .nav-row {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 9px 11px;
            border-radius: var(--radius-sm);
            color: var(--sb-text);
            font-weight: 500;
            font-size: .84rem;
            transition: background .14s, color .14s;
        }

        .nav-row i {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            background: var(--sb-panel-soft);
            text-align: center;
            font-size: .82rem;
            color: var(--sb-text-dim);
            transition: color .14s, background .14s;
            flex-shrink: 0;
        }

        .nav-row span {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        a.nav-row:hover {
            background: var(--sb-panel);
            color: #fff;
        }

        a.nav-row:hover i {
            color: var(--pink);
            background: rgba(255, 71, 120, .14);
        }

        .nav-row.unread span::after {
            content: '';
            display: inline-block;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: var(--pink);
            margin-left: 6px;
            vertical-align: middle;
        }

        .nav-row.nav-row-empty {
            cursor: default;
            color: var(--sb-text-dim);
        }

        .nav-notif-viewall {
            display: block;
            text-align: center;
            padding: 8px 8px 4px;
            font-size: .7rem;
            font-weight: 700;
            color: var(--pink);
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 9px 11px;
            border-radius: var(--radius-sm);
            color: var(--sb-text);
            font-weight: 500;
            font-size: .84rem;
            border-left: 2.5px solid transparent;
            transition: background .14s, color .14s, border-color .14s;
        }

        .sidebar-nav a i {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            background: var(--sb-panel-soft);
            text-align: center;
            font-size: .82rem;
            color: var(--sb-text-dim);
            transition: color .14s, background .14s;
            flex-shrink: 0;
        }

        .sidebar-nav a:hover {
            background: var(--sb-panel);
            color: #fff;
        }

        .sidebar-nav a:hover i {
            color: var(--pink);
            background: rgba(255, 71, 120, .14);
        }

        .sidebar-nav a.active {
            background: linear-gradient(135deg, rgba(255, 71, 120, .16), rgba(255, 71, 120, .05));
            color: #fff;
            font-weight: 700;
            border-left-color: transparent;
            box-shadow: inset 0 0 0 1px rgba(255, 71, 120, .22);
        }

        .sidebar-nav a.active i {
            color: #fff;
            background: var(--pink);
            box-shadow: 0 4px 10px rgba(255, 71, 120, .4);
        }

        /* ===== NESTED SUBMENUS (People / Manpower / Sales Forecast / CMS groups) ===== */
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
            min-width: 0;
        }

        .nav-parent-row .nav-parent-link span {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 10px;
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
            color: var(--sb-text-dim);
            transition: .15s;
        }

        .nav-caret-btn:hover {
            background: rgba(255, 71, 120, .12);
            color: var(--pink);
        }

        .nav-caret-btn i {
            font-size: .7rem;
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
            max-height: 320px;
            padding: 3px 0 5px;
        }

        .nav-submenu a {
            padding-left: 12px;
            padding-top: 8px;
            padding-bottom: 8px;
            font-size: .8rem;
            margin-left: 15px;
            border-left: 2px solid var(--sb-line);
            border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
        }

        .nav-submenu a i {
            width: 24px;
            height: 24px;
            font-size: .74rem;
        }

        .nav-submenu a.active {
            border-left: 2px solid var(--pink);
        }
        .nav-label-count.is-zero {
            display: none;
        }

        .sidebar-nav a.has-unread {
            background: linear-gradient(135deg, rgba(255, 71, 120, .16), rgba(255, 71, 120, .05));
            color: #fff;
            box-shadow: inset 0 0 0 1px rgba(255, 71, 120, .22);
        }

        .sidebar-nav a.has-unread i {
            color: #fff;
            background: var(--pink);
        }

        /* --- Footer / account card --- */
        .sidebar-footer {
            padding: 12px;
            border-top: 1px solid var(--sb-line);
            flex-shrink: 0;
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px;
            border-radius: var(--radius-md);
            background: var(--sb-panel);
            border: 1px solid var(--sb-line);
            margin-bottom: 0;
        }

        .sidebar-user .avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--pink) 0%, var(--pink-deep) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: .85rem;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(255, 71, 120, .35);
        }

        .sidebar-user .user-info {
            display: flex;
            flex-direction: column;
            min-width: 0;
            flex: 1;
        }

        .sidebar-user .name {
            color: #fff;
            font-weight: 600;
            font-size: .8rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user .role {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: var(--pink);
            background: rgba(255, 71, 120, .14);
            font-size: .62rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
            padding: 1px 7px;
            border-radius: 5px;
            margin-top: 3px;
            width: fit-content;
        }

        .sidebar-logout {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 9px;
            color: var(--sb-text-dim);
            font-size: .85rem;
            transition: .15s;
            background: none;
            border: none;
            cursor: pointer;
            flex-shrink: 0;
        }

        .sidebar-logout:hover {
            background: rgba(255, 71, 120, .14);
            color: var(--pink);
        }

        .logout-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(14, 11, 21, .6);
            backdrop-filter: blur(3px);
            z-index: 100;
            align-items: center;
            justify-content: center;
        }

        .logout-modal-overlay.active {
            display: flex;
        }

        .logout-modal {
            background: var(--card);
            border-radius: 22px;
            padding: 40px 32px 26px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: var(--shadow-md);
            animation: modalPop .18s ease;
        }

        @keyframes modalPop {
            from { transform: scale(.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .logout-modal-icon {
            width: 68px;
            height: 68px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: var(--pink-light);
            color: var(--pink-deep);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
        }

        .logout-modal h3 {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--ink);
            margin-bottom: 10px;
            letter-spacing: -.01em;
        }

        .logout-modal p {
            font-size: .92rem;
            color: var(--ink-soft);
            margin-bottom: 6px;
        }

        .logout-modal .logout-modal-subtext {
            font-size: .8rem;
            color: var(--muted);
            line-height: 1.5;
            margin-bottom: 26px;
        }

        .logout-modal-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .logout-modal-actions button,
        .logout-modal-actions a {
            flex: 1;
            padding: 13px 16px;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: .92rem;
            cursor: pointer;
            border: none;
            display: inline-block;
            transition: background .14s, transform .08s;
        }

        .logout-modal-actions button:active,
        .logout-modal-actions a:active {
            transform: scale(.97);
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
            box-shadow: var(--shadow-pink);
        }

        .btn-confirm-logout:hover {
            background: var(--pink-dark);
        }

        .logout-modal-hint {
            margin-top: 20px;
            font-size: .76rem;
            color: var(--muted);
        }

        .logout-modal-hint .lm-kbd {
            background: var(--pink-pale);
            border: 1px solid var(--line-strong);
            border-radius: 5px;
            padding: 1px 7px;
            font-size: .72rem;
            font-weight: 700;
            color: var(--ink-soft);
            margin: 0 2px;
        }

        /* --- Notification DETAIL popup (opens when a notif-item is clicked) --- */
        .notif-detail-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(14, 11, 21, .6);
            backdrop-filter: blur(3px);
            z-index: 10100;
            align-items: center;
            justify-content: center;
        }

        .notif-detail-overlay.active {
            display: flex;
        }

        .notif-detail-modal {
            background: var(--card);
            border-radius: var(--radius-lg);
            padding: 28px 26px 22px;
            width: 90%;
            max-width: 380px;
            text-align: center;
            box-shadow: var(--shadow-md);
            animation: modalPop .18s ease;
        }

        .notif-detail-icon {
            width: 54px;
            height: 54px;
            margin: 0 auto 14px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            background: var(--pink-light);
            color: var(--pink-deep);
        }

        .notif-detail-icon.type-low_stock,
        .notif-detail-icon.type-inventory {
            background: var(--amber-light);
            color: var(--amber);
        }

        .notif-detail-icon.type-pos {
            background: #E3F0FF;
            color: #2563EB;
        }

        .notif-detail-title {
            font-size: 1.04rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 8px;
            letter-spacing: -.01em;
        }

        .notif-detail-message {
            font-size: .85rem;
            color: var(--ink-soft);
            line-height: 1.65;
            margin-bottom: 14px;
            word-break: break-word;
            white-space: pre-line;
            text-align: left;
            background: var(--bg);
            border-radius: var(--radius-sm);
            padding: 12px 14px;
        }

        .notif-detail-time {
            font-size: .74rem;
            color: var(--muted);
            margin-bottom: 22px;
        }

        .notif-detail-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .notif-detail-actions button,
        .notif-detail-actions a {
            flex: 1;
            padding: 10px 16px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: .86rem;
            cursor: pointer;
            border: none;
            display: inline-block;
            text-align: center;
            transition: background .14s, transform .08s;
        }

        .notif-detail-actions button:active,
        .notif-detail-actions a:active {
            transform: scale(.97);
        }

        .btn-notif-exit {
            background: var(--pink-pale);
            color: var(--ink-soft);
        }

        .btn-notif-exit:hover {
            background: var(--line);
        }

        .btn-notif-goto {
            background: var(--pink);
            color: #fff;
        }

        .btn-notif-goto:hover {
            background: var(--pink-dark);
        }
        .notif-btn:hover,
        .notif-btn.open {
            background: rgba(255, 71, 120, .16);
            color: var(--pink);
        }

        .notif-btn.has-unread {
            background: rgba(255, 71, 120, .16);
            border-color: rgba(255, 71, 120, .3);
            color: var(--pink);
        }

        .notif-btn.has-unread .notif-badge {
            animation: notifPulse 1.6s ease-in-out infinite;
        }

        @keyframes notifPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.12); }
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
                width: 280px;
                transform: translateX(-100%);
                transition: transform .25s ease;
                z-index: 50;
                box-shadow: 0 0 48px rgba(0, 0, 0, .35);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .sidebar-brand .notif-trigger {
                display: none;
            }

            .notif-panel {
                position: fixed;
                top: 64px;
                left: 12px;
                right: 12px;
                width: auto;
                max-width: none;
            }

            .content-body {
                padding: 18px;
            }
        }
    </style>

    {{-- Page-specific styles (metrics grid, tables, modals, etc.) --}}
    @yield('styles')
    @vite(['resources/js/app.js'])
</head>

<body>

    @php
        $sidebarNotifications = $sidebarNotifications ?? collect();
        $unreadNotifCount = $unreadNotifCount ?? 0;
        $notifTotalCount = $notifTotalCount ?? 0;

        $canViewNotifications = !in_array(session('role'), ['cashier', 'customer']);
    @endphp

    <div class="mobile-topbar no-print">
        <img src="{{ asset('images/wonderpark1logo.png') }}" alt="WonderPark">
        <div class="mobile-topbar-actions">
            @if ($canViewNotifications)
                <div class="notif-trigger">
                    <button type="button" class="notif-btn {{ $unreadNotifCount > 0 ? 'has-unread' : '' }}" id="notifBtnMobile" aria-label="Notifications">
                        <i class="fa-solid fa-bell"></i>
                        @if ($unreadNotifCount > 0)
                            <span class="notif-badge">{{ $unreadNotifCount > 9 ? '9+' : $unreadNotifCount }}</span>
                        @endif
                    </button>
                    <div class="notif-panel" id="notifPanelMobile">
                        <div class="notif-panel-header">
                            <h4>Notifications</h4>
                            @if ($unreadNotifCount > 0)
                                <button type="button" class="notif-mark-read" onclick="markAllNotifsRead(this)">Mark all read</button>
                            @endif
                        </div>
                        <div class="notif-list">
                            @forelse ($sidebarNotifications as $n)
                                <div class="notif-item {{ data_get($n, 'is_read', true) ? '' : 'unread' }}"
                                     role="button" tabindex="0"
                                     data-id="{{ data_get($n, 'id') }}"
                                     data-title="{{ data_get($n, 'title') }}"
                                     data-message="{{ data_get($n, 'message') }}"
                                     data-time="{{ data_get($n, 'time') }}"
                                     data-url="{{ data_get($n, 'url', '#') }}"
                                     data-type="{{ data_get($n, 'type', 'reservation') }}"
                                     data-read-url="{{ route('notifications.read', data_get($n, 'id')) }}">
                                    <div class="notif-icon type-{{ data_get($n, 'type', 'reservation') }}">
                                        <i class="fa-solid {{ in_array(data_get($n, 'type'), ['low_stock', 'inventory']) ? 'fa-boxes-stacked' : (data_get($n, 'type') === 'pos' ? 'fa-receipt' : 'fa-ticket') }}"></i>
                                    </div>
                                    <div class="notif-body">
                                        <div class="notif-title-row">
                                            <span class="notif-title">{{ data_get($n, 'title') }}</span>
                                            @unless (data_get($n, 'is_read', true))
                                                <span class="notif-dot"></span>
                                            @endunless
                                        </div>
                                        <div class="notif-message">{{ data_get($n, 'message') }}</div>
                                        <div class="notif-time">{{ data_get($n, 'time') }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="notif-empty"><span class="notif-empty-text">No notifications yet.</span></div>
                            @endforelse
                        </div>
                        <div class="notif-panel-footer">
                            <a href="{{ route('notifications.index') }}">View all</a>
                        </div>
                    </div>
                </div>
            @endif
            <button class="hamburger-btn" id="hamburgerBtn" aria-label="Open menu"><i class="fa-solid fa-bars"></i></button>
        </div>
    </div>

    <div class="sidebar-overlay no-print" id="sidebarOverlay"></div>

    <div class="app-shell">

        <aside class="sidebar no-print" id="sidebar">
            <div class="sidebar-brand">
                <div class="mark"><img src="{{ asset('images/wonderpark1logo.png') }}" alt="REKS"></div>
                <div class="brand-text">
                    <span class="brand-name">WonderPark</span>
                    <span class="brand-sub">Lipa Branch &middot; Operations</span>
                </div>
                @if ($canViewNotifications)
                    <div class="notif-trigger">
                        <button type="button" class="notif-btn {{ $unreadNotifCount > 0 ? 'has-unread' : '' }}" id="notifBtnDesktop" aria-label="Notifications">
                            <i class="fa-solid fa-bell"></i>
                            @if ($unreadNotifCount > 0)
                                <span class="notif-badge">{{ $unreadNotifCount > 9 ? '9+' : $unreadNotifCount }}</span>
                            @endif
                        </button>
                        <div class="notif-panel" id="notifPanelDesktop">
                            <div class="notif-panel-header">
                                <h4>Notifications</h4>
                                @if ($unreadNotifCount > 0)
                                    <button type="button" class="notif-mark-read" onclick="markAllNotifsRead(this)">Mark all read</button>
                                @endif
                            </div>
                            <div class="notif-list">
                                @forelse ($sidebarNotifications as $n)
                                    <div class="notif-item {{ data_get($n, 'is_read', true) ? '' : 'unread' }}"
                                         role="button" tabindex="0"
                                         data-id="{{ data_get($n, 'id') }}"
                                         data-title="{{ data_get($n, 'title') }}"
                                         data-message="{{ data_get($n, 'message') }}"
                                         data-time="{{ data_get($n, 'time') }}"
                                         data-url="{{ data_get($n, 'url', '#') }}"
                                         data-type="{{ data_get($n, 'type', 'reservation') }}"
                                         data-read-url="{{ route('notifications.read', data_get($n, 'id')) }}">
                                        <div class="notif-icon type-{{ data_get($n, 'type', 'reservation') }}">
                                            <i class="fa-solid {{ in_array(data_get($n, 'type'), ['low_stock', 'inventory']) ? 'fa-boxes-stacked' : (data_get($n, 'type') === 'pos' ? 'fa-receipt' : 'fa-ticket') }}"></i>
                                        </div>
                                        <div class="notif-body">
                                            <div class="notif-title-row">
                                                <span class="notif-title">{{ data_get($n, 'title') }}</span>
                                                @unless (data_get($n, 'is_read', true))
                                                    <span class="notif-dot"></span>
                                                @endunless
                                            </div>
                                            <div class="notif-message">{{ data_get($n, 'message') }}</div>
                                            <div class="notif-time">{{ data_get($n, 'time') }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="notif-empty"><span class="notif-empty-text">No notifications yet.</span></div>
                                @endforelse
                            </div>
                            <div class="notif-panel-footer">
                                <a href="{{ route('notifications.index') }}">View all</a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-label">Operations</div>

                    @if (session('role') === 'cashier')
                        <a href="/pos" class="{{ request()->is('pos') ? 'active' : '' }}">
                            <i class="fa-solid fa-cash-register"></i> <span>Point of Sale</span>
                        </a>
                        <a href="/inventory" class="{{ request()->is('inventory') ? 'active' : '' }}">
                            <i class="fa-solid fa-boxes-stacked"></i> <span>Inventory</span>
                        </a>
                    @else
                        <a href="/inventory" class="{{ request()->is('inventory') ? 'active' : '' }}">
                            <i class="fa-solid fa-boxes-stacked"></i> <span>Inventory</span>
                        </a>
                    @endif
                </div>

                @if ($canViewNotifications)
                    <div class="nav-section">
                        <div class="nav-label">Notifications</div>
                        <a href="{{ route('notifications.index') }}"id="navNotifLink"
                        class="{{ request()->routeIs('notifications.index') ? 'active' : '' }} {{ $unreadNotifCount > 0 ? 'has-unread' : '' }}">
                            <i class="fa-solid fa-bell"></i><span>Notifications</span>
                            <span id="navNotifCount" class="nav-label-count {{ $unreadNotifCount > 0 ? '' : 'is-zero' }}" style="margin-left:auto;">{{ $unreadNotifCount > 9 ? '9+' : $unreadNotifCount }}</span>
                        </a>

                        @if ($notifTotalCount > 4)
                            <a href="{{ route('notifications.index') }}" class="nav-notif-viewall">View all ({{ $notifTotalCount }})</a>
                        @endif
                    </div>
                @endif

                @if (session('role') !== 'cashier')
                    <div class="nav-section">
                        <div class="nav-label">People</div>

                        @php
                            $isOnVisitorRoute = request()->routeIs('visitor-summary') || request()->routeIs('reservations.*');
                            $isOnManpowerRoute = request()->routeIs('attendance') || request()->routeIs('salary') || request()->routeIs('payroll-history');
                        @endphp

                        {{-- Visitor Login and Booking Summary group --}}
                        <div class="nav-group">
                            <div class="nav-parent-row">
                                <a href="javascript:void(0)" class="nav-parent-link {{ $isOnVisitorRoute ? 'active' : '' }}">
                                    <i class="fa-solid fa-users"></i> <span>Visitor &amp; Booking</span>
                                </a>
                                <button type="button" class="nav-caret-btn {{ $isOnVisitorRoute ? 'open' : '' }}"
                                    id="peopleSubmenuToggle" aria-label="Toggle Visitor Login and Booking Summary submenu">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                            </div>
                            <div class="nav-submenu {{ $isOnVisitorRoute ? 'open' : '' }}" id="peopleSubmenu">
                                <a href="{{ route('visitor-summary') }}" class="{{ request()->routeIs('visitor-summary') ? 'active' : '' }}">
                                    <i class="fa-solid fa-user-clock"></i> <span>Summary</span>
                                </a>
                                <a href="{{ route('reservations.index') }}" class="{{ request()->routeIs('reservations.*') ? 'active' : '' }}">
                                    <i class="fa-solid fa-ticket"></i> <span>Reservation</span>
                                </a>
                            </div>
                        </div>

                        {{-- Manpower group --}}
                        <div class="nav-group">
                            <div class="nav-parent-row">
                                <a href="javascript:void(0)" class="nav-parent-link {{ $isOnManpowerRoute ? 'active' : '' }}">
                                    <i class="fa-solid fa-clipboard-check"></i> <span>Manpower</span>
                                </a>
                                <button type="button" class="nav-caret-btn {{ $isOnManpowerRoute ? 'open' : '' }}"
                                    id="manpowerSubmenuToggle" aria-label="Toggle Manpower submenu">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                            </div>
                            <div class="nav-submenu {{ $isOnManpowerRoute ? 'open' : '' }}" id="manpowerSubmenu">
                                <a href="{{ route('attendance') }}" class="{{ request()->routeIs('attendance') ? 'active' : '' }}">
                                    <i class="fa-solid fa-clipboard-check"></i> <span>Attendance</span>
                                </a>
                                <a href="{{ route('salary') }}" class="{{ request()->routeIs('salary') ? 'active' : '' }}">
                                    <i class="fa-solid fa-money-check-dollar"></i> <span>Salary &amp; Deductions</span>
                                </a>
                                <a href="{{ route('payroll-history') }}" class="{{ request()->routeIs('payroll-history') ? 'active' : '' }}">
                                    <i class="fa-solid fa-file-invoice-dollar"></i> <span>Payroll &amp; Payslip</span>
                                </a>
                            </div>
                        </div>

                        <a href="/accounts/create" class="{{ request()->is('accounts/create') ? 'active' : '' }}">
                            <i class="fa-solid fa-user-plus"></i> <span>Account Management</span>
                        </a>
                    </div>

                    {{-- ============================================================
                         CONTENT (CMS) nav-section — Admin only.
                         ============================================================ --}}
                    @if (session('role') === 'admin')
                        <div class="nav-section">
                            <div class="nav-label">Content</div>

                            @php
                                $isOnCmsRoute = request()->routeIs('cms.*');
                            @endphp

                            <div class="nav-group">
                                <div class="nav-parent-row">
                                    <a href="{{ route('cms.index') }}" class="nav-parent-link {{ $isOnCmsRoute ? 'active' : '' }}">
                                        <i class="fa-solid fa-pen-to-square"></i> <span style="font-size: 10px;">Website Management</span>
                                    </a>
                                    <button type="button" class="nav-caret-btn {{ $isOnCmsRoute ? 'open' : '' }}"
                                        id="cmsSubmenuToggle" aria-label="Toggle Website Management submenu">
                                        <i class="fa-solid fa-chevron-down"></i>
                                    </button>
                                </div>
                                <div class="nav-submenu {{ $isOnCmsRoute ? 'open' : '' }}" id="cmsSubmenu">
                                    <a href="{{ route('cms.section.edit', 'hero') }}" class="{{ request()->is('admin/cms/section/hero') ? 'active' : '' }}">
                                        <i class="fa-solid fa-house"></i> <span>Homepage Banner</span>
                                    </a>
                                    <a href="{{ route('cms.booking.pricing') }}" class="{{ request()->is('admin/cms/booking*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-money-bill-wave"></i> <span>Booking &amp; Pricing</span>
                                    </a>
                                    <a href="{{ route('cms.cards', 'pass') }}" class="{{ request()->is('admin/cms/cards/pass*') || request()->is('admin/cms/card/*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-ticket"></i> <span>Tickets Passes</span>
                                    </a>
                                    <a href="{{ route('cms.cards', 'attraction') }}" class="{{ request()->is('admin/cms/cards/attraction*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-ferris-wheel"></i> <span>Attractions and Rides</span>
                                    </a>
                                    <a href="{{ route('cms.cards', 'service') }}" class="{{ request()->is('admin/cms/cards/service*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-concierge-bell"></i> <span>Guest Information</span>
                                    </a>
                                    <a href="{{ route('cms.cards', 'step') }}" class="{{ request()->is('admin/cms/cards/step*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-shoe-prints"></i> <span>Visitor Guide</span>
                                    </a>
                                    <a href="{{ route('cms.section.edit', 'split') }}" class="{{ request()->is('admin/cms/section/split') ? 'active' : '' }}">
                                        <i class="fa-solid fa-calendar-days"></i> <span>Planning Visit</span>
                                    </a>
                                    <a href="{{ route('cms.section.edit', 'contact') }}" class="{{ request()->is('admin/cms/section/contact') ? 'active' : '' }}">
                                        <i class="fa-solid fa-location-dot"></i> <span>Contact Info</span>
                                    </a>
                                    <a href="{{ route('cms.section.edit', 'footer') }}" class="{{ request()->is('admin/cms/section/footer') ? 'active' : '' }}">
                                        <i class="fa-solid fa-shoe-prints"></i> <span>Footer</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="nav-section">
                        <div class="nav-label">Insights</div>

                        @php
                            $zoneRoutes = ['fieldOfRides', 'RollerFever', 'DinoAdventure'];
                            $isOnZoneRoute = request()->is($zoneRoutes);
                            $isOnForecastRoute = request()->is('ml-forecast');
                            $submenuOpen = $isOnZoneRoute || $isOnForecastRoute;
                        @endphp

                        @if (session('role') === 'admin')
                            <div class="nav-group">
                                <div class="nav-parent-row">
                                    <a href="/ml-forecast" class="nav-parent-link {{ $isOnForecastRoute ? 'active' : '' }}">
                                        <i class="fa-solid fa-brain"></i> <span>Sales Forecast</span>
                                    </a>
                                    <button type="button" class="nav-caret-btn {{ $submenuOpen ? 'open' : '' }}"
                                        id="forecastSubmenuToggle" aria-label="Toggle Sales Forecast submenu">
                                        <i class="fa-solid fa-chevron-down"></i>
                                    </button>
                                </div>
                                <div class="nav-submenu {{ $submenuOpen ? 'open' : '' }}" id="forecastSubmenu">
                                    <a href="/fieldOfRides" class="{{ request()->is('fieldOfRides') ? 'active' : '' }}">
                                        <i class="fa-solid fa-ferris-wheel"></i> <span>Field of Rides</span>
                                    </a>
                                    <a href="/RollerFever" class="{{ request()->is('RollerFever') ? 'active' : '' }}">
                                        <i class="fa-solid fa-bolt"></i> <span>Roller Fever</span>
                                    </a>
                                    <a href="/DinoAdventure" class="{{ request()->is('DinoAdventure') ? 'active' : '' }}">
                                        <i class="fa-solid fa-dragon"></i> <span>Dino Adventure</span>
                                    </a>
                                </div>
                            </div>
                        @else
                            <a href="/fieldOfRides" class="{{ request()->is('fieldOfRides') ? 'active' : '' }}">
                                <i class="fa-solid fa-ferris-wheel"></i> <span>Field of Rides</span>
                            </a>
                            <a href="/RollerFever" class="{{ request()->is('RollerFever') ? 'active' : '' }}">
                                <i class="fa-solid fa-bolt"></i> <span>Roller Fever</span>
                            </a>
                            <a href="/DinoAdventure" class="{{ request()->is('DinoAdventure') ? 'active' : '' }}">
                                <i class="fa-solid fa-dragon"></i> <span>Dino Adventure</span>
                            </a>
                        @endif
                    </div>
                @endif
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="avatar">{{ strtoupper(substr(session('fullname', 'U'), 0, 1)) }}</div>
                    <div class="user-info">
                        <div class="name">{{ session('fullname') }}</div>
                        <div class="role">{{ session('role') }}</div>
                    </div>
                              <button type="button" class="sidebar-logout" onclick="openLogoutModal()">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
                </div>
            </div>
        </aside>

        <div class="main-content">
            <div class="content-body">
                @yield('content')
            </div>
        </div>

    </div>

   <!-- Logout Confirmation Modal -->
<div class="logout-modal-overlay" id="logoutModalOverlay">
    <div class="logout-modal">
        <div class="logout-modal-icon">
            <i class="fas fa-arrow-right-from-bracket"></i>
        </div>
        <h3>Confirm Logout</h3>
        <p>Are you sure you want to log out?</p>
        <div class="logout-modal-subtext">
            Active {{ session('role') === 'cashier' ? 'cashier ' : '' }}session for
            <strong>{{ session('fullname', 'this account') }}</strong> will be ended.
        </div>
        <div class="logout-modal-actions">
            <button type="button" class="btn-cancel" onclick="closeLogoutModal()">Cancel</button>
            <a href="/logout" class="btn-confirm-logout">Yes, Logout</a>
        </div>
        <div class="logout-modal-hint">
            Press <span class="lm-kbd">Esc</span> to cancel &bull; <span class="lm-kbd">Enter</span> to confirm
        </div>
    </div>
</div>

    <!-- Notification DETAIL Popup (opens when any notif-item is clicked) -->
    <div class="notif-detail-overlay" id="notifDetailOverlay">
        <div class="notif-detail-modal">
            <div class="notif-detail-icon" id="notifDetailIcon">
                <i class="fa-solid fa-bell" id="notifDetailIconGlyph"></i>
            </div>
            <div class="notif-detail-title" id="notifDetailTitle"></div>
            <div class="notif-detail-message" id="notifDetailMessage"></div>
            <div class="notif-detail-time" id="notifDetailTime"></div>
            <div class="notif-detail-actions">
                <button type="button" class="btn-notif-exit" id="notifDetailExitBtn">Exit</button>
                <a href="#" class="btn-notif-goto" id="notifDetailGotoBtn">View More</a>
            </div>
        </div>
    </div>

    <script>
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

        (function() {
            const toggleBtn = document.getElementById('forecastSubmenuToggle');
            const submenu = document.getElementById('forecastSubmenu');
            if (!toggleBtn || !submenu) return;

            toggleBtn.addEventListener('click', () => {
                submenu.classList.toggle('open');
                toggleBtn.classList.toggle('open');
            });
        })();

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
            wireGroup('cmsSubmenuToggle', 'cmsSubmenu');
        })();

        (function() {
            const pairs = [
                ['notifBtnDesktop', 'notifPanelDesktop'],
                ['notifBtnMobile', 'notifPanelMobile'],
            ];

            pairs.forEach(([btnId, panelId]) => {
                const btn = document.getElementById(btnId);
                const panel = document.getElementById(panelId);
                if (!btn || !panel) return;

                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const isOpen = panel.classList.contains('open');
                    document.querySelectorAll('.notif-panel.open').forEach(p => p.classList.remove('open'));
                    document.querySelectorAll('.notif-btn.open').forEach(b => b.classList.remove('open'));
                    if (!isOpen) {
                        panel.classList.add('open');
                        btn.classList.add('open');
                    }
                });
            });

            document.addEventListener('click', (e) => {
                if (!e.target.closest('.notif-trigger')) {
                    document.querySelectorAll('.notif-panel.open').forEach(p => p.classList.remove('open'));
                    document.querySelectorAll('.notif-btn.open').forEach(b => b.classList.remove('open'));
                }
            });
        })();

        (function() {
            const NOTIF_READ_URL_PATTERN = @json(route('notifications.read', ['notification' => '__ID__']));

            const overlay     = document.getElementById('notifDetailOverlay');
            const iconWrap    = document.getElementById('notifDetailIcon');
            const iconGlyph   = document.getElementById('notifDetailIconGlyph');
            const titleEl     = document.getElementById('notifDetailTitle');
            const messageEl   = document.getElementById('notifDetailMessage');
            const timeEl      = document.getElementById('notifDetailTime');
            const exitBtn     = document.getElementById('notifDetailExitBtn');
            const gotoBtn     = document.getElementById('notifDetailGotoBtn');
            if (!overlay) return;

            function openDetail(item) {
                const title   = item.dataset.title || 'Notification';
                const message = item.dataset.message || '';
                const time    = item.dataset.time || '';
                const url     = item.dataset.url && item.dataset.url !== '#' ? item.dataset.url : '/inventory';
                const type    = item.dataset.type || 'reservation';

                titleEl.textContent   = title;
                messageEl.textContent = message;
                timeEl.textContent    = time;
                gotoBtn.href          = url;

                iconWrap.className  = 'notif-detail-icon type-' + type;
                iconGlyph.className = 'fa-solid ' + (['low_stock', 'inventory'].includes(type) ? 'fa-boxes-stacked' : (type === 'pos' ? 'fa-receipt' : 'fa-ticket'));

                const wasUnread = item.classList.contains('unread');
                item.classList.remove('unread');
                item.querySelector('.notif-dot')?.remove();

                if (wasUnread) {
                    const id = item.dataset.id;
                    const readUrl = item.dataset.readUrl
                        || (id ? NOTIF_READ_URL_PATTERN.replace('__ID__', id) : null);
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    if (readUrl) {
                        fetch(readUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                        }).catch(() => {});
                    }
                }

                overlay.classList.add('active');
                document.querySelectorAll('.notif-panel.open').forEach(p => p.classList.remove('open'));
                document.querySelectorAll('.notif-btn.open').forEach(b => b.classList.remove('open'));
            }

            function closeDetail() {
                overlay.classList.remove('active');
            }

            document.addEventListener('click', (e) => {
                if (e.target.closest('.notif-row-actions')) return;
                const item = e.target.closest('.notif-item, .notif-row');
                if (!item) return;
                openDetail(item);
            });

            document.addEventListener('keydown', (e) => {
                if (e.key !== 'Enter' && e.key !== ' ') return;
                if (e.target.closest('.notif-row-actions')) return;
                const item = e.target.closest('.notif-item, .notif-row');
                if (!item) return;
                e.preventDefault();
                openDetail(item);
            });

            exitBtn.addEventListener('click', closeDetail);
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) closeDetail();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeDetail();
            });
        })();

        function markAllNotifsRead(btn) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            }).catch(() => {});

            document.querySelectorAll('.notif-item.unread, .nav-notif-item.unread').forEach(item => item.classList.remove('unread'));
            document.querySelectorAll('.notif-dot').forEach(dot => dot.remove());
            document.querySelectorAll('.notif-badge').forEach(badge => badge.remove());
            // All notifications are now read, so remove the persistent highlight from both bell buttons.
            document.querySelectorAll('.notif-btn').forEach(b => b.classList.remove('has-unread'));
            document.getElementById('navNotifLink')?.classList.remove('has-unread');
            const navCount = document.getElementById('navNotifCount');
            if (navCount) {
                navCount.textContent = '0';
                navCount.classList.add('is-zero');
            }
            btn.remove();
        }

        function openLogoutModal() {
            document.getElementById('logoutModalOverlay').classList.add('active');
        }

        function closeLogoutModal() {
            document.getElementById('logoutModalOverlay').classList.remove('active');
        }

        document.getElementById('logoutModalOverlay')?.addEventListener('click', function(e) {
            if (e.target === this) closeLogoutModal();
        });
        document.addEventListener('keydown', function(e) {
        const overlay = document.getElementById('logoutModalOverlay');
        if (!overlay || !overlay.classList.contains('active')) return;

        if (e.key === 'Escape') {
            closeLogoutModal();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            window.location.href = '/logout';
        }
    });
    </script>
<script>
(function() {
    @if (!$canViewNotifications)
        return;
    @endif

    const iconMap = { low_stock: 'fa-boxes-stacked', inventory: 'fa-boxes-stacked', pos: 'fa-receipt' };
    const NOTIF_POLL_URL = @json(route('notifications.poll'));

    // ---- Badge + bell highlight helpers ----
    function setBadgeCount(btnSel, count) {
        const btn = document.querySelector(btnSel);
        if (!btn) return;

        let el = btn.querySelector('.notif-badge');

        if (count <= 0) {
            el?.remove();
            btn.classList.remove('has-unread');
            return;
        }

        if (!el) {
            el = document.createElement('span');
            el.className = 'notif-badge';
            btn.appendChild(el);
        }
        el.textContent = count > 9 ? '9+' : count;
        btn.classList.add('has-unread');
    }

    function buildItemHtml(n) {
        const icon = iconMap[n.type] || 'fa-ticket';
        const unreadClass = n.is_read ? '' : 'unread';
        const dot = n.is_read ? '' : '<span class="notif-dot"></span>';
        return `
            <div class="notif-item ${unreadClass}" role="button" tabindex="0"
                 data-id="${n.id}" data-read-url="${n.read_url}"
                 data-title="${n.title}" data-message="${n.message}"
                 data-time="${n.time}" data-url="${n.url || '#'}" data-type="${n.type}">
                <div class="notif-icon type-${n.type}">
                    <i class="fa-solid ${icon}"></i>
                </div>
                <div class="notif-body">
                    <div class="notif-title-row">
                        <span class="notif-title">${n.title}</span>
                        ${dot}
                    </div>
                    <div class="notif-message">${n.message}</div>
                    <div class="notif-time">${n.time}</div>
                </div>
            </div>`;
    }

    function renderList(sel, notifications) {
        const list = document.querySelector(sel);
        if (!list) return;

        if (!notifications.length) {
            list.innerHTML = '<div class="notif-empty"><span class="notif-empty-text">No notifications yet.</span></div>';
            return;
        }

        list.innerHTML = notifications.map(buildItemHtml).join('');
    }

    // ---- Core refresh routine, used by both polling and manual triggers ----
        window.refreshNotifications = function refreshNotifications() {
        fetch(NOTIF_POLL_URL, {
            headers: { 'Accept': 'application/json' },
        })
            .then(r => r.json())
            .then(data => {
                setBadgeCount('#notifBtnDesktop', data.unread_count);
                setBadgeCount('#notifBtnMobile', data.unread_count);
                renderList('#notifPanelDesktop .notif-list', data.notifications);
                renderList('#notifPanelMobile .notif-list', data.notifications);

                const navCount = document.getElementById('navNotifCount');
                const navLink = document.getElementById('navNotifLink');
                const hasUnread = data.unread_count > 0;

                if (navCount) {
                    navCount.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
                    navCount.classList.toggle('is-zero', !hasUnread);
                }
                if (navLink) {
                    navLink.classList.toggle('has-unread', hasUnread);
                }
            })
            .catch(() => {});
    };

    // Poll every 15 seconds for new notifications, no full page reload needed.
    setInterval(refreshNotifications, 15000);

    // Real-time push via Echo, if configured, updates instantly on top of polling.
    if (typeof Echo !== 'undefined') {
        Echo.private('admin-notifications')
            .listen('.new-notification', () => refreshNotifications());
    }
})();
</script>

 
    @stack('scripts')

</body>

</html>