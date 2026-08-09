@extends('admin.layout')

@section('title', 'Manage Orders — Admin Panel')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
    <div>
        <h1 class="h2 font-heading fw-bold text-dark mb-1">Customer Orders</h1>
        <p class="text-muted mb-0">View and update customer orders and shipping status</p>
    </div>
</div>

<div id="admin-order-alert"></div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: #FFFFFF;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4 py-3 text-muted small fw-semibold">ORDER ID</th>
                    <th class="py-3 text-muted small fw-semibold">CUSTOMER</th>
                    <th class="py-3 text-muted small fw-semibold">DATE</th>
                    <th class="py-3 text-muted small fw-semibold">ITEMS</th>
                    <th class="py-3 text-muted small fw-semibold">TOTAL</th>
                    <th class="py-3 text-muted small fw-semibold">STATUS</th>
                    <th class="pe-4 py-3 text-end text-muted small fw-semibold">UPDATE STATUS</th>
                </tr>
            </thead>
            <tbody id="orders-table-body">
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="spinner-border text-emerald" role="status" style="color: var(--accent-emerald);"></div>
                        <p class="text-muted small mt-2">Loading orders...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function loadAdminOrders() {
    const tbody = document.getElementById('orders-table-body');
    try {
        const res = await apiFetch('/admin/orders');
        const orders = res.data || [];

        if (orders.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted">No orders placed yet.</td></tr>`;
            return;
        }

        renderOrdersTable(orders);
    } catch (e) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-danger">Failed to load orders: ${e.message}</td></tr>`;
    }
}

function renderOrdersTable(orders) {
    const tbody = document.getElementById('orders-table-body');
    const statusBadges = {
        'pending': 'bg-warning text-dark',
        'paid': 'bg-success text-white',
        'shipped': 'bg-primary text-white',
        'cancelled': 'bg-danger text-white'
    };

    tbody.innerHTML = orders.map(order => {
        const dateStr = order.created_at ? new Date(order.created_at).toLocaleDateString(undefined, {
            year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
        }) : 'N/A';

        const totalFormatted = (typeof order.total === 'number' ? order.total : parseFloat(order.total || 0)).toFixed(2);
        const badgeClass = statusBadges[order.status?.toLowerCase()] || 'bg-secondary text-white';
        const items = order.items || [];

        return `
            <tr>
                <td class="ps-4 font-heading fw-bold">#${order.id}</td>
                <td>
                    <div class="fw-semibold text-dark">${order.user ? order.user.name : 'Customer'}</div>
                    <div class="small text-muted">${order.user ? order.user.email : ''}</div>
                </td>
                <td class="small text-muted">${dateStr}</td>
                <td class="small fw-semibold">${items.length} items</td>
                <td class="product-price fw-bold">$${totalFormatted}</td>
                <td>
                    <span class="badge ${badgeClass} rounded-pill px-2.5 py-1 text-uppercase" style="font-size: 0.7rem;">
                        ${order.status}
                    </span>
                </td>
                <td class="pe-4 text-end">
                    <select class="form-select form-select-sm d-inline-block status-select" style="width: 140px;" data-id="${order.id}">
                        <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>pending</option>
                        <option value="paid" ${order.status === 'paid' ? 'selected' : ''}>paid</option>
                        <option value="shipped" ${order.status === 'shipped' ? 'selected' : ''}>shipped</option>
                        <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>cancelled</option>
                    </select>
                </td>
            </tr>
        `;
    }).join('');

    tbody.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', async () => {
            const orderId = select.dataset.id;
            const newStatus = select.value;
            const alertBox = document.getElementById('admin-order-alert');

            try {
                await apiFetch(`/admin/orders/${orderId}/status`, {
                    method: 'PATCH',
                    body: JSON.stringify({ status: newStatus })
                });

                alertBox.innerHTML = `
                    <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                        <strong>Order #${orderId}</strong> status updated to <strong>${newStatus}</strong>!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                loadAdminOrders();
            } catch (err) {
                alertBox.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                        ${err.message || 'Failed to update order status.'}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', loadAdminOrders);
</script>
@endpush
