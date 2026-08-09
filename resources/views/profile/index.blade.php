@extends('layouts.app')

@section('title', 'My Account & Orders — AURA Storefront')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-5 pb-3 border-bottom">
        <div>
            <h1 class="h2 font-heading fw-bold text-dark mb-1">My Account & Orders</h1>
            <p class="text-muted mb-0">View your profile details and recent purchase history</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Account Info Sidebar Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4" style="background: #FFFFFF;">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-emerald text-white rounded-circle fs-3 fw-bold" 
                         style="width: 56px; height: 56px; background-color: var(--accent-emerald);" 
                         id="profile-avatar">
                        U
                    </div>
                    <div>
                        <h5 class="font-heading fw-bold text-dark mb-0" id="profile-name">User Name</h5>
                        <span class="badge bg-emerald-subtle rounded-pill px-2.5 py-1 small">Customer Account</span>
                    </div>
                </div>

                <hr class="my-3 opacity-15">

                <div class="mb-3">
                    <label class="small text-muted fw-semibold d-block mb-1">EMAIL ADDRESS</label>
                    <div class="fw-medium text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-envelope text-muted"></i>
                        <span id="profile-email">user@example.com</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="small text-muted fw-semibold d-block mb-1">ACCOUNT STATUS</label>
                    <div class="text-success small fw-semibold d-flex align-items-center gap-1.5">
                        <i class="bi bi-check-circle-fill"></i> Active & Verified
                    </div>
                </div>

                <hr class="my-3 opacity-15">

                <button type="button" onclick="logoutUser()" class="btn btn-outline-danger btn-sm w-100 rounded-3 py-2">
                    <i class="bi bi-box-arrow-right me-1"></i> Sign Out
                </button>
            </div>
        </div>

        <!-- Orders History Column -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4" style="background: #FFFFFF;">
                <h4 class="font-heading fw-bold text-dark mb-4 d-flex align-items-center gap-2">
                    <i class="bi bi-receipt text-emerald" style="color: var(--accent-emerald);"></i>
                    <span>Order History</span>
                </h4>

                <div id="orders-list-container">
                    <!-- Loading state -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-emerald" role="status" style="color: var(--accent-emerald);">
                            <span class="visually-hidden">Loading order history...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function loadProfileAndOrders() {
    const token = getToken();
    if (!token) {
        window.location.href = '/login';
        return;
    }

    const user = getUser();
    if (user) {
        document.getElementById('profile-name').textContent = user.name || 'Account';
        document.getElementById('profile-email').textContent = user.email || '';
        document.getElementById('profile-avatar').textContent = (user.name || 'U').charAt(0).toUpperCase();
    }

    const container = document.getElementById('orders-list-container');

    try {
        const res = await apiFetch('/orders');
        const orders = res.data || [];

        if (orders.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-bag-x text-muted display-4"></i>
                    <h5 class="font-heading text-dark mt-3">No orders placed yet</h5>
                    <p class="text-muted mb-3">When you purchase items, your order history will appear here.</p>
                    <a href="/" class="btn btn-emerald px-4">Start Shopping</a>
                </div>
            `;
            return;
        }

        renderOrders(orders);
    } catch (e) {
        container.innerHTML = `
            <div class="alert alert-danger rounded-3" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i> Failed to load orders: ${e.message || 'Error occurred'}
            </div>
        `;
    }
}

function renderOrders(orders) {
    const container = document.getElementById('orders-list-container');

    container.innerHTML = orders.map((order, idx) => {
        const dateStr = order.created_at ? new Date(order.created_at).toLocaleDateString(undefined, {
            year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
        }) : 'N/A';

        const totalFormatted = (typeof order.total === 'number' ? order.total : parseFloat(order.total || 0)).toFixed(2);

        const statusBadges = {
            'pending': 'bg-warning text-dark',
            'processing': 'bg-info text-dark',
            'shipped': 'bg-primary text-white',
            'delivered': 'bg-success text-white',
            'cancelled': 'bg-danger text-white'
        };
        const badgeClass = statusBadges[order.status?.toLowerCase()] || 'bg-secondary text-white';

        const items = order.items || [];
        return `
            <div class="card border rounded-3 mb-3 overflow-hidden" style="border-color: var(--border-light) !important;">
                <div class="card-header bg-light p-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom-0">
                    <div>
                        <span class="font-heading fw-bold text-dark me-2">Order #${order.id}</span>
                        <span class="small text-muted me-3"><i class="bi bi-calendar3 me-1"></i>${dateStr}</span>
                        <span class="badge ${badgeClass} rounded-pill px-2.5 py-1 text-uppercase" style="font-size: 0.7rem;">${order.status || 'Pending'}</span>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <span class="product-price fs-6">$${totalFormatted}</span>
                        <button class="btn btn-sm btn-outline-secondary rounded-2 py-1 px-2 text-nowrap" 
                                type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#order-items-${order.id}" 
                                aria-expanded="${idx === 0 ? 'true' : 'false'}" 
                                aria-controls="order-items-${order.id}">
                            <i class="bi bi-list-nested me-1"></i> Details
                        </button>
                    </div>
                </div>

                <div class="collapse ${idx === 0 ? 'show' : ''}" id="order-items-${order.id}">
                    <div class="card-body p-3 bg-white border-top">
                        ${order.shipping_address ? `
                            <div class="mb-3 p-2.5 rounded-3 bg-light text-muted small">
                                <strong>Shipping Address:</strong> ${order.shipping_address}
                            </div>
                        ` : ''}

                        <h6 class="small text-muted fw-bold text-uppercase mb-2">Items Ordered (${items.length})</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>Product</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-end">Price Paid</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${items.map(item => {
                                        const p = item.product || {};
                                        const unitPrice = typeof item.price === 'number' ? item.price : parseFloat(item.price || 0);
                                        const subtotal = unitPrice * item.quantity;
                                        return `
                                            <tr>
                                                <td class="fw-semibold text-dark">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="bi bi-box-seam text-emerald" style="color: var(--accent-emerald);"></i>
                                                        <span>${p.name || 'Product #' + item.product_id}</span>
                                                    </div>
                                                </td>
                                                <td class="text-center">${item.quantity}</td>
                                                <td class="text-end">$${unitPrice.toFixed(2)}</td>
                                                <td class="text-end fw-bold">$${subtotal.toFixed(2)}</td>
                                            </tr>
                                        `;
                                    }).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

document.addEventListener('DOMContentLoaded', loadProfileAndOrders);
</script>
@endpush
