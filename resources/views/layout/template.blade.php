<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DENR XI - DOCUMENT TRACKING SYSTEM</title>
    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root,
        [data-theme="light"] {
            --green-dark: #1a5c2a;
            --green: #2d9e4a;
            --green-light: #3dbf5e;
            --green-soft: #e8f5ec;
            --green-muted: #a7d7b3;
            --blue: #1a56db;
            --blue-light: #3b82f6;
            --blue-soft: #e8f0fe;
            --sb-bg: #ffffff;
            --sb-border: #e8edf5;
            --sb-text: #374151;
            --sb-muted: #9ca3af;
            --sb-hover-bg: #f0faf2;
            --sb-active-bg: #e4f5e9;
            --sb-active-tx: #1a5c2a;
            --sb-label: #b0bac8;
            --sb-icon: #2d9e4a;
            --sb-sub-dot: #a7d7b3;
            --bg: #f3f6fa;
            --card-bg: #ffffff;
            --topbar-bg: #ffffff;
            --border: #dde3ed;
            --text: #1a2332;
            --muted: #6b7b94;
            --input-bg: #f0f4f8;
            --shadow: 0 1px 3px rgba(0, 0, 0, .07), 0 4px 14px rgba(0, 0, 0, .05);
            --shadow-md: 0 4px 20px rgba(0, 0, 0, .1);
        }

        [data-theme="dark"] {
            --sb-bg: #111827;
            --sb-border: #1f2937;
            --sb-text: #d1d5db;
            --sb-muted: #6b7280;
            --sb-hover-bg: #1a2e1e;
            --sb-active-bg: #1a3626;
            --sb-active-tx: #3dbf5e;
            --sb-label: #4b5563;
            --sb-icon: #3dbf5e;
            --sb-sub-dot: #2d9e4a;
            --bg: #0f172a;
            --card-bg: #1e293b;
            --topbar-bg: #1e293b;
            --border: #2d3748;
            --text: #e2e8f0;
            --muted: #94a3b8;
            --input-bg: #273044;
            --shadow: 0 1px 3px rgba(0, 0, 0, .3), 0 4px 14px rgba(0, 0, 0, .2);
            --shadow-md: 0 4px 20px rgba(0, 0, 0, .35);
        }

        :root {
            --sidebar-w: 258px;
            --radius: 10px;
            --font-head: 'Plus Jakarta Sans', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
        }

        html,
        body {
            height: 100%;
        }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--font-head);
            display: flex;
            overflow: hidden;
            transition: background .3s, color .3s;
            margin: 0;
        }

        /* Override Bootstrap defaults */
        .btn-success {
            background-color: var(--green) !important;
            border-color: var(--green) !important;
        }
        .btn-success:hover {
            background-color: var(--green-dark) !important;
            border-color: var(--green-dark) !important;
        }
        .btn-outline-success {
            color: var(--green) !important;
            border-color: var(--green) !important;
        }
        .btn-outline-success:hover {
            background-color: var(--green-soft) !important;
            color: var(--green-dark) !important;
        }
        .btn-primary {
            background-color: var(--blue) !important;
            border-color: var(--blue) !important;
        }
        .btn-primary:hover {
            background-color: #1245b5 !important;
            border-color: #1245b5 !important;
        }
        .badge-success {
            background-color: #dcfce7 !important;
            color: #15803d !important;
        }
        .badge-warning {
            background-color: #fef9c3 !important;
            color: #a16207 !important;
        }
        .badge-danger {
            background-color: #fee2e2 !important;
            color: #dc2626 !important;
        }
        .badge-info {
            background-color: #dbeafe !important;
            color: #1d4ed8 !important;
        }
        [data-theme="dark"] .badge-success {
            background-color: rgba(21, 128, 61, .2) !important;
            color: #4ade80 !important;
        }
        [data-theme="dark"] .badge-warning {
            background-color: rgba(161, 98, 7, .2) !important;
            color: #fbbf24 !important;
        }
        [data-theme="dark"] .badge-danger {
            background-color: rgba(220, 38, 38, .2) !important;
            color: #f87171 !important;
        }
        [data-theme="dark"] .badge-info {
            background-color: rgba(29, 78, 216, .2) !important;
            color: #60a5fa !important;
        }
        .card {
            background-color: var(--card-bg) !important;
            border-color: var(--border) !important;
            box-shadow: var(--shadow);
            transition: background .3s, border-color .3s;
        }
        .card-header {
            background-color: transparent !important;
            border-bottom-color: var(--border) !important;
        }
        .table {
            color: var(--text) !important;
        }
        .table thead th {
            border-bottom-color: var(--border) !important;
            background: var(--input-bg);
            font-family: var(--font-mono);
            font-size: 10px;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--muted);
            font-weight: 600;
        }
        .table td {
            border-bottom-color: var(--border) !important;
            vertical-align: middle;
        }
        .table tbody tr:hover {
            background: var(--green-soft);
        }
        [data-theme="dark"] .table tbody tr:hover {
            background: var(--sb-active-bg);
        }
        .form-control {
            background-color: var(--input-bg) !important;
            border-color: var(--border) !important;
            color: var(--text) !important;
            font-family: var(--font-head);
            font-size: 13px;
            border-radius: 8px;
            transition: border-color .17s, box-shadow .17s;
        }
        .form-control:focus {
            border-color: var(--green) !important;
            box-shadow: 0 0 0 3px rgba(45, 158, 74, .12) !important;
        }
        .form-control::placeholder {
            color: var(--muted);
            font-size: 12.5px;
        }
        .alert-success {
            background-color: var(--green-soft) !important;
            color: var(--green-dark) !important;
            border-color: var(--green-muted) !important;
        }
        .alert-info {
            background-color: var(--blue-soft) !important;
            color: #1a56db !important;
            border-color: #bfdbfe !important;
        }
        .alert-warning {
            background-color: #fef9c3 !important;
            color: #a16207 !important;
            border-color: #fde68a !important;
        }
        .alert-danger {
            background-color: #fee2e2 !important;
            color: #dc2626 !important;
            border-color: #fca5a5 !important;
        }
        [data-theme="dark"] .alert-success {
            background-color: rgba(45, 158, 74, .15) !important;
            color: #6ee7b7 !important;
            border-color: rgba(45, 158, 74, .3) !important;
        }
        [data-theme="dark"] .alert-info {
            background-color: rgba(26, 86, 219, .15) !important;
            color: #93c5fd !important;
            border-color: rgba(26, 86, 219, .3) !important;
        }
        [data-theme="dark"] .alert-warning {
            background-color: rgba(161, 98, 7, .15) !important;
            color: #fcd34d !important;
            border-color: rgba(161, 98, 7, .3) !important;
        }
        [data-theme="dark"] .alert-danger {
            background-color: rgba(220, 38, 38, .15) !important;
            color: #f87171 !important;
            border-color: rgba(220, 38, 38, .3) !important;
        }
        .modal-content {
            background-color: var(--card-bg) !important;
            border-color: var(--border) !important;
        }
        .modal-header {
            border-bottom-color: var(--border) !important;
        }
        .modal-footer {
            border-top-color: var(--border) !important;
        }
        .nav-tabs {
            border-bottom-color: var(--border) !important;
        }
        .nav-tabs .nav-link {
            color: var(--muted);
            font-weight: 600;
            font-size: 12.5px;
            border: none;
            border-bottom: 2px solid transparent;
            padding: 9px 16px;
            transition: all .17s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .nav-tabs .nav-link:hover {
            color: var(--green);
            border-bottom-color: transparent;
        }
        .nav-tabs .nav-link.active {
            color: var(--green) !important;
            background: transparent !important;
            border-bottom: 2px solid var(--green) !important;
            font-weight: 700;
        }
        .nav-pills {
            background: var(--input-bg);
            border-radius: 10px;
            padding: 4px;
            display: inline-flex;
            gap: 2px;
        }
        .nav-pills .nav-link {
            border-radius: 8px !important;
            padding: 7px 14px;
            font-weight: 600;
            font-size: 12.5px;
            color: var(--muted);
        }
        .nav-pills .nav-link.active {
            background: var(--card-bg) !important;
            color: var(--green) !important;
            box-shadow: var(--shadow);
        }
        .progress {
            background-color: var(--border) !important;
            height: 5px;
            border-radius: 10px;
        }
        .progress-bar {
            border-radius: 10px;
        }
        .dropdown-menu {
            background-color: var(--card-bg) !important;
            border-color: var(--border) !important;
            box-shadow: var(--shadow-md);
        }
        .dropdown-item {
            color: var(--text) !important;
        }
        .dropdown-item:hover {
            background-color: var(--green-soft) !important;
            color: var(--green) !important;
        }
        [data-theme="dark"] .dropdown-item:hover {
            background-color: var(--sb-active-bg) !important;
        }
        .dropdown-divider {
            border-top-color: var(--border) !important;
        }
        .close {
            color: var(--muted) !important;
            text-shadow: none;
            opacity: .7;
        }
        .close:hover {
            color: var(--text) !important;
            opacity: 1;
        }

        /* Overlay */
        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 99;
            backdrop-filter: blur(2px);
        }
        .overlay.show {
            display: block;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: var(--sb-bg);
            border-right: 1px solid var(--sb-border);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            position: relative;
            z-index: 100;
            transition: transform .28s cubic-bezier(.4, 0, .2, 1), background .3s, border-color .3s;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--sb-border) transparent;
            box-shadow: 2px 0 12px rgba(0, 0, 0, .06);
        }
        .sidebar::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: var(--sb-border);
            border-radius: 4px;
        }
        .logo {
            padding: 20px 18px 16px;
            border-bottom: 1px solid var(--sb-border);
            display: flex;
            align-items: center;
            gap: 11px;
        }
        .logo-emblem {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--green), var(--blue-light));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 3px 10px rgba(45, 158, 74, .35);
        }
        .logo-emblem svg {
            width: 20px;
            height: 20px;
            color: #fff;
        }
        .logo-text .l1 {
            font-size: 14px;
            font-weight: 800;
            color: var(--green-dark);
            letter-spacing: .06em;
        }
        [data-theme="dark"] .logo-text .l1 {
            color: var(--green-light);
        }
        .logo-text .l2 {
            font-size: 10px;
            color: var(--sb-muted);
            font-weight: 500;
        }
        nav {
            padding: 12px 10px 10px;
            flex: 1;
        }
        .nav-section-label {
            font-size: 9px;
            font-family: var(--font-mono);
            letter-spacing: .15em;
            text-transform: uppercase;
            color: var(--sb-label);
            padding: 10px 10px 5px;
            display: block;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 10px;
            border-radius: 8px;
            cursor: pointer;
            color: var(--sb-text);
            font-size: 13px;
            font-weight: 600;
            transition: all .17s;
            margin-bottom: 1px;
            position: relative;
            text-decoration: none;
            user-select: none;
        }
        .nav-item:hover {
            background: var(--sb-hover-bg);
            color: var(--green);
            text-decoration: none;
        }
        .nav-item.active {
            background: var(--sb-active-bg);
            color: var(--sb-active-tx);
        }
        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 18%;
            bottom: 18%;
            width: 3px;
            background: var(--green);
            border-radius: 0 3px 3px 0;
        }
        .nav-icon {
            width: 17px;
            height: 17px;
            flex-shrink: 0;
            color: var(--sb-icon);
        }
        .nav-badge {
            margin-left: auto;
            background: #ef4444;
            color: #fff;
            font-family: var(--font-mono);
            font-size: 9px;
            padding: 2px 6px;
            border-radius: 20px;
        }
        .nav-badge-new {
            margin-left: auto;
            background: linear-gradient(135deg, var(--green), var(--blue-light));
            color: #fff;
            font-family: var(--font-mono);
            font-size: 9px;
            padding: 2px 7px;
            border-radius: 20px;
            letter-spacing: .04em;
        }
        .nav-group {
            margin-bottom: 1px;
        }
        .nav-group-trigger {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 10px;
            border-radius: 8px;
            cursor: pointer;
            color: var(--sb-text);
            font-size: 13px;
            font-weight: 600;
            transition: all .17s;
            user-select: none;
        }
        .nav-group-trigger:hover {
            background: var(--sb-hover-bg);
            color: var(--green);
        }
        .nav-group-trigger.open {
            background: var(--sb-active-bg);
            color: var(--sb-active-tx);
        }
        .nav-chevron {
            margin-left: auto;
            width: 14px;
            height: 14px;
            color: var(--sb-muted);
            transition: transform .22s;
            flex-shrink: 0;
        }
        .nav-group-trigger.open .nav-chevron {
            transform: rotate(180deg);
        }
        .nav-submenu {
            overflow: hidden;
            max-height: 0;
            transition: max-height .28s cubic-bezier(.4, 0, .2, 1);
        }
        .nav-submenu.open {
            max-height: 400px;
        }
        .nav-sub-item {
            display: flex;
            align-items: center;
            padding: 7px 10px 7px 38px;
            border-radius: 7px;
            cursor: pointer;
            color: var(--sb-muted);
            font-size: 12.5px;
            font-weight: 500;
            transition: all .15s;
            text-decoration: none;
            margin-bottom: 1px;
            position: relative;
        }
        .nav-sub-item::before {
            content: '';
            position: absolute;
            left: 22px;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: var(--sb-sub-dot);
            transition: background .15s;
        }
        .nav-sub-item:hover {
            background: var(--sb-hover-bg);
            color: var(--green);
            text-decoration: none;
        }
        .nav-sub-item:hover::before {
            background: var(--green);
        }
        .nav-sub-item.active {
            color: var(--green);
            background: var(--sb-active-bg);
        }
        .nav-sub-item.active::before {
            background: var(--green);
        }
        .sidebar-footer {
            padding: 12px 10px;
            border-top: 1px solid var(--sb-border);
        }
        .user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 10px;
            border-radius: 8px;
            background: var(--sb-hover-bg);
            cursor: pointer;
            transition: background .17s;
        }
        .user-card:hover {
            background: var(--sb-active-bg);
        }
        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--green), var(--blue-light));
            display: grid;
            place-items: center;
            font-size: 12px;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }
        .user-info {
            flex: 1;
            min-width: 0;
        }
        .user-name {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--sb-active-tx);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        [data-theme="dark"] .user-name {
            color: var(--green-light);
        }
        .user-role {
            font-size: 10px;
            color: var(--sb-muted);
            font-family: var(--font-mono);
        }

        /* Main */
        .main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-width: 0;
        }
        .topbar {
            height: 62px;
            background: var(--topbar-bg);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 22px;
            gap: 14px;
            flex-shrink: 0;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
            transition: background .3s, border-color .3s;
            position: relative;
            z-index: 50;
        }
        .hamburger {
            width: 36px;
            height: 36px;
            border: none;
            background: none;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
            border-radius: 8px;
            transition: background .17s;
            padding: 0;
            flex-shrink: 0;
        }
        .hamburger:hover {
            background: var(--green-soft);
        }
        .hamburger span {
            display: block;
            width: 20px;
            height: 2px;
            background: var(--green-dark);
            border-radius: 2px;
            transition: all .25s cubic-bezier(.4, 0, .2, 1);
            transform-origin: center;
        }
        [data-theme="dark"] .hamburger span {
            background: var(--green-light);
        }
        .hamburger.active span:nth-child(1) {
            transform: translateY(7px) rotate(45deg);
        }
        .hamburger.active span:nth-child(2) {
            opacity: 0;
            transform: scaleX(0);
        }
        .hamburger.active span:nth-child(3) {
            transform: translateY(-7px) rotate(-45deg);
        }
        .topbar-brand {
            font-size: 15px;
            font-weight: 800;
            color: var(--green-dark);
            letter-spacing: .05em;
            white-space: nowrap;
        }
        [data-theme="dark"] .topbar-brand {
            color: var(--green-light);
        }
        .topbar-brand span {
            color: var(--blue);
        }
        .topbar-sep {
            width: 1px;
            height: 22px;
            background: var(--border);
            flex-shrink: 0;
        }
        .breadcrumb-custom {
            font-size: 12.5px;
            color: var(--muted);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 0;
        }
        .breadcrumb-custom strong {
            color: var(--text);
            font-weight: 700;
        }
        .search-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--input-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 7px 14px;
            width: 210px;
            margin-left: auto;
            transition: border-color .17s, background .3s;
        }
        .search-bar:focus-within {
            border-color: var(--green);
        }
        .search-bar input {
            background: none;
            border: none;
            outline: none;
            color: var(--text);
            font-family: var(--font-mono);
            font-size: 12px;
            width: 100%;
            padding: 0;
        }
        .search-bar input::placeholder {
            color: var(--muted);
        }
        .icon-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: var(--input-bg);
            border: 1px solid var(--border);
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: all .17s;
            position: relative;
            flex-shrink: 0;
        }
        .icon-btn:hover {
            border-color: var(--green);
            background: var(--green-soft);
        }
        .notif-dot {
            position: absolute;
            top: 7px;
            right: 7px;
            width: 7px;
            height: 7px;
            background: #ef4444;
            border-radius: 50%;
            border: 1.5px solid var(--topbar-bg);
        }
        .dark-toggle {
            width: 44px;
            height: 24px;
            border-radius: 999px;
            background: var(--border);
            border: none;
            cursor: pointer;
            position: relative;
            transition: background .25s;
            flex-shrink: 0;
            padding: 0;
        }
        .dark-toggle.on {
            background: var(--green);
        }
        .dark-toggle::after {
            content: '';
            position: absolute;
            top: 3px;
            left: 3px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #fff;
            transition: transform .25s;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .2);
        }
        .dark-toggle.on::after {
            transform: translateX(20px);
        }
        .toggle-wrap {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 0 4px;
        }
        .toggle-icon {
            width: 16px;
            height: 16px;
            color: var(--muted);
            flex-shrink: 0;
        }
        .profile-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 5px 10px 5px 5px;
            border-radius: 8px;
            cursor: pointer;
            border: 1px solid var(--border);
            background: var(--input-bg);
            transition: all .17s;
            position: relative;
            flex-shrink: 0;
        }
        .profile-btn:hover {
            border-color: var(--green);
            background: var(--green-soft);
        }
        .profile-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--green), var(--blue-light));
            display: grid;
            place-items: center;
            font-size: 11px;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }
        .profile-name {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--text);
            white-space: nowrap;
        }
        .profile-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: 230px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            z-index: 200;
            overflow: hidden;
            animation: dropIn .18s ease;
        }
        .profile-dropdown.open {
            display: block;
        }
        @keyframes dropIn {
            from {
                opacity: 0;
                transform: translateY(-6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .pd-header {
            padding: 14px 16px 12px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .pd-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--green), var(--blue-light));
            display: grid;
            place-items: center;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }
        .pd-name {
            font-size: 13px;
            font-weight: 700;
            color: var(--text);
        }
        .pd-role {
            font-size: 11px;
            color: var(--muted);
            font-family: var(--font-mono);
        }
        .pd-body {
            padding: 6px 8px;
        }
        .pd-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 10px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            cursor: pointer;
            transition: background .15s;
            text-decoration: none;
        }
        .pd-item svg {
            width: 16px;
            height: 16px;
            color: var(--green);
            flex-shrink: 0;
        }
        .pd-item:hover {
            background: var(--green-soft);
            color: var(--green);
            text-decoration: none;
        }
        [data-theme="dark"] .pd-item:hover {
            background: var(--sb-active-bg);
        }
        .pd-divider {
            height: 1px;
            background: var(--border);
            margin: 4px 8px;
        }
        .pd-item.danger {
            color: #ef4444;
        }
        .pd-item.danger svg {
            color: #ef4444;
        }
        .pd-item.danger:hover {
            background: #fee2e2;
        }

        /* Content */
        .content {
            flex: 1;
            overflow-y: auto;
            padding: 24px 26px;
            display: flex;
            flex-direction: column;
            gap: 22px;
        }
        .content::-webkit-scrollbar {
            width: 5px;
        }
        .content::-webkit-scrollbar-track {
            background: var(--bg);
        }
        .content::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 5px;
        }
        .page {
            display: none;
            flex-direction: column;
            gap: 22px;
        }
        .page.active {
            display: flex;
        }

        /* Section header */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .section-title {
            font-size: 10px;
            font-family: var(--font-mono);
            letter-spacing: .15em;
            text-transform: uppercase;
            color: var(--muted);
        }
        .section-link {
            font-size: 12px;
            color: var(--blue);
            font-weight: 600;
            text-decoration: none;
        }
        .section-link:hover {
            text-decoration: underline;
            color: var(--blue);
        }

        /* Stat cards */
        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            transition: transform .2s, box-shadow .2s, background .3s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }
        .stat-card:nth-child(1)::before {
            background: linear-gradient(90deg, #1a5c2a, #3dbf5e);
        }
        .stat-card:nth-child(2)::before {
            background: linear-gradient(90deg, #1a56db, #3b82f6);
        }
        .stat-card:nth-child(3)::before {
            background: linear-gradient(90deg, #237a38, #3dbf5e);
        }
        .stat-card:nth-child(4)::before {
            background: linear-gradient(90deg, #0a2a6e, #1a56db);
        }
        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            margin-bottom: 12px;
        }
        .stat-card:nth-child(1) .stat-icon {
            background: var(--green-soft);
        }
        .stat-card:nth-child(2) .stat-icon {
            background: var(--blue-soft);
        }
        .stat-card:nth-child(3) .stat-icon {
            background: var(--green-soft);
        }
        .stat-card:nth-child(4) .stat-icon {
            background: var(--blue-soft);
        }
        .stat-card:nth-child(1) .stat-icon svg,
        .stat-card:nth-child(3) .stat-icon svg {
            color: var(--green);
        }
        .stat-card:nth-child(2) .stat-icon svg,
        .stat-card:nth-child(4) .stat-icon svg {
            color: var(--blue);
        }
        .stat-label {
            font-size: 11px;
            color: var(--muted);
            font-weight: 600;
            margin-bottom: 5px;
            letter-spacing: .02em;
        }
        .stat-value {
            font-size: 28px;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 10px;
            letter-spacing: -.02em;
            color: var(--text);
        }
        .stat-footer {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            color: var(--muted);
        }
        .stat-delta {
            font-size: 11px;
            font-family: var(--font-mono);
            padding: 2px 7px;
            border-radius: 4px;
            font-weight: 600;
        }
        .delta-up {
            background: #dcfce7;
            color: #15803d;
        }
        .delta-down {
            background: #fee2e2;
            color: #dc2626;
        }
        [data-theme="dark"] .delta-up {
            background: rgba(21, 128, 61, .2);
            color: #4ade80;
        }
        [data-theme="dark"] .delta-down {
            background: rgba(220, 38, 38, .2);
            color: #f87171;
        }

        /* Chart */
        .chart-placeholder {
            padding: 14px 18px 18px;
        }
        .chart-legend {
            display: flex;
            gap: 16px;
            margin-bottom: 12px;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11.5px;
            color: var(--muted);
            font-weight: 500;
        }
        .legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }
        .chart-area {
            width: 100%;
            height: 185px;
        }
        .chart-svg {
            width: 100%;
            height: 100%;
        }
        .chart-grid {
            stroke: var(--border);
            stroke-width: 1;
        }
        .donut-wrap {
            padding: 18px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
        }
        .donut-svg {
            width: 130px;
            height: 130px;
        }
        .donut-stats {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .donut-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
        }
        .donut-swatch {
            width: 10px;
            height: 10px;
            border-radius: 3px;
            flex-shrink: 0;
        }
        .donut-label {
            flex: 1;
            color: var(--muted);
        }
        .donut-pct {
            font-family: var(--font-mono);
            font-size: 12px;
            font-weight: 600;
            color: var(--text);
        }

        /* Table custom */
        .order-id {
            font-family: var(--font-mono);
            font-size: 12px;
            color: var(--muted);
        }
        .customer {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .customer-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 11px;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }
        .customer-name {
            font-weight: 600;
            font-size: 13px;
        }
        .customer-email {
            font-family: var(--font-mono);
            font-size: 10px;
            color: var(--muted);
        }
        .amount {
            font-family: var(--font-mono);
            font-weight: 600;
        }
        .progress-sm {
            width: 80px;
            height: 5px;
            background: var(--border);
            border-radius: 10px;
            overflow: hidden;
        }
        .progress-sm-fill {
            height: 100%;
            border-radius: 10px;
        }

        /* Activity */
        .activity-feed {
            padding: 5px 18px 14px;
        }
        .activity-item {
            display: flex;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
            margin-top: 2px;
        }
        .act-green {
            background: var(--green-soft);
            color: var(--green);
        }
        .act-blue {
            background: var(--blue-soft);
            color: var(--blue);
        }
        .act-red {
            background: #fee2e2;
            color: #dc2626;
        }
        .act-yellow {
            background: #fef9c3;
            color: #a16207;
        }
        [data-theme="dark"] .act-green {
            background: rgba(45, 158, 74, .15);
        }
        [data-theme="dark"] .act-blue {
            background: rgba(26, 86, 219, .15);
        }
        [data-theme="dark"] .act-red {
            background: rgba(220, 38, 38, .15);
            color: #f87171;
        }
        [data-theme="dark"] .act-yellow {
            background: rgba(161, 98, 7, .15);
            color: #fbbf24;
        }
        .activity-body {
            flex: 1;
        }
        .activity-text {
            font-size: 12.5px;
            line-height: 1.5;
            color: var(--text);
        }
        .activity-text strong {
            font-weight: 700;
        }
        .activity-time {
            font-family: var(--font-mono);
            font-size: 10px;
            color: var(--muted);
            margin-top: 2px;
        }

        /* Components page */
        .comp-page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding: 28px 30px 24px;
            background: linear-gradient(135deg, var(--card-bg) 0%, var(--sb-hover-bg) 100%);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: var(--shadow);
        }
        .comp-page-header h1 {
            font-size: 22px;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -.02em;
            margin-bottom: 4px;
        }
        .comp-page-header p {
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 0;
        }
        .comp-header-badge {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        .comp-hb {
            font-family: var(--font-mono);
            font-size: 10px;
            padding: 4px 10px;
            border-radius: 20px;
            border: 1px solid var(--border);
            color: var(--muted);
            background: var(--input-bg);
        }
        .comp-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: box-shadow .2s, border-color .2s;
        }
        .comp-card:hover {
            box-shadow: var(--shadow-md);
            border-color: var(--green-muted);
        }
        .comp-card-header {
            padding: 14px 18px 12px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--input-bg);
        }
        .comp-card-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .comp-card-title svg {
            width: 14px;
            height: 14px;
            color: var(--green);
        }
        .comp-card-tag {
            font-family: var(--font-mono);
            font-size: 9px;
            padding: 2px 8px;
            border-radius: 20px;
            background: var(--green-soft);
            color: var(--green-dark);
            border: 1px solid var(--green-muted);
        }
        [data-theme="dark"] .comp-card-tag {
            background: rgba(45, 158, 74, .15);
            color: var(--green-light);
            border-color: rgba(45, 158, 74, .3);
        }
        .comp-card-body {
            padding: 18px;
        }

        /* Buttons */
        .btn-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .btn-denr-primary {
            background: var(--green);
            color: #fff;
            box-shadow: 0 2px 8px rgba(45, 158, 74, .3);
            border: none;
            font-weight: 600;
            font-size: 12.5px;
            border-radius: 8px;
            padding: 8px 16px;
            transition: all .18s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }
        .btn-denr-primary:hover {
            background: var(--green-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(45, 158, 74, .4);
            color: #fff;
            text-decoration: none;
        }
        .btn-denr-outline {
            background: transparent;
            color: var(--green);
            border: 1.5px solid var(--green);
            font-weight: 600;
            font-size: 12.5px;
            border-radius: 8px;
            padding: 8px 16px;
            transition: all .18s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }
        .btn-denr-outline:hover {
            background: var(--green-soft);
            color: var(--green);
            text-decoration: none;
        }
        .btn-denr-ghost {
            background: var(--input-bg);
            color: var(--text);
            border: 1px solid var(--border);
            font-weight: 600;
            font-size: 12.5px;
            border-radius: 8px;
            padding: 8px 16px;
            transition: all .18s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }
        .btn-denr-ghost:hover {
            border-color: var(--green);
            color: var(--green);
            text-decoration: none;
        }
        .btn-denr-danger {
            background: #ef4444;
            color: #fff;
            border: none;
            font-weight: 600;
            font-size: 12.5px;
            border-radius: 8px;
            padding: 8px 16px;
            transition: all .18s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }
        .btn-denr-danger:hover {
            background: #dc2626;
            transform: translateY(-1px);
            color: #fff;
            text-decoration: none;
        }
        .btn-denr-sm {
            padding: 5px 12px;
            font-size: 11.5px;
            border-radius: 6px;
        }
        .btn-denr-lg {
            padding: 11px 22px;
            font-size: 14px;
            border-radius: 10px;
        }
        .btn-denr-icon {
            padding: 8px;
            border-radius: 8px;
        }
        .btn-loading {
            position: relative;
            color: transparent !important;
            pointer-events: none;
        }
        .btn-loading::after {
            content: '';
            position: absolute;
            width: 13px;
            height: 13px;
            border: 2px solid rgba(255, 255, 255, .3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Forms */
        .form-label {
            font-size: 11.5px;
            font-weight: 700;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .form-label .req {
            color: #ef4444;
            font-size: 10px;
        }
        .form-hint {
            font-size: 11px;
            color: var(--muted);
            font-family: var(--font-mono);
            margin-top: 4px;
            display: block;
        }
        .form-hint.err {
            color: #ef4444;
        }
        .form-hint.ok {
            color: var(--green);
        }
        .input-group-prepend .input-group-text,
        .input-group-append .input-group-text {
            background: var(--border);
            border-color: var(--border);
            color: var(--muted);
            font-family: var(--font-mono);
            font-size: 12px;
        }

        /* Toggle switch */
        .custom-switch .custom-control-label::before {
            background: var(--border);
            border-color: var(--border);
        }
        .custom-switch .custom-control-input:checked~.custom-control-label::before {
            background: var(--green);
            border-color: var(--green);
        }

        /* Range */
        .range-wrap {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .range-labels {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            font-family: var(--font-mono);
            color: var(--muted);
        }
        input[type="range"] {
            width: 100%;
            height: 5px;
            accent-color: var(--green);
            cursor: pointer;
            border-radius: 10px;
        }

        /* Dropzone */
        .dropzone {
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 36px 24px;
            text-align: center;
            cursor: pointer;
            transition: all .22s;
            background: var(--input-bg);
        }
        .dropzone:hover,
        .dropzone.drag-over {
            border-color: var(--green);
            background: var(--green-soft);
        }
        .dropzone-icon {
            width: 48px;
            height: 48px;
            background: var(--green-soft);
            border-radius: 12px;
            display: grid;
            place-items: center;
            margin: 0 auto 12px;
        }
        .dropzone-icon svg {
            width: 22px;
            height: 22px;
            color: var(--green);
        }
        .dropzone h3 {
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 5px;
        }
        .dropzone p {
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 12px;
        }
        .dropzone .dz-types {
            font-family: var(--font-mono);
            font-size: 10px;
            color: var(--muted);
        }
        .file-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 14px;
        }
        .file-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            background: var(--input-bg);
            border-radius: 8px;
            border: 1px solid var(--border);
        }
        .file-icon {
            width: 32px;
            height: 32px;
            border-radius: 7px;
            background: var(--green-soft);
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }
        .file-icon svg {
            width: 15px;
            height: 15px;
            color: var(--green);
        }
        .file-name {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text);
        }
        .file-size {
            font-size: 10.5px;
            font-family: var(--font-mono);
            color: var(--muted);
        }
        .file-remove {
            margin-left: auto;
            width: 24px;
            height: 24px;
            border-radius: 6px;
            border: none;
            background: none;
            cursor: pointer;
            display: grid;
            place-items: center;
            color: var(--muted);
            transition: all .15s;
        }
        .file-remove:hover {
            background: #fee2e2;
            color: #ef4444;
        }
        .file-progress {
            height: 3px;
            background: var(--border);
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }
        .file-progress-fill {
            height: 100%;
            background: var(--green);
            border-radius: 2px;
            transition: width 1.2s ease;
        }

        /* Accordion */
        .acc-item {
            border: 1px solid var(--border);
            border-radius: 9px;
            overflow: hidden;
            margin-bottom: 6px;
        }
        .acc-trigger {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 13px 16px;
            background: var(--input-bg);
            border: none;
            cursor: pointer;
            font-family: var(--font-head);
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            transition: background .17s;
            text-align: left;
            gap: 10px;
        }
        .acc-trigger:hover {
            background: var(--sb-hover-bg);
        }
        .acc-trigger[aria-expanded="true"] {
            background: var(--sb-active-bg);
            color: var(--sb-active-tx);
        }
        .acc-chevron {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
            color: var(--muted);
            transition: transform .22s;
        }
        .acc-trigger[aria-expanded="true"] .acc-chevron {
            transform: rotate(180deg);
            color: var(--green);
        }
        .acc-body-inner {
            padding: 14px 16px;
            font-size: 13px;
            color: var(--muted);
            line-height: 1.65;
            border-top: 1px solid var(--border);
        }

        /* Toasts */
        #toast-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .toast-custom {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 13px 16px;
            border-radius: 10px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            box-shadow: 0 8px 28px rgba(0, 0, 0, .14);
            min-width: 280px;
            max-width: 380px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text);
            animation: toastIn .3s cubic-bezier(.34, 1.56, .64, 1);
        }
        .toast-custom.removing {
            animation: toastOut .25s ease forwards;
        }
        @keyframes toastIn {
            from {
                opacity: 0;
                transform: translateX(60px) scale(.9);
            }
            to {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
        }
        @keyframes toastOut {
            from {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
            to {
                opacity: 0;
                transform: translateX(60px) scale(.9);
            }
        }
        .toast-icon-wrap {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }
        .toast-icon-wrap svg {
            width: 14px;
            height: 14px;
        }
        .toast-success .toast-icon-wrap {
            background: var(--green-soft);
            color: var(--green);
        }
        .toast-error .toast-icon-wrap {
            background: #fee2e2;
            color: #ef4444;
        }
        .toast-info .toast-icon-wrap {
            background: var(--blue-soft);
            color: var(--blue);
        }
        .toast-warning .toast-icon-wrap {
            background: #fef9c3;
            color: #a16207;
        }
        .toast-msg {
            flex: 1;
        }
        .toast-title {
            font-weight: 700;
            font-size: 12.5px;
        }
        .toast-sub {
            font-size: 11.5px;
            color: var(--muted);
            margin-top: 1px;
        }
        .toast-close-btn {
            width: 22px;
            height: 22px;
            border-radius: 5px;
            border: none;
            background: none;
            cursor: pointer;
            color: var(--muted);
            display: grid;
            place-items: center;
            transition: all .15s;
        }
        .toast-close-btn:hover {
            background: var(--input-bg);
            color: var(--text);
        }

        /* Tooltip */
        .tooltip-wrap {
            position: relative;
            display: inline-flex;
        }
        .tooltip-wrap .tooltip-box {
            position: absolute;
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%);
            background: #1a2332;
            color: #fff;
            font-size: 11.5px;
            font-weight: 500;
            padding: 6px 10px;
            border-radius: 7px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity .18s;
            z-index: 100;
        }
        [data-theme="dark"] .tooltip-wrap .tooltip-box {
            background: #e2e8f0;
            color: #1a2332;
        }
        .tooltip-wrap .tooltip-box::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 5px solid transparent;
            border-top-color: #1a2332;
        }
        [data-theme="dark"] .tooltip-wrap .tooltip-box::after {
            border-top-color: #e2e8f0;
        }
        .tooltip-wrap:hover .tooltip-box {
            opacity: 1;
        }

        /* Avatars & Chips */
        .avatar-group {
            display: flex;
        }
        .av {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 12px;
            font-weight: 700;
            color: #fff;
            border: 2.5px solid var(--card-bg);
            margin-left: -8px;
        }
        .av:first-child {
            margin-left: 0;
        }
        .av-more {
            background: var(--input-bg);
            color: var(--muted);
            font-size: 11px;
        }
        .chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 600;
            background: var(--input-bg);
            border: 1px solid var(--border);
            color: var(--text);
            cursor: default;
        }
        .chip .chip-x {
            width: 14px;
            height: 14px;
            cursor: pointer;
            display: grid;
            place-items: center;
            border-radius: 50%;
            color: var(--muted);
        }
        .chip .chip-x:hover {
            background: #fee2e2;
            color: #ef4444;
        }
        .chip-green {
            background: var(--green-soft);
            color: var(--green-dark);
            border-color: var(--green-muted);
        }
        .chip-blue {
            background: var(--blue-soft);
            color: #1a56db;
            border-color: #93c5fd;
        }
        [data-theme="dark"] .chip-green {
            background: rgba(45, 158, 74, .15);
            color: var(--green-light);
            border-color: rgba(45, 158, 74, .3);
        }
        [data-theme="dark"] .chip-blue {
            background: rgba(59, 130, 246, .15);
            color: #93c5fd;
            border-color: rgba(59, 130, 246, .3);
        }

        /* Steps */
        .steps {
            display: flex;
            align-items: center;
        }
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            position: relative;
        }
        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 14px;
            left: 50%;
            right: -50%;
            height: 2px;
            background: var(--border);
            z-index: 0;
        }
        .step.done:not(:last-child)::after {
            background: var(--green);
        }
        .step-dot {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 11px;
            font-weight: 700;
            border: 2px solid var(--border);
            background: var(--card-bg);
            color: var(--muted);
            z-index: 1;
            position: relative;
        }
        .step.done .step-dot {
            background: var(--green);
            border-color: var(--green);
            color: #fff;
        }
        .step.active .step-dot {
            border-color: var(--green);
            color: var(--green);
            background: var(--green-soft);
        }
        .step-label {
            font-size: 10px;
            font-family: var(--font-mono);
            color: var(--muted);
            margin-top: 6px;
            text-align: center;
        }
        .step.done .step-label {
            color: var(--green);
        }
        .step.active .step-label {
            color: var(--green);
            font-weight: 600;
        }

        /* Skeleton */
        .skel {
            background: linear-gradient(90deg, var(--border) 25%, var(--input-bg) 50%, var(--border) 75%);
            background-size: 200% 100%;
            animation: skel-shine 1.5s infinite;
            border-radius: 6px;
        }
        @keyframes skel-shine {
            to {
                background-position: -200% 0;
            }
        }

        /* Mini stats */
        .mini-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        .mini-stat {
            background: var(--input-bg);
            border: 1px solid var(--border);
            border-radius: 9px;
            padding: 14px 16px;
        }
        .mini-stat-label {
            font-size: 10.5px;
            color: var(--muted);
            font-family: var(--font-mono);
            margin-bottom: 5px;
        }
        .mini-stat-val {
            font-size: 21px;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -.02em;
        }
        .mini-stat-sub {
            font-size: 10.5px;
            color: var(--green);
            font-weight: 600;
            margin-top: 3px;
        }

        /* Timeline */
        .timeline {
            position: relative;
            padding-left: 22px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 5px;
            top: 8px;
            bottom: 8px;
            width: 2px;
            background: var(--border);
            border-radius: 2px;
        }
        .tl-item {
            position: relative;
            padding-left: 20px;
            padding-bottom: 18px;
        }
        .tl-item:last-child {
            padding-bottom: 0;
        }
        .tl-dot {
            position: absolute;
            left: -22px;
            top: 3px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--card-bg);
            border: 2.5px solid var(--border);
            z-index: 1;
        }
        .tl-dot.green {
            border-color: var(--green);
            background: var(--green);
        }
        .tl-dot.blue {
            border-color: var(--blue);
            background: var(--blue);
        }
        .tl-dot.red {
            border-color: #ef4444;
            background: #ef4444;
        }
        .tl-dot.yellow {
            border-color: #f59e0b;
            background: #f59e0b;
        }
        .tl-title {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 2px;
        }
        .tl-desc {
            font-size: 12px;
            color: var(--muted);
            line-height: 1.55;
        }
        .tl-time {
            font-family: var(--font-mono);
            font-size: 10px;
            color: var(--muted);
            margin-top: 3px;
        }

        /* Animations */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .stat-card {
            animation: fadeUp .4s both;
        }
        .stat-card:nth-child(1) {
            animation-delay: .04s;
        }
        .stat-card:nth-child(2) {
            animation-delay: .09s;
        }
        .stat-card:nth-child(3) {
            animation-delay: .14s;
        }
        .stat-card:nth-child(4) {
            animation-delay: .19s;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .mini-stats {
                grid-template-columns: 1fr 1fr;
            }
        }
        @media (max-width: 860px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .search-bar {
                width: 150px;
            }
            .breadcrumb-custom {
                display: none;
            }
            .topbar-sep {
                display: none;
            }
        }
        @media (max-width: 560px) {
            .content {
                padding: 16px 14px;
            }
            .profile-name {
                display: none;
            }
            .mini-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <div class="overlay" id="overlay" onclick="closeSidebar()"></div>
    <div id="toast-container"></div>

    <!-- Sidebar -->
    @include('layout.sidebar')

    <!-- Main -->
    <div class="main">
        <!-- Topbar -->
        @include('layout.navbar')
        <!-- Content -->
        <div class="content">
            <!-- Overview Page -->
            @yield('content')
            <!-- Components Page -->
        </div>
    </div>

    <!-- Bootstrap 4 JS -->
    <script src="{{ asset('public/js/app.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Wait for DOM to be fully loaded before running any scripts
        document.addEventListener('DOMContentLoaded', function() {
            
            /* Sidebar */
            window.toggleSidebar = function() {
                const s = document.getElementById('sidebar');
                const h = document.getElementById('hamburger');
                const o = document.getElementById('overlay');
                if (s) s.classList.toggle('open');
                if (h) h.classList.toggle('active');
                if (o) o.classList.toggle('show');
            };

            window.closeSidebar = function() {
                const sidebar = document.getElementById('sidebar');
                const hamburger = document.getElementById('hamburger');
                const overlay = document.getElementById('overlay');
                if (sidebar) sidebar.classList.remove('open', 'show');
                if (hamburger) hamburger.classList.remove('active');
                if (overlay) overlay.classList.remove('show');
            };

            document.addEventListener('keydown', function(e) { 
                if (e.key === 'Escape') { 
                    closeSidebar();
                    closeProfile(); 
                } 
            });

            /* Nav dropdowns */
            window.toggleDropdown = function(trigger) {
                if (!trigger) return;
                const submenu = trigger.nextElementSibling;
                if (!submenu) return;
                const isOpen = submenu.classList.contains('open');
                
                // Close all open submenus
                document.querySelectorAll('.nav-submenu.open').forEach(function(el) {
                    el.classList.remove('open');
                    if (el.previousElementSibling) {
                        el.previousElementSibling.classList.remove('open');
                    }
                });
                
                if (!isOpen) { 
                    submenu.classList.add('open');
                    trigger.classList.add('open'); 
                }
            };

            /* Page navigation */
            window.showPage = function(id, navEl) {
                document.querySelectorAll('.page').forEach(function(p) {
                    p.classList.remove('active');
                });
                
                const page = document.getElementById('page-' + id);
                if (page) {
                    page.classList.add('active');
                }
                
                const labels = { overview: 'Overview', components: 'Components' };
                const breadcrumb = document.getElementById('breadcrumb');
                if (breadcrumb) {
                    breadcrumb.innerHTML = 'Dashboard <span>›</span> <strong>' + (labels[id] || id) + '</strong>';
                }
                
                document.querySelectorAll('.nav-item').forEach(function(n) {
                    n.classList.remove('active');
                });
                
                if (navEl) {
                    navEl.classList.add('active');
                }
                
                if (id === 'components') {
                    const sub = document.getElementById('comp-submenu');
                    const trig = document.getElementById('comp-trigger');
                    if (sub && !sub.classList.contains('open')) {
                        document.querySelectorAll('.nav-submenu.open').forEach(function(el) {
                            el.classList.remove('open');
                            if (el.previousElementSibling) {
                                el.previousElementSibling.classList.remove('open');
                            }
                        });
                        sub.classList.add('open');
                        if (trig) trig.classList.add('open');
                    }
                }
            };

            // Setup comp-trigger with null check
            const compTrigger = document.getElementById('comp-trigger');
            if (compTrigger) {
                compTrigger.addEventListener('click', function() { 
                    showPage('components', null); 
                });
            }

            // Setup nav items with null checks
            document.querySelectorAll('.nav-item').forEach(function(el) {
                if (el.textContent.trim().startsWith('Overview')) { 
                    el.classList.add('active');
                    el.onclick = function(e) { 
                        e.preventDefault();
                        showPage('overview', this); 
                    }; 
                }
            });

            window.scrollComp = function(id) { 
                setTimeout(function() { 
                    const el = document.getElementById(id); 
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' }); 
                }, 80); 
            };

            /* Dark mode */
            const html = document.documentElement;
            const dtBtn = document.getElementById('darkToggle');

            window.toggleDark = function() {
                const isDark = html.getAttribute('data-theme') === 'dark';
                html.setAttribute('data-theme', isDark ? 'light' : 'dark');
                if (dtBtn) dtBtn.classList.toggle('on', !isDark);
                localStorage.setItem('denr-theme', isDark ? 'light' : 'dark');
            };

            const saved = localStorage.getItem('denr-theme');
            if (saved === 'dark') { 
                html.setAttribute('data-theme', 'dark');
                if (dtBtn) dtBtn.classList.add('on'); 
            }

            /* Profile */
            window.toggleProfile = function(e) { 
                e.stopPropagation();
                const profileDropdown = document.getElementById('profileDropdown');
                if (profileDropdown) profileDropdown.classList.toggle('open'); 
            };

            window.closeProfile = function() { 
                const profileDropdown = document.getElementById('profileDropdown');
                if (profileDropdown) profileDropdown.classList.remove('open'); 
            };
            
            document.addEventListener('click', function() { 
                closeProfile(); 
            });

        }); // End DOMContentLoaded

        /* Toast functions (available globally) */
        const ICONS = {
            success: '<polyline points="20 6 9 17 4 12"/>',
            error: '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',
            info: '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
            warning: '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        };

        window.showToast = function(type, title, sub) {
            const c = document.getElementById('toast-container');
            if (!c) return;
            
            const t = document.createElement('div');
            t.className = 'toast-custom toast-' + type;
            t.innerHTML = `
                <div class="toast-icon-wrap"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${ICONS[type] || ICONS.info}</svg></div>
                <div class="toast-msg"><div class="toast-title">${title}</div><div class="toast-sub">${sub}</div></div>
                <button class="toast-close-btn" onclick="removeToast(this.parentElement)"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>`;
            c.appendChild(t);
            setTimeout(function() { removeToast(t); }, 4000);
        };

        window.removeToast = function(t) { 
            if (!t || !t.parentElement) return;
            t.classList.add('removing');
            setTimeout(function() { 
                if (t.parentElement) t.remove(); 
            }, 280); 
        };

        window.demoFlow = function() { 
            showToast('info', 'Submitting…', 'Sending permit application to server.');
            setTimeout(function() { showToast('success', 'Submitted!', 'Permit #FP-2216 sent for review.'); }, 1400);
            setTimeout(function() { showToast('info', 'Email Sent', 'Confirmation sent to applicant.'); }, 2600); 
        };

        /* Dropzone functions */
        window.dzOver = function(e, id) { 
            e.preventDefault();
            const el = document.getElementById(id);
            if (el) el.classList.add('drag-over'); 
        };

        window.dzLeave = function(id) { 
            const el = document.getElementById(id);
            if (el) el.classList.remove('drag-over'); 
        };

        window.dzDrop = function(e, id) { 
            e.preventDefault();
            dzLeave(id);
            addFiles(e.dataTransfer.files); 
        };

        const EXT_ICONS = {
            pdf: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>',
            xlsx: '<rect x="3" y="3" width="18" height="18" rx="2"/><polyline points="3 9 3 21 21 21 21 9"/>',
            docx: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><line x1="16" y1="13" x2="8" y2="13"/>',
            img: '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>',
        };

        function getIcon(name) { 
            const ext = name.split('.').pop().toLowerCase(); 
            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) return EXT_ICONS.img; 
            return EXT_ICONS[ext] || EXT_ICONS.pdf; 
        }

        function fmtSize(bytes) { 
            if (bytes < 1024) return bytes + ' B'; 
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'; 
            return (bytes / 1024 / 1024).toFixed(1) + ' MB'; 
        }

        window.addFiles = function(files) {
            const list = document.getElementById('fileList');
            if (!list) return;
            
            [...files].forEach(function(f) {
                const item = document.createElement('div');
                item.className = 'file-item';
                item.innerHTML = `
                    <div class="file-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${getIcon(f.name)}</svg></div>
                    <div style="flex:1"><div class="file-name">${f.name}</div><div class="file-size">${fmtSize(f.size)}</div><div class="file-progress"><div class="file-progress-fill" style="width:0%"></div></div></div>
                    <button class="file-remove" onclick="this.closest('.file-item').remove()"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>`;
                list.appendChild(item);
                const fill = item.querySelector('.file-progress-fill');
                setTimeout(function() { 
                    if (fill) fill.style.width = '100%'; 
                }, 100);
                showToast('success', 'File Added', f.name + ' queued for upload.');
            });
        };
    </script>
</body>
</html>