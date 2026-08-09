@extends('admin.layout')

@section('title', 'Dashboard Overview — AURA Admin')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
    <div>
        <h1 class="h2 font-heading fw-bold text-dark mb-1">Admin Dashboard</h1>
        <p class="text-muted mb-0">Overview of store metrics, inventory, and sales performance</p>
    </div>
    <a href="{{ url('/admin/products/create') }}" class="btn btn-emerald rounded-3">
        <i class="bi bi-plus-lg me-1"></i> Add New Product
    </a>
</div>

<!-- Overview Metric Cards -->
<div class="row g-4 mb-5">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-4" style="background: #FFFFFF;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-muted small fw-semibold">TOTAL REVENUE</span>
                <span class="d-inline-flex align-items-center justify-content-center bg-emerald-subtle text-emerald rounded-3 p-2">
                    <i class="bi bi-currency-dollar fs-5" style="color: var(--accent-emerald);"></i>
                </span>
            </div>
            <h2 class="font-heading fw-bold text-dark mb-0" id="stat-revenue">$0.00</h2>
            <span class="small text-muted">All completed & placed orders</span>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-4" style="background: #FFFFFF;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-muted small fw-semibold">TOTAL ORDERS</span>
                <span class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3 p-2">
                    <i class="bi bi-receipt fs-5"></i>
                </span>
            </div>
            <h2 class="font-heading fw-bold text-dark mb-0" id="stat-orders">0</h2>
            <span class="small text-muted">Total customer purchases</span>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-4" style="background: #FFFFFF;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-muted small fw-semibold">TOTAL PRODUCTS</span>
                <span class="d-inline-flex align-items-center justify-content-center bg-info-subtle text-info rounded-3 p-2">
                    <i class="bi bi-box-seam fs-5"></i>
                </span>
            </div>
            <h2 class="font-heading fw-bold text-dark mb-0" id="stat-products">0</h2>
            <span class="small text-muted">Active store inventory items</span>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-4" style="background: #FFFFFF;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-muted small fw-semibold">PENDING ORDERS</span>
                <span class="d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning-emphasis rounded-3 p-2">
                    <i class="bi bi-clock-history fs-5"></i>
                </span>
            </div>
            <h2 class="font-heading fw-bold text-dark mb-0" id="stat-pending">0</h2>
            <span class="small text-muted">Awaiting processing/shipping</span>
        </div>
    </div>
</div>

<!-- Status Breakdown Section -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 p-4" style="background: #FFFFFF;">
            <h5 class="font-heading fw-bold text-dark mb-4">Orders Status Breakdown</h5>
            
            <div class="d-flex align-items-center justify-content-between py-2.5 border-bottom">
                <span class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning text-dark rounded-circle p-1"> </span>
                    <span class="fw-medium text-dark">Pending</span>
                </span>
                <span class="fw-bold font-heading" id="breakdown-pending">0</span>
            </div>

            <div class="d-flex align-items-center justify-content-between py-2.5 border-bottom">
                <span class="d-flex align-items-center gap-2">
                    <span class="badge bg-success text-white rounded-circle p-1"> </span>
                    <span class="fw-medium text-dark">Paid</span>
                </span>
                <span class="fw-bold font-heading" id="breakdown-paid">0</span>
            </div>

            <div class="d-flex align-items-center justify-content-between py-2.5 border-bottom">
                <span class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary text-white rounded-circle p-1"> </span>
                    <span class="fw-medium text-dark">Shipped</span>
                </span>
                <span class="fw-bold font-heading" id="breakdown-shipped">0</span>
            </div>

            <div class="d-flex align-items-center justify-content-between py-2.5">
                <span class="d-flex align-items-center gap-2">
                    <span class="badge bg-danger text-white rounded-circle p-1"> </span>
                    <span class="fw-medium text-dark">Cancelled</span>
                </span>
                <span class="fw-bold font-heading" id="breakdown-cancelled">0</span>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100 d-flex flex-column justify-content-between" style="background: #FFFFFF;">
            <div>
                <h5 class="font-heading fw-bold text-dark mb-2">Quick Management</h5>
                <p class="text-muted small mb-4">Direct shortcuts to manage catalog products and update customer orders.</p>
            </div>
            
            <div class="d-grid gap-3">
                <a href="{{ url('/admin/products') }}" class="btn btn-outline-emerald p-3 d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-box-seam me-2"></i> Manage Catalog Products</span>
                    <i class="bi bi-chevron-right"></i>
                </a>
                <a href="{{ url('/admin/orders') }}" class="btn btn-outline-dark p-3 d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-receipt me-2"></i> Manage Customer Orders</span>
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function loadDashboardStats() {
    try {
        const [productsRes, ordersRes] = await Promise.all([
            apiFetch('/products'),
            apiFetch('/admin/orders')
        ]);

        const productsMeta = productsRes.meta;
        const totalProducts = productsMeta ? productsMeta.total : (productsRes.data ? productsRes.data.length : 0);
        document.getElementById('stat-products').textContent = totalProducts;

        const orders = ordersRes.data || [];
        const totalOrders = ordersRes.meta ? ordersRes.meta.total : orders.length;
        document.getElementById('stat-orders').textContent = totalOrders;

        let totalRevenue = 0;
        const statusCounts = { pending: 0, paid: 0, shipped: 0, cancelled: 0 };

        orders.forEach(order => {
            const amount = typeof order.total === 'number' ? order.total : parseFloat(order.total || 0);
            if (order.status !== 'cancelled') {
                totalRevenue += amount;
            }
            const st = (order.status || 'pending').toLowerCase();
            if (statusCounts[st] !== undefined) {
                statusCounts[st]++;
            }
        });

        document.getElementById('stat-revenue').textContent = `$${totalRevenue.toFixed(2)}`;
        document.getElementById('stat-pending').textContent = statusCounts.pending;

        document.getElementById('breakdown-pending').textContent = statusCounts.pending;
        document.getElementById('breakdown-paid').textContent = statusCounts.paid;
        document.getElementById('breakdown-shipped').textContent = statusCounts.shipped;
        document.getElementById('breakdown-cancelled').textContent = statusCounts.cancelled;

    } catch (err) {
        console.error('Failed to compute dashboard stats:', err);
    }
}

document.addEventListener('DOMContentLoaded', loadDashboardStats);
</script>
@endpush
