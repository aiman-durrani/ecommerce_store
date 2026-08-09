<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AURA Storefront')</title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom CSS Overrides -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    <!-- Sticky Charcoal Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
                <span class="d-inline-flex align-items-center justify-content-center bg-emerald text-white rounded-3 p-1 fs-6" style="width: 32px; height: 32px; background-color: var(--accent-emerald);">
                    <i class="bi bi-bag-heart-fill"></i>
                </span>
                <span>AURA</span>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('/') || request()->is('products*') ? 'active' : '' }}" href="{{ url('/') }}">Products</a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                    <li class="nav-item">
                        <a class="nav-link position-relative d-inline-flex align-items-center gap-1.5 px-3 py-2 rounded-3 text-white" href="{{ url('/cart') }}">
                            <i class="bi bi-cart3 fs-5"></i>
                            <span class="d-none d-lg-inline small">Cart</span>
                            <span id="nav-cart-badge" class="badge rounded-pill bg-emerald-subtle d-none ms-1" style="font-size: 0.75rem;">0</span>
                        </a>
                    </li>

                    <!-- Dynamic Guest Menu -->
                    <div id="nav-guest-menu" class="d-flex align-items-center gap-2">
                        <li class="nav-item">
                            <a class="nav-link px-3" href="{{ url('/login') }}">Sign In</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-sm btn-emerald px-3" href="{{ url('/register') }}">Register</a>
                        </li>
                    </div>

                    <!-- Dynamic Authenticated User Menu -->
                    <div id="nav-user-menu" class="d-none d-flex align-items-center gap-2">
                        <a href="{{ url('/profile') }}" class="nav-link text-white d-flex align-items-center gap-1.5 px-2 py-1 rounded-3">
                            <i class="bi bi-person-circle fs-5 text-emerald" style="color: var(--accent-emerald);"></i>
                            <span id="nav-user-name" class="fw-semibold">User</span>
                        </a>
                        <button type="button" onclick="logoutUser()" class="btn btn-sm btn-outline-light px-3 rounded-3 ms-1" style="font-size: 0.85rem;">Logout</button>
                    </div>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main>
        @yield('content')
    </main>

    <!-- Polished Minimalist Footer -->
    <footer class="py-5 mt-5 border-top border-secondary border-opacity-10">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <h5 class="font-heading fw-bold text-white mb-1">AURA E-Commerce</h5>
                    <p class="small text-muted mb-0">Crafted with precision. Powered by Laravel 13 REST API & Bootstrap 5.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="small text-muted mb-0">&copy; {{ date('Y') }} AURA Storefront. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- API JS helper -->
    <script src="{{ asset('js/api.js') }}"></script>
    @stack('scripts')
</body>
</html>
