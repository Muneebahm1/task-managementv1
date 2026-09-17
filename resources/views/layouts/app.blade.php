<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TaskFlow') — TaskFlow</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <style>
        :root {
            --sidebar-width: 240px;
            --sidebar-bg: #0A1929;
            --sidebar-hover: #132F4C;
            --sidebar-active: #1E4976;
            --sidebar-text: #B0BEC5;
            --topbar-height: 56px;
            --accent: #0052CC;
            --accent-hover: #0065FF;
            --body-bg: #F4F5F7;
            --border-color: #DFE1E6;
            --text-muted-color: #6B778C;
            --text-dark: #172B4D;
        }
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--body-bg); color: var(--text-dark); margin: 0; overflow-x: hidden; }

        /* SIDEBAR */
        #sidebar { position: fixed; top: 0; left: 0; width: var(--sidebar-width); height: 100vh; background: var(--sidebar-bg); z-index: 1040; display: flex; flex-direction: column; transition: transform .3s ease; overflow-y: auto; }
        #sidebar::-webkit-scrollbar { width: 4px; }
        #sidebar::-webkit-scrollbar-thumb { background: #2d4a66; border-radius: 4px; }
        .sidebar-brand { padding: 16px 20px; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid rgba(255,255,255,.07); text-decoration: none; }
        .sidebar-brand .brand-icon { width: 34px; height: 34px; background: var(--accent); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; color: #fff; flex-shrink: 0; }
        .sidebar-brand .brand-text { font-size: 17px; font-weight: 700; color: #fff; letter-spacing: -.3px; }
        .sidebar-brand .brand-text span { color: #4C9AFF; }
        .sidebar-section { padding: 16px 20px 4px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #546e7a; }
        .sidebar-nav { padding: 8px 0; flex: 1; }
        .sidebar-nav .nav-item { margin: 1px 8px; }
        .sidebar-nav .nav-link { display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 6px; color: var(--sidebar-text); font-size: 13.5px; font-weight: 500; text-decoration: none; transition: all .18s; }
        .sidebar-nav .nav-link i { font-size: 16px; flex-shrink: 0; }
        .sidebar-nav .nav-link:hover { background: var(--sidebar-hover); color: #fff; }
        .sidebar-nav .nav-link.active { background: var(--sidebar-active); color: #fff; }
        .sidebar-footer { padding: 12px; border-top: 1px solid rgba(255,255,255,.07); }
        .user-card { display: flex; align-items: center; gap: 10px; padding: 8px; border-radius: 8px; text-decoration: none; transition: background .18s; }
        .user-card:hover { background: var(--sidebar-hover); }
        .user-avatar { width: 34px; height: 34px; border-radius: 50%; background: var(--accent); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; color: #fff; flex-shrink: 0; overflow: hidden; }
        .user-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .user-info .user-name { font-size: 13px; font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px; }
        .user-info .user-role { font-size: 11px; color: var(--sidebar-text); }

        /* TOPBAR */
        #topbar { position: fixed; top: 0; left: var(--sidebar-width); right: 0; height: var(--topbar-height); background: #fff; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; padding: 0 24px; z-index: 1030; gap: 12px; }
        .topbar-toggle { display: none; background: none; border: none; font-size: 20px; color: var(--text-dark); cursor: pointer; padding: 4px; }
        .topbar-search { flex: 1; max-width: 380px; position: relative; }
        .topbar-search input { width: 100%; padding: 7px 14px 7px 36px; border: 1.5px solid var(--border-color); border-radius: 8px; font-size: 13.5px; background: var(--body-bg); color: var(--text-dark); outline: none; transition: border-color .2s; }
        .topbar-search input:focus { border-color: var(--accent); background: #fff; }
        .topbar-search .search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted-color); font-size: 15px; }
        .topbar-actions { margin-left: auto; display: flex; align-items: center; gap: 8px; }
        .create-btn { background: var(--accent); color: #fff; border: none; padding: 7px 16px; border-radius: 8px; font-size: 13.5px; font-weight: 600; display: flex; align-items: center; gap: 6px; text-decoration: none; transition: background .18s; white-space: nowrap; }
        .create-btn:hover { background: var(--accent-hover); color: #fff; }

        /* MAIN */
        #main-content { margin-left: var(--sidebar-width); margin-top: var(--topbar-height); min-height: calc(100vh - var(--topbar-height)); padding: 28px; }

        /* CARDS */
        .card { border: 1px solid var(--border-color); border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
        .card-header { background: #fff; border-bottom: 1px solid var(--border-color); padding: 14px 20px; border-radius: 10px 10px 0 0 !important; }

        /* STAT CARDS */
        .stat-card { background: #fff; border: 1px solid var(--border-color); border-radius: 12px; padding: 20px 22px; display: flex; align-items: center; gap: 16px; transition: box-shadow .2s, transform .2s; cursor: default; }
        .stat-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,.1); transform: translateY(-2px); }
        .stat-icon { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; }
        .stat-info .stat-value { font-size: 28px; font-weight: 700; line-height: 1.1; }
        .stat-info .stat-label { font-size: 13px; color: var(--text-muted-color); margin-top: 2px; }

        /* PRIORITY / TYPE BADGES */
        .priority-badge { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 4px; white-space: nowrap; }
        .type-badge    { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 4px; white-space: nowrap; }
        .task-key { font-size: 11.5px; font-weight: 700; color: var(--accent); font-family: monospace; }
        .status-pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #fff; }

        /* BOARD */
        .board-wrapper { display: flex; gap: 16px; overflow-x: auto; padding-bottom: 16px; min-height: calc(100vh - 200px); align-items: flex-start; }
        .board-wrapper::-webkit-scrollbar { height: 6px; }
        .board-wrapper::-webkit-scrollbar-thumb { background: #c0c8d4; border-radius: 4px; }
        .board-col { min-width: 280px; max-width: 280px; background: #EBECF0; border-radius: 10px; display: flex; flex-direction: column; max-height: calc(100vh - 180px); }
        .board-col-header { padding: 12px 14px; display: flex; align-items: center; gap: 8px; }
        .board-col-header .col-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
        .board-col-header .col-name { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #5e6c84; flex: 1; }
        .board-col-header .col-count { font-size: 11px; background: rgba(9,30,66,.13); color: #5e6c84; font-weight: 700; border-radius: 12px; padding: 1px 8px; }
        .board-cards { padding: 0 8px 8px; flex: 1; overflow-y: auto; min-height: 60px; }
        .board-cards::-webkit-scrollbar { width: 4px; }
        .board-cards::-webkit-scrollbar-thumb { background: #c0c8d4; border-radius: 4px; }
        .board-card { background: #fff; border: none; border-radius: 8px; padding: 12px 14px; margin-bottom: 8px; cursor: grab; transition: box-shadow .18s, transform .18s; box-shadow: 0 1px 2px rgba(9,30,66,.16); }
        .board-card:hover { box-shadow: 0 6px 16px rgba(9,30,66,.18); transform: translateY(-1px); }
        .board-card.sortable-ghost { opacity: .4; background: #deebff; }
        .board-card .card-task-key { font-size: 10.5px; font-weight: 700; color: var(--accent); font-family: monospace; }
        .board-card .card-title { font-size: 13px; font-weight: 500; color: var(--text-dark); margin: 5px 0; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .board-card .card-meta { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; }
        .mini-avatar { width: 24px; height: 24px; border-radius: 50%; background: var(--accent); display: inline-flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; color: #fff; overflow: hidden; flex-shrink: 0; }
        .mini-avatar img { width: 100%; height: 100%; object-fit: cover; }

        /* ALERTS */
        .alert { border-radius: 8px; border: none; font-size: 14px; }
        .alert-success { background: #E3FCEF; color: #006644; }
        .alert-danger  { background: #FFEBE6; color: #BF2600; }
        .alert-warning { background: #FFFAE6; color: #974F0C; }
        .alert-info    { background: #DEEBFF; color: #0747A6; }

        /* TASK DETAIL */
        .detail-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: var(--text-muted-color); margin-bottom: 6px; }
        .detail-value { font-size: 14px; color: var(--text-dark); }

        /* COMMENTS */
        .comment-item { display: flex; gap: 12px; padding: 14px 0; border-bottom: 1px solid var(--border-color); }
        .comment-item:last-child { border-bottom: none; }
        .comment-body { flex: 1; }
        .comment-meta { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
        .comment-author { font-size: 13px; font-weight: 700; color: var(--text-dark); }
        .comment-time   { font-size: 12px; color: var(--text-muted-color); }
        .comment-content { font-size: 14px; color: var(--text-dark); line-height: 1.6; white-space: pre-wrap; word-break: break-word; }
        .comment-actions a { font-size: 12px; color: var(--text-muted-color); text-decoration: none; cursor: pointer; margin-right: 8px; }
        .comment-actions a:hover { color: var(--accent); }

        /* FORMS */
        .form-label { font-size: 13px; font-weight: 600; color: var(--text-dark); }
        .form-control, .form-select { border: 1.5px solid var(--border-color); border-radius: 7px; font-size: 14px; padding: 9px 12px; color: var(--text-dark); }
        .form-control:focus, .form-select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(0,82,204,.12); }

        /* TABLE */
        .table { font-size: 13.5px; }
        .table thead th { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: var(--text-muted-color); border-bottom: 2px solid var(--border-color); padding: 10px 14px; }
        .table td { padding: 12px 14px; vertical-align: middle; border-color: var(--border-color); }
        .table tbody tr:hover { background: #f8f9fc; }

        /* PAGE HEADER */
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
        .page-title { font-size: 22px; font-weight: 700; color: var(--text-dark); margin: 0; }
        .page-subtitle { font-size: 13px; color: var(--text-muted-color); margin-top: 2px; }

        /* MISC */
        .btn-primary { background: var(--accent); border-color: var(--accent); }
        .btn-primary:hover { background: var(--accent-hover); border-color: var(--accent-hover); }
        .text-primary { color: var(--accent) !important; }
        .overdue-badge { background: #FFEBE6; color: #BF2600; border-radius: 4px; padding: 2px 7px; font-size: 11px; font-weight: 700; }
        .attachment-thumb { width: 80px; height: 80px; border: 1px solid var(--border-color); border-radius: 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; overflow: hidden; font-size: 11px; color: var(--text-muted-color); text-decoration: none; transition: border-color .2s; }
        .attachment-thumb:hover { border-color: var(--accent); }
        .attachment-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .attachment-thumb i { font-size: 26px; }
        .spinner-overlay { position: fixed; inset: 0; background: rgba(255,255,255,.7); z-index: 9999; display: none; align-items: center; justify-content: center; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
        .fade-in { animation: fadeIn .25s ease; }

        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            #topbar { left: 0; }
            #main-content { margin-left: 0; padding: 16px; }
            .topbar-toggle { display: flex; }
            .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1039; }
            .sidebar-overlay.show { display: block; }
        }
    </style>
    @stack('styles')
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- SIDEBAR -->
<nav id="sidebar">
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        <div class="brand-icon"><i class="bi bi-kanban-fill"></i></div>
        <span class="brand-text">Task<span>Flow</span></span>
    </a>

    <div class="sidebar-nav">
        @auth
        @php $authUser = auth()->user(); @endphp

        {{-- CLIENT sidebar --}}
        @if($authUser->isClient())
        <div class="sidebar-section">Portal</div>
        <ul class="nav flex-column list-unstyled mb-0">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> My Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('tasks.index') }}" class="nav-link {{ request()->routeIs('tasks.index') ? 'active' : '' }}">
                    <i class="bi bi-card-list"></i> My Requests
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('tasks.create') }}" class="nav-link {{ request()->routeIs('tasks.create') ? 'active' : '' }}"
                   style="background:rgba(0,82,204,.18);color:#4C9AFF;margin-top:4px;">
                    <i class="bi bi-plus-circle-fill"></i> Submit Request
                </a>
            </li>
        </ul>

        {{-- EMPLOYEE sidebar --}}
        @elseif($authUser->isEmployee())
        <div class="sidebar-section">Workspace</div>
        <ul class="nav flex-column list-unstyled mb-0">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('tasks.board') }}" class="nav-link {{ request()->routeIs('tasks.board') ? 'active' : '' }}">
                    <i class="bi bi-kanban"></i> Board
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('tasks.index') }}" class="nav-link {{ request()->routeIs('tasks.index') ? 'active' : '' }}">
                    <i class="bi bi-list-task"></i> My Tasks
                </a>
            </li>
        </ul>

        {{-- ADMIN sidebar --}}
        @else
        <div class="sidebar-section">Main</div>
        <ul class="nav flex-column list-unstyled mb-0">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('tasks.board') }}" class="nav-link {{ request()->routeIs('tasks.board') ? 'active' : '' }}">
                    <i class="bi bi-kanban"></i> Board
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('tasks.index') }}" class="nav-link {{ request()->routeIs('tasks.index') ? 'active' : '' }}">
                    <i class="bi bi-list-task"></i> All Tasks
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('tasks.create') }}" class="nav-link {{ request()->routeIs('tasks.create') ? 'active' : '' }}">
                    <i class="bi bi-plus-circle"></i> Create Task
                </a>
            </li>
        </ul>

        <div class="sidebar-section" style="margin-top:8px">Administration</div>
        <ul class="nav flex-column list-unstyled mb-0">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-shield-check"></i> Admin Panel
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Users
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.statuses.index') }}" class="nav-link {{ request()->routeIs('admin.statuses.*') ? 'active' : '' }}">
                    <i class="bi bi-tags"></i> Statuses
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.contacts.index') }}" class="nav-link {{ request()->routeIs('admin.contacts.*') ? 'active' : '' }}">
                    <i class="bi bi-person-lines-fill"></i> Contacts
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.targets.index') }}" class="nav-link {{ request()->routeIs('admin.targets.*') ? 'active' : '' }}">
                    <i class="bi bi-bullseye"></i> Targets
                </a>
            </li>
        </ul>
        @endif
        @endauth
    </div>

    <div class="sidebar-footer">
        @auth
        <div class="dropdown dropup">
            <a href="#" class="user-card d-flex text-decoration-none" data-bs-toggle="dropdown">
                <div class="user-avatar">
                    @if(auth()->user()->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="">
                    @else
                        {{ auth()->user()->initials }}
                    @endif
                </div>
                <div class="user-info ms-1" style="min-width:0">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark mb-2" style="min-width:200px;">
                <li><span class="dropdown-item-text text-muted small">{{ auth()->user()->email }}</span></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-left me-2"></i>Sign Out
                        </button>
                    </form>
                </li>
            </ul>
        </div>
        @endauth
    </div>
</nav>

<!-- TOPBAR -->
<header id="topbar">
    <button class="topbar-toggle" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>
    <div class="topbar-search">
        <i class="bi bi-search search-icon"></i>
        <input type="text" id="globalSearch" placeholder="Search tasks…" autocomplete="off">
    </div>
    <div class="topbar-actions">
        @auth
        <!-- Notification Bell -->
        <div class="dropdown" id="notifDropdown">
            <button class="topbar-btn position-relative" id="notifBell" data-bs-toggle="dropdown"
                    aria-expanded="false" title="Deadline Notifications" style="width:38px;height:38px;border-radius:8px;border:none;background:none;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#6B778C;font-size:20px;">
                <i class="bi bi-bell"></i>
                <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                      style="font-size:9px;padding:3px 5px;display:none;">0</span>
            </button>
            <div class="dropdown-menu dropdown-menu-end p-0" id="notifPanel"
                 style="width:360px;max-height:480px;overflow-y:auto;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.16);border:1px solid #DFE1E6;">
                <div class="d-flex align-items-center justify-content-between px-3 py-2"
                     style="border-bottom:1px solid #DFE1E6;background:#F4F5F7;border-radius:10px 10px 0 0;">
                    <span style="font-size:13px;font-weight:700;color:#172B4D;">
                        <i class="bi bi-bell me-2 text-primary"></i>Deadline Alerts
                    </span>
                    <button onclick="dismissAll()" class="btn btn-link btn-sm p-0 text-muted"
                            style="font-size:11px;text-decoration:none;">Dismiss all</button>
                </div>
                <div id="notifList">
                    <div class="text-center py-4 text-muted" style="font-size:13px;" id="notifEmpty">
                        <i class="bi bi-check-circle" style="font-size:28px;color:#36B37E;display:block;margin-bottom:8px;"></i>
                        No upcoming deadlines!
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->user()->isAdmin())
        <a href="{{ route('tasks.create') }}" class="create-btn">
            <i class="bi bi-plus-lg"></i> Create
        </a>
        @elseif(auth()->user()->isClient())
        <a href="{{ route('tasks.create') }}" class="create-btn" style="background:#6554C0;">
            <i class="bi bi-send"></i> Submit Request
        </a>
        @endif
        @endauth
    </div>
</header>

<!-- MAIN CONTENT -->
<main id="main-content">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4">
            <i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</main>

<div class="spinner-overlay" id="spinnerOverlay">
    <div class="spinner-border text-primary" style="width:3rem;height:3rem;" role="status"></div>
</div>

<!-- Toast container for push notifications -->
<div class="position-fixed" style="top:68px;right:20px;z-index:9050;display:flex;flex-direction:column;gap:8px;max-width:360px;" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    $('#sidebarToggle').on('click', function () { $('#sidebar').toggleClass('open'); $('#sidebarOverlay').toggleClass('show'); });
    $('#sidebarOverlay').on('click', function () { $('#sidebar').removeClass('open'); $(this).removeClass('show'); });
    $('#globalSearch').on('keypress', function (e) {
        if (e.which === 13 && $(this).val().trim()) window.location = '/tasks?search=' + encodeURIComponent($(this).val().trim());
    });
    setTimeout(function () { $('.alert').fadeTo(500, 0, function() { $(this).alert('close'); }); }, 4000);
    function showSpinner() { $('#spinnerOverlay').css('display','flex'); }
    function hideSpinner() { $('#spinnerOverlay').hide(); }

    /* =====================================================
       DEADLINE NOTIFICATION SYSTEM
       Polls /notifications/deadlines every 5 minutes.
       Uses localStorage to suppress toasts already seen
       in the current browser session (resets each day).
    ===================================================== */
    @auth
    const NOTIF_KEY     = 'tf_dismissed_' + '{{ date("Y-m-d") }}';
    const POLL_INTERVAL = 5 * 60 * 1000; // 5 minutes
    let   activeNotifs  = {};

    function getDismissed() {
        try { return JSON.parse(localStorage.getItem(NOTIF_KEY) || '[]'); }
        catch(e) { return []; }
    }

    function dismiss(id) {
        const d = getDismissed();
        if (!d.includes(id)) { d.push(id); localStorage.setItem(NOTIF_KEY, JSON.stringify(d)); }
        delete activeNotifs[id];
        $('#notif-item-' + id).fadeOut(300, function() { $(this).remove(); refreshPanel(); });
    }

    function dismissAll() {
        const d = getDismissed();
        Object.keys(activeNotifs).forEach(function(id) {
            if (!d.includes(id)) d.push(id);
        });
        localStorage.setItem(NOTIF_KEY, JSON.stringify(d));
        activeNotifs = {};
        $('#notifList').html('<div class="text-center py-4 text-muted" style="font-size:13px;" id="notifEmpty">' +
            '<i class="bi bi-check-circle" style="font-size:28px;color:#36B37E;display:block;margin-bottom:8px;"></i>No upcoming deadlines!</div>');
        updateBadge(0);
    }

    function refreshPanel() {
        const count = Object.keys(activeNotifs).length;
        updateBadge(count);
        if (count === 0) {
            $('#notifList').html('<div class="text-center py-4 text-muted" style="font-size:13px;" id="notifEmpty">' +
                '<i class="bi bi-check-circle" style="font-size:28px;color:#36B37E;display:block;margin-bottom:8px;"></i>No upcoming deadlines!</div>');
        }
    }

    function updateBadge(count) {
        const badge = $('#notifBadge');
        const bell  = $('#notifBell i');
        if (count > 0) {
            badge.text(count > 9 ? '9+' : count).show();
            bell.css('color', '#FF5630');
        } else {
            badge.hide();
            bell.css('color', '#6B778C');
        }
    }

    function showToast(n) {
        const colors = { overdue: '#FF5630', today: '#FF8C00', soon: '#FFC400' };
        const bg     = colors[n.level] || '#0052CC';

        const toastHtml = `
            <div class="toast align-items-center show fade-in" id="toast-${n.id}" role="alert"
                 style="background:#fff;border:1px solid #DFE1E6;border-left:4px solid ${bg};border-radius:10px;
                        box-shadow:0 8px 24px rgba(0,0,0,.14);min-width:320px;max-width:360px;pointer-events:all;">
                <div class="d-flex align-items-start p-3 gap-3">
                    <div style="width:36px;height:36px;border-radius:8px;background:${bg}22;display:flex;
                                align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi ${n.icon}" style="color:${bg};font-size:18px;"></i>
                    </div>
                    <div class="flex-1" style="min-width:0;">
                        <div style="font-size:12px;font-weight:700;color:${bg};text-transform:uppercase;letter-spacing:.5px;">
                            ${n.label}
                        </div>
                        <a href="${n.url}" style="font-size:13px;font-weight:600;color:#172B4D;text-decoration:none;
                                                  display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px;">
                            <span style="color:#0052CC;font-family:monospace;">${n.task_key}</span> — ${n.title}
                        </a>
                        <div style="font-size:11px;color:#6B778C;margin-top:3px;">
                            <i class="bi bi-calendar3 me-1"></i>Due: ${n.due_date}
                            ${n.assignee ? ' · <i class="bi bi-person me-1"></i>' + n.assignee : ''}
                        </div>
                    </div>
                    <button onclick="dismissToast('${n.id}')" class="btn-close" style="flex-shrink:0;font-size:11px;margin-top:2px;"></button>
                </div>
            </div>`;

        $('#toastContainer').append(toastHtml);

        // Auto-remove toast after 8 seconds
        setTimeout(function() { dismissToast(n.id); }, 8000);
    }

    function dismissToast(id) {
        $('#toast-' + id).fadeTo(400, 0, function() { $(this).remove(); });
    }

    function buildPanelItem(n) {
        const colors = { overdue: '#FF5630', today: '#FF8C00', soon: '#FFC400' };
        const bg = colors[n.level] || '#0052CC';
        return `<div class="d-flex align-items-start gap-3 px-3 py-2" id="notif-item-${n.id}"
                     style="border-bottom:1px solid #F4F5F7;transition:background .15s;" onmouseenter="this.style.background='#F4F5F7'" onmouseleave="this.style.background=''">
                    <div style="width:32px;height:32px;border-radius:7px;background:${bg}22;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;">
                        <i class="bi ${n.icon}" style="color:${bg};font-size:15px;"></i>
                    </div>
                    <div class="flex-1" style="min-width:0;">
                        <div style="font-size:11px;font-weight:700;color:${bg};">${n.label}</div>
                        <a href="${n.url}" style="font-size:13px;font-weight:600;color:#172B4D;text-decoration:none;
                                                  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;">
                            <span style="color:#0052CC;font-family:monospace;font-size:11px;">${n.task_key}</span> ${n.title}
                        </a>
                        <div style="font-size:11px;color:#9BA8B4;margin-top:2px;">
                            <i class="bi bi-calendar3 me-1"></i>${n.due_date}
                            ${n.assignee ? ' · ' + n.assignee : ''}
                        </div>
                    </div>
                    <button onclick="dismiss('${n.id}')" class="btn-close" style="font-size:10px;flex-shrink:0;margin-top:4px;"></button>
                </div>`;
    }

    function fetchDeadlines(showToasts) {
        $.getJSON('/notifications/deadlines', function(res) {
            const dismissed = getDismissed();
            const fresh     = res.notifications.filter(function(n) { return !dismissed.includes(n.id); });

            // Rebuild panel
            activeNotifs = {};
            fresh.forEach(function(n) { activeNotifs[n.id] = n; });
            updateBadge(fresh.length);

            if (fresh.length === 0) {
                $('#notifList').html('<div class="text-center py-4 text-muted" style="font-size:13px;" id="notifEmpty">' +
                    '<i class="bi bi-check-circle" style="font-size:28px;color:#36B37E;display:block;margin-bottom:8px;"></i>No upcoming deadlines!</div>');
                return;
            }

            let html = '';
            fresh.forEach(function(n) { html += buildPanelItem(n); });
            $('#notifList').html(html);

            // Show toasts only on first load or when new ones appear
            if (showToasts) {
                // Show max 3 toasts to avoid flooding; prioritise overdue first
                const toShow = fresh
                    .sort(function(a,b) { return (a.days - b.days); })
                    .slice(0, 3);
                toShow.forEach(function(n, i) {
                    setTimeout(function() { showToast(n); }, i * 600);
                });
            }
        });
    }

    // Initial fetch (with toasts) after 2s page load delay
    setTimeout(function() { fetchDeadlines(true); }, 2000);

    // Poll every 5 minutes silently (no toasts on refresh)
    setInterval(function() { fetchDeadlines(false); }, POLL_INTERVAL);

    @endauth

    @if(auth()->check() && auth()->user()->isAdmin())
    /* =====================================================
       TARGET DEADLINE NOTIFICATION SYSTEM
       Polls /admin/targets/check-deadlines every 5 min.
       Shows a toast when any target has ≤10 days left.
    ===================================================== */
    const TARGET_DISMISS_KEY = 'tf_target_{{ date("Y-m-d") }}';

    function getTargetDismissed() {
        try { return JSON.parse(localStorage.getItem(TARGET_DISMISS_KEY) || '[]'); }
        catch(e) { return []; }
    }

    function dismissTargetNotif(toastId, targetKey) {
        const d = getTargetDismissed();
        if (!d.includes(targetKey)) { d.push(targetKey); localStorage.setItem(TARGET_DISMISS_KEY, JSON.stringify(d)); }
        $('#toast-' + toastId).fadeTo(400, 0, function() { $(this).remove(); });
    }

    function showTargetToast(t) {
        const bg      = t.days_remaining <= 3 ? '#FF5630' : '#FF8C00';
        const toastId = 'target-' + t.id;
        const label   = t.days_remaining === 0 ? 'TARGET EXPIRES TODAY'
                      : t.days_remaining === 1  ? '1 DAY LEFT ON TARGET'
                      : t.days_remaining + ' DAYS LEFT ON TARGET';

        const html = `
        <div class="toast align-items-center show fade-in" id="toast-${toastId}" role="alert"
             style="background:#fff;border:1px solid #DFE1E6;border-left:4px solid ${bg};border-radius:10px;
                    box-shadow:0 8px 24px rgba(0,0,0,.14);min-width:320px;max-width:360px;pointer-events:all;">
            <div class="d-flex align-items-start p-3 gap-3">
                <div style="width:36px;height:36px;border-radius:8px;background:${bg}22;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-bullseye" style="color:${bg};font-size:18px;"></i>
                </div>
                <div class="flex-1" style="min-width:0;">
                    <div style="font-size:11px;font-weight:700;color:${bg};text-transform:uppercase;letter-spacing:.5px;">${label}</div>
                    <a href="/admin/targets/${t.id}" style="font-size:13px;font-weight:600;color:#172B4D;text-decoration:none;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px;">
                        ${t.title}
                    </a>
                    <div style="font-size:11px;color:#6B778C;margin-top:3px;">
                        <i class="bi bi-calendar3 me-1"></i>Ends ${t.end_date}
                        &nbsp;·&nbsp;<i class="bi bi-person-dash me-1"></i>${t.remaining_target} remaining / ${t.total_target}
                    </div>
                </div>
                <button onclick="dismissTargetNotif('${toastId}', 'target-${t.id}')"
                        class="btn-close" style="flex-shrink:0;font-size:11px;margin-top:2px;"></button>
            </div>
        </div>`;

        $('#toastContainer').append(html);
        setTimeout(function() { dismissTargetNotif(toastId, 'target-' + t.id); }, 12000);
    }

    function fetchTargetDeadlines(showToasts) {
        $.getJSON('/admin/targets/check-deadlines', function(res) {
            if (!showToasts || !res.targets || !res.targets.length) return;
            const dismissed = getTargetDismissed();
            const fresh     = res.targets.filter(function(t) { return !dismissed.includes('target-' + t.id); });
            fresh.slice(0, 3).forEach(function(t, i) {
                setTimeout(function() { showTargetToast(t); }, i * 900 + 4000);
            });
        });
    }

    setTimeout(function() { fetchTargetDeadlines(true); }, 5000);
    setInterval(function() { fetchTargetDeadlines(true); }, 5 * 60 * 1000);
    @endif
</script>
@stack('scripts')
</body>
</html>
