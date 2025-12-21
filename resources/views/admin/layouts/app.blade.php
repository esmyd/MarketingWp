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
        body:not(.dashboard-page) {
            background-color: #f8f9fa;
        }
        body {
            font-size: 0.9rem;
        }
        .sidebar {
            background-color: #343a40;
            min-height: 100vh;
            color: white;
            padding: 1rem;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.8);
            padding: 0.5rem 1rem;
            margin: 0.1rem 0;
            border-radius: 0.25rem;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }
        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255,255,255,.1);
        }
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,.2);
            color: white;
        }
        .sidebar .nav-link i {
            width: 20px;
            font-size: 0.9rem;
            text-align: center;
        }
        .main-content {
            padding: 1rem;
        }
        .alert {
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        .sidebar-header {
            padding: 0.5rem 0;
            margin-bottom: 1rem;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        .sidebar-header i {
            font-size: 1.2rem;
        }
        .sidebar-header span {
            font-size: 1.1rem;
            font-weight: 500;
        }
        .sidebar-section {
            font-size: 0.8rem;
            color: rgba(255,255,255,.5);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 1rem 0 0.5rem 0;
            padding-left: 1rem;
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
    </style>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    @yield('header')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="sidebar-header d-flex align-items-center">
                    <i class="fab fa-whatsapp me-2"></i>
                    <span>Admin Panel</span>
                </div>
                <nav class="nav flex-column">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a href="{{ route('admin.orders') }}" class="nav-link {{ request()->routeIs('admin.orders') ? 'active' : '' }}">
                        <i class="fas fa-shopping-cart"></i> Pedidos
                    </a>
                    <a href="{{ route('admin.chats') }}" class="nav-link {{ request()->routeIs('admin.chats') ? 'active' : '' }}">
                        <i class="fas fa-comments"></i> Chats
                    </a>
                    <div class="sidebar-section">
                        Gestión del Chatbot
                    </div>
                    <a href="{{ route('admin.menus.index') }}" class="nav-link {{ request()->routeIs('admin.menus.*') ? 'active' : '' }}">
                        <i class="fas fa-list"></i> Menús y Categorías
                    </a>
                    <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                        <i class="fas fa-box"></i> Productos
                    </a>
                    <a href="{{ route('admin.chatbot.config') }}" class="nav-link {{ request()->routeIs('admin.chatbot.*') ? 'active' : '' }}">
                        <i class="fas fa-cog"></i> Configuración
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4 main-content">
                @if(session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        // Configuración global de Axios para incluir el token CSRF
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    </script>

    @stack('scripts')
</body>
</html>
