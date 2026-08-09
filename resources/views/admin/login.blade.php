<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal Login — AURA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="background: #FFFFFF;">
                    <div class="card-body p-4 p-md-5">
                        <!-- Header -->
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center bg-emerald text-white rounded-3 p-2 mb-3 shadow-sm" style="width: 52px; height: 52px; background-color: var(--accent-emerald);">
                                <i class="bi bi-shield-lock-fill fs-3"></i>
                            </div>
                            <h2 class="font-heading fw-bold text-dark mb-1">Admin Portal</h2>
                            <p class="text-muted small">Sign in with an authorized administrator account</p>
                        </div>

                        <!-- Error Alert -->
                        <div id="admin-auth-error" class="alert alert-danger d-none rounded-3 small py-2.5" role="alert"></div>

                        <!-- Form -->
                        <form id="admin-login-form">
                            <div class="mb-3">
                                <label for="email" class="form-label small text-muted fw-semibold">Admin Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                    <input type="email" id="email" class="form-control border-start-0 ps-0" placeholder="admin@example.com" required autocomplete="email">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label small text-muted fw-semibold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                    <input type="password" id="password" class="form-control border-start-0 ps-0" placeholder="••••••••" required autocomplete="current-password">
                                </div>
                            </div>

                            <button type="submit" id="btn-admin-login" class="btn btn-emerald btn-lg w-100 py-2.5 fs-6 shadow-sm mb-3">
                                <span>Sign In to Dashboard</span>
                            </button>
                        </form>

                        <div class="text-center pt-3 border-top">
                            <a href="{{ url('/') }}" class="small text-muted text-decoration-none">
                                <i class="bi bi-arrow-left me-1"></i> Back to Storefront
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/api.js') }}"></script>
    <script>
        document.getElementById('admin-login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const alertBox = document.getElementById('admin-auth-error');
            alertBox.classList.add('d-none');

            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            const btn = document.getElementById('btn-admin-login');
            const origText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status"></span> Authenticating...`;

            try {
                const res = await apiFetch('/login', {
                    method: 'POST',
                    body: JSON.stringify({ email, password })
                });

                if (res.user && res.user.role === 'admin') {
                    setToken(res.token);
                    setUser(res.user);
                    window.location.href = '/admin/dashboard';
                } else {
                    throw new Error('This login is for admin accounts only.');
                }
            } catch (err) {
                alertBox.textContent = err.message || 'Invalid administrator credentials.';
                alertBox.classList.remove('d-none');
                btn.disabled = false;
                btn.innerHTML = origText;
            }
        });
    </script>
</body>
</html>
