@extends('layouts.app')

@section('title', 'Sign In — AURA Storefront')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden mt-3" style="background: #FFFFFF;">
                <div class="card-body p-4 p-md-5">
                    <!-- Brand Header -->
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-emerald text-white rounded-3 p-2 mb-3 shadow-sm" style="width: 48px; height: 48px; background-color: var(--accent-emerald);">
                            <i class="bi bi-person-circle fs-3"></i>
                        </div>
                        <h2 class="font-heading fw-bold text-dark mb-1">Welcome Back</h2>
                        <p class="text-muted small">Enter your credentials to access your account</p>
                    </div>

                    <!-- Error Alert -->
                    <div id="auth-error-alert" class="alert alert-danger d-none rounded-3 small py-2.5" role="alert"></div>

                    <!-- Form -->
                    <form id="login-form">
                        <div class="mb-3">
                            <label for="email" class="form-label small text-muted fw-semibold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" id="email" class="form-control border-start-0 ps-0" placeholder="name@example.com" required autocomplete="email">
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="password" class="form-label small text-muted fw-semibold mb-1">Password</label>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                <input type="password" id="password" class="form-control border-start-0 ps-0" placeholder="••••••••" required autocomplete="current-password">
                            </div>
                        </div>

                        <button type="submit" id="btn-login-submit" class="btn btn-emerald btn-lg w-100 py-2.5 fs-6 shadow-sm mb-3">
                            <span>Sign In</span>
                        </button>
                    </form>

                    <div class="text-center pt-3 border-top">
                        <p class="small text-muted mb-0">Don't have an account? <a href="{{ url('/register') }}" class="fw-semibold text-decoration-none" style="color: var(--accent-emerald);">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const alertBox = document.getElementById('auth-error-alert');
    alertBox.classList.add('d-none');

    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;

    const btn = document.getElementById('btn-login-submit');
    const origText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status"></span> Signing in...`;

    try {
        const res = await apiFetch('/login', {
            method: 'POST',
            body: JSON.stringify({ email, password })
        });

        if (res.token) {
            setToken(res.token);
            setUser(res.user);
            window.location.href = '/';
        } else {
            throw new Error('Invalid server response token.');
        }
    } catch (err) {
        alertBox.textContent = err.message || 'Invalid email or password.';
        alertBox.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = origText;
    }
});
</script>
@endpush
