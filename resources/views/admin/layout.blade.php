<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel — AURA')</title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom CSS Overrides -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 260px; background-color: var(--nav-bg); color: #FFFFFF; flex-shrink: 0; }
        .admin-sidebar .nav-link { color: #A0A0A0; font-weight: 500; padding: 0.75rem 1.25rem; border-radius: 8px; margin-bottom: 0.25rem; transition: all 0.2s ease; }
        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active { color: #FFFFFF; background-color: rgba(255, 255, 255, 0.08); }
        .admin-sidebar .nav-link.active { border-left: 4px solid var(--accent-emerald); }
        .admin-main { flex: 1; padding: 2.5rem; background-color: var(--bg-main); overflow-y: auto; }
    </style>
    @stack('styles')
</head>
<body>
    <!-- API JS helper -->
    <script src="{{ asset('js/api.js') }}"></script>
    <script>
        (function checkAdminAuth() {
            const token = getToken();
            const user = getUser();
            if (!token || !user || user.role !== 'admin') {
                window.location.href = '/admin/login';
            }
        })();
    </script>

    <div class="admin-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar p-3 d-flex flex-column">
            <a href="{{ url('/admin/dashboard') }}" class="d-flex align-items-center gap-2 text-white text-decoration-none px-2 py-3 mb-3 border-bottom border-secondary border-opacity-25">
                <span class="d-inline-flex align-items-center justify-content-center bg-emerald text-white rounded-3 p-1 fs-6" style="width: 32px; height: 32px; background-color: var(--accent-emerald);">
                    <i class="bi bi-shield-lock-fill"></i>
                </span>
                <span class="font-heading fw-bold fs-5">AURA Admin</span>
            </a>

            <nav class="nav flex-column mb-auto">
                <a class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}" href="{{ url('/admin/dashboard') }}">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a class="nav-link {{ request()->is('admin/products*') ? 'active' : '' }}" href="{{ url('/admin/products') }}">
                    <i class="bi bi-box-seam me-2"></i> Products
                </a>
                <a class="nav-link {{ request()->is('admin/orders*') ? 'active' : '' }}" href="{{ url('/admin/orders') }}">
                    <i class="bi bi-receipt me-2"></i> Orders
                </a>
            </nav>

            <div class="pt-3 border-top border-secondary border-opacity-25">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="small fw-bold text-white" id="admin-sidebar-name">Admin User</div>
                        <div class="small text-muted" style="font-size: 0.75rem;">Administrator</div>
                    </div>
                    <button type="button" onclick="logoutUser()" class="btn btn-sm btn-outline-light border-0" title="Sign Out">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="admin-main">
            @yield('content')
        </main>
    </div>

    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const user = getUser();
            if (user && document.getElementById('admin-sidebar-name')) {
                document.getElementById('admin-sidebar-name').textContent = user.name || 'Admin User';
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
