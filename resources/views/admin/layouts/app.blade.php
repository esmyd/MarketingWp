<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo - WhatsApp Marketing</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom CSS -->
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
            --sidebar-bg: #343a40;
            --sidebar-hover: rgba(255,255,255,.1);
            --sidebar-active: rgba(255,255,255,.2);
        }

        * {
            box-sizing: border-box;
        }

        body:not(.dashboard-page) {
            background-color: #f8f9fa;
        }

        body {
            font-size: 0.9rem;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            background-color: var(--sidebar-bg);
            min-height: 100vh;
            color: white;
            padding: 0;
            padding-bottom: 120px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            width: var(--sidebar-width);
            transition: all 0.3s ease;
            overflow-y: auto;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,.2);
            border-radius: 3px;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar.collapsed .sidebar-text,
        .sidebar.collapsed .sidebar-section {
            opacity: 0;
            visibility: hidden;
            width: 0;
            overflow: hidden;
        }

        .sidebar-header {
            padding: 1rem;
            margin-bottom: 0;
            border-bottom: 1px solid rgba(255,255,255,.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 60px;
        }

        .sidebar-header-content {
            display: flex;
            align-items: center;
            flex: 1;
            min-width: 0;
        }

        .sidebar-header i {
            font-size: 1.5rem;
            color: #25d366;
            margin-right: 0.75rem;
            flex-shrink: 0;
        }

        .sidebar-header span {
            font-size: 1.1rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-toggle-btn {
            background: transparent;
            border: none;
            color: rgba(255,255,255,.8);
            font-size: 1.1rem;
            cursor: pointer;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .sidebar-toggle-btn:hover {
            background: var(--sidebar-hover);
            color: white;
        }

        .sidebar-nav {
            padding: 0.5rem 0;
            flex: 1;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,.8);
            padding: 0.75rem 1rem;
            margin: 0.1rem 0.5rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            text-decoration: none;
            white-space: nowrap;
        }

        .sidebar .nav-link:hover {
            color: white;
            background-color: var(--sidebar-hover);
        }

        .sidebar .nav-link.active {
            background-color: var(--sidebar-active);
            color: white;
        }

        .sidebar .nav-link i {
            width: 24px;
            font-size: 1rem;
            text-align: center;
            flex-shrink: 0;
            margin-right: 0.75rem;
        }

        .sidebar-text {
            transition: opacity 0.2s ease;
            overflow: hidden;
        }

        .sidebar-section {
            font-size: 0.75rem;
            color: rgba(255,255,255,.5);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 1rem 0 0.5rem 0;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            margin-top: auto;
            padding: 1rem;
            border-top: 1px solid rgba(255,255,255,.1);
            background-color: rgba(0,0,0,.1);
        }

        .sidebar-user-info {
            display: flex;
            align-items: center;
            padding: 0.5rem 0.75rem;
            margin-bottom: 0.5rem;
            color: rgba(255,255,255,.8);
            font-size: 0.85rem;
        }

        .sidebar-user-info i {
            font-size: 1.2rem;
            color: #25d366;
            margin-right: 0.5rem;
            flex-shrink: 0;
        }

        .sidebar-logout-form {
            margin: 0;
            padding: 0;
        }

        .sidebar-logout-btn {
            width: 100%;
            color: rgba(255,255,255,.8) !important;
            background-color: transparent !important;
            border: none;
            padding: 0.75rem 1rem;
            margin: 0;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .sidebar-logout-btn:hover {
            background-color: rgba(220, 53, 69, 0.2) !important;
            color: #ff6b6b !important;
        }

        .sidebar-logout-btn i {
            color: inherit;
        }

        .sidebar.collapsed .sidebar-footer {
            padding: 0.5rem;
        }

        .sidebar.collapsed .sidebar-user-info span,
        .sidebar.collapsed .sidebar-logout-btn .sidebar-text {
            opacity: 0;
            visibility: hidden;
            width: 0;
            overflow: hidden;
        }

        /* Main Content */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
            min-height: 100vh;
        }

        .main-wrapper.sidebar-collapsed {
            margin-left: var(--sidebar-collapsed-width);
        }

        .main-content {
            padding: 1rem;
        }

        .mobile-menu-btn {
            display: none;
            background: white;
            border: 1px solid #dee2e6;
            font-size: 1.25rem;
            color: #343a40;
            cursor: pointer;
            padding: 0.5rem 0.75rem;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.2s ease;
        }

        .mobile-menu-btn:hover {
            background: #f8f9fa;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }

        .alert {
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .content-header {
            background: white;
            padding: 1rem;
            margin: -1rem -1rem 1rem -1rem;
            border-bottom: 1px solid #dee2e6;
        }

        .content-header h1 {
            font-size: 1.5rem;
            margin: 0;
            color: #343a40;
        }

        /* Top Navbar */
        .top-navbar {
            background: white;
            border-bottom: 1px solid #dee2e6;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .top-navbar .navbar-brand {
            font-size: 1.1rem;
            font-weight: 600;
            color: #343a40;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .top-navbar .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .top-navbar .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .top-navbar .user-info i {
            color: #25d366;
        }

        .top-navbar .logout-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 0.375rem;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .top-navbar .logout-btn:hover {
            background: #c82333;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
        }

        .top-navbar .logout-btn i {
            font-size: 0.85rem;
        }

        /* Mobile Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-overlay.show {
            display: block;
            opacity: 1;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width) !important;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .sidebar.collapsed {
                transform: translateX(-100%);
            }

            .main-wrapper {
                margin-left: 0 !important;
            }

            .mobile-menu-btn {
                display: block;
                position: fixed;
                top: 10px;
                left: 10px;
                z-index: 1001;
            }

            .top-navbar {
                padding: 0.5rem 1rem;
                flex-wrap: wrap;
            }

            .top-navbar .user-info span {
                display: none;
            }

            .top-navbar .logout-btn span {
                display: none;
            }

            .top-navbar .logout-btn {
                padding: 0.5rem;
            }

            .main-content {
                padding: 0.75rem;
            }

            .content-header {
                margin: -0.75rem -0.75rem 1rem -0.75rem;
                padding: 0.75rem;
            }

            .content-header h1 {
                font-size: 1.25rem;
            }
        }

        @media (max-width: 767.98px) {
            .main-content {
                padding: 0.5rem;
            }

            .content-header {
                margin: -0.5rem -0.5rem 1rem -0.5rem;
                padding: 0.5rem;
            }

            .content-header h1 {
                font-size: 1.1rem;
            }
        }

        /* Table responsive */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Cards responsive */
        @media (max-width: 767.98px) {
            .card {
                margin-bottom: 1rem;
            }
        }
    </style>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    @yield('header')
</head>
<body>
    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-header-content">
                <i class="fab fa-whatsapp"></i>
                <span class="sidebar-text">Admin Panel</span>
            </div>
            <button class="sidebar-toggle-btn d-none d-lg-block" id="sidebarToggle" title="Minimizar/Maximizar">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>
        <nav class="sidebar-nav">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span class="sidebar-text">Dashboard</span>
            </a>
            <a href="{{ route('admin.orders') }}" class="nav-link {{ request()->routeIs('admin.orders') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart"></i>
                <span class="sidebar-text">Pedidos</span>
            </a>
            <a href="{{ route('admin.chats') }}" class="nav-link {{ request()->routeIs('admin.chats') ? 'active' : '' }}">
                <i class="fas fa-comments"></i>
                <span class="sidebar-text">Chats</span>
            </a>
            <div class="sidebar-section">
                <span class="sidebar-text">Gestión del Chatbot</span>
            </div>
            <a href="{{ route('admin.menus.index') }}" class="nav-link {{ request()->routeIs('admin.menus.*') ? 'active' : '' }}">
                <i class="fas fa-list"></i>
                <span class="sidebar-text">Menús y Categorías</span>
            </a>
            <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                <i class="fas fa-box"></i>
                <span class="sidebar-text">Productos</span>
            </a>
            <a href="{{ route('admin.chatbot.config') }}" class="nav-link {{ request()->routeIs('admin.chatbot.*') ? 'active' : '' }}">
                <i class="fas fa-cog"></i>
                <span class="sidebar-text">Configuración</span>
            </a>
            <div class="sidebar-section">
                <span class="sidebar-text">Marketing</span>
            </div>
            <a href="{{ route('admin.marketing.index') }}" class="nav-link {{ request()->routeIs('admin.marketing.*') ? 'active' : '' }}">
                <i class="fas fa-bullhorn"></i>
                <span class="sidebar-text">Campañas</span>
            </a>
        </nav>

        <!-- Sidebar Footer with Logout -->
        <div class="sidebar-footer">
            <div class="sidebar-user-info">
                <i class="fas fa-user-circle"></i>
                <span class="sidebar-text">{{ Auth::user()->name ?? 'Administrador' }}</span>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="sidebar-logout-form">
                @csrf
                <button type="submit" class="nav-link sidebar-logout-btn" onclick="return confirm('¿Estás seguro de que deseas cerrar sesión?');">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="sidebar-text">Cerrar Sesión</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-wrapper" id="mainWrapper">
        <!-- Mobile Menu Button (Fixed) -->
        <button class="mobile-menu-btn" id="mobileMenuBtn" style="position: fixed; top: 10px; left: 10px; z-index: 1001; background: white; border: 1px solid #dee2e6; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <i class="fas fa-bars"></i>
        </button>

        <div class="main-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        // Configuración global de Axios para incluir el token CSRF
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Sidebar Toggle Functionality
        (function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const mainWrapper = document.getElementById('mainWrapper');

            // Check localStorage for sidebar state
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (sidebarCollapsed && window.innerWidth >= 992) {
                sidebar.classList.add('collapsed');
                mainWrapper.classList.add('sidebar-collapsed');
                if (sidebarToggle) {
                    sidebarToggle.querySelector('i').classList.remove('fa-chevron-left');
                    sidebarToggle.querySelector('i').classList.add('fa-chevron-right');
                }
            }

            // Desktop toggle
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    mainWrapper.classList.toggle('sidebar-collapsed');

                    const isCollapsed = sidebar.classList.contains('collapsed');
                    localStorage.setItem('sidebarCollapsed', isCollapsed);

                    const icon = this.querySelector('i');
                    if (isCollapsed) {
                        icon.classList.remove('fa-chevron-left');
                        icon.classList.add('fa-chevron-right');
                    } else {
                        icon.classList.remove('fa-chevron-right');
                        icon.classList.add('fa-chevron-left');
                    }
                });
            }

            // Mobile menu toggle
            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    sidebarOverlay.classList.toggle('show');
                    document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
                });
            }

            // Close sidebar when clicking overlay
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                    document.body.style.overflow = '';
                });
            }

            // Close sidebar when clicking a link on mobile
            const navLinks = sidebar.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 992) {
                        sidebar.classList.remove('show');
                        sidebarOverlay.classList.remove('show');
                        document.body.style.overflow = '';
                    }
                });
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 992) {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                    document.body.style.overflow = '';
                }
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>
