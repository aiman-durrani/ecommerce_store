@extends('admin.layout')

@section('title', 'Manage Products — Admin Panel')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
    <div>
        <h1 class="h2 font-heading fw-bold text-dark mb-1">Products Management</h1>
        <p class="text-muted mb-0">Manage catalog inventory, pricing, and product status</p>
    </div>
    <a href="{{ url('/admin/products/create') }}" class="btn btn-emerald rounded-3">
        <i class="bi bi-plus-lg me-1"></i> Add Product
    </a>
</div>

<div id="admin-product-alert"></div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: #FFFFFF;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4 py-3 text-muted small fw-semibold">PRODUCT</th>
                    <th class="py-3 text-muted small fw-semibold">CATEGORY</th>
                    <th class="py-3 text-muted small fw-semibold">PRICE</th>
                    <th class="py-3 text-muted small fw-semibold">STOCK</th>
                    <th class="py-3 text-muted small fw-semibold">STATUS</th>
                    <th class="pe-4 py-3 text-end text-muted small fw-semibold">ACTIONS</th>
                </tr>
            </thead>
            <tbody id="products-table-body">
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="spinner-border text-emerald" role="status" style="color: var(--accent-emerald);"></div>
                        <p class="text-muted small mt-2">Loading products...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-center mt-4" id="admin-pagination-container"></div>
@endsection

@push('scripts')
<script>
let currentPage = 1;

async function loadAdminProducts(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('products-table-body');
    try {
        const res = await apiFetch(`/products?page=${page}`);
        const products = res.data || [];

        if (products.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5 text-muted">No products found.</td></tr>`;
            return;
        }

        renderProductsTable(products);
        renderPagination(res.meta);
    } catch (e) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5 text-danger">Failed to load products: ${e.message}</td></tr>`;
    }
}

function renderProductsTable(products) {
    const tbody = document.getElementById('products-table-body');

    tbody.innerHTML = products.map(p => {
        const priceFormatted = (typeof p.price === 'number' ? p.price : parseFloat(p.price || 0)).toFixed(2);

        return `
            <tr>
                <td class="ps-4 py-3">
                    <div class="d-flex align-items-center gap-3">
                        <img src="${p.image_path || 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=100&auto=format&fit=crop&q=80'}" 
                             alt="${p.name}" class="rounded-3 object-fit-cover" style="width: 48px; height: 48px; background: #F4F3EF;">
                        <div>
                            <h6 class="font-heading mb-0 fw-bold text-dark">${p.name}</h6>
                            <span class="small text-muted">ID: #${p.id}</span>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge bg-emerald-subtle rounded-pill px-2.5 py-1 small">
                        ${p.category ? p.category.name : 'General'}
                    </span>
                </td>
                <td class="product-price fw-bold">$${priceFormatted}</td>
                <td>
                    <span class="fw-semibold ${p.stock > 0 ? 'text-dark' : 'text-danger'}">${p.stock} units</span>
                </td>
                <td>
                    <span class="badge ${p.is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'} rounded-pill px-2.5 py-1">
                        ${p.is_active ? 'Active' : 'Disabled'}
                    </span>
                </td>
                <td class="pe-4 text-end">
                    <div class="d-inline-flex gap-2">
                        <a href="/admin/products/${p.id}/edit" class="btn btn-sm btn-outline-secondary rounded-2" title="Edit product">
                            <i class="bi bi-pencil-square"></i> Edit
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-2 btn-delete-product" data-id="${p.id}" data-name="${p.name}">
                            <i class="bi bi-trash3"></i> Delete
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    tbody.querySelectorAll('.btn-delete-product').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;
            const name = btn.dataset.name;

            if (!confirm(`Are you sure you want to delete "${name}"?`)) return;

            const alertBox = document.getElementById('admin-product-alert');
            try {
                await apiFetch(`/products/${id}`, { method: 'DELETE' });
                alertBox.innerHTML = `
                    <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                        Product <strong>"${name}"</strong> was deleted successfully.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                loadAdminProducts(currentPage);
            } catch (err) {
                alertBox.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                        ${err.message || 'Failed to delete product.'}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
            }
        });
    });
}

function renderPagination(meta) {
    const container = document.getElementById('admin-pagination-container');
    if (!meta || meta.last_page <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '<ul class="pagination pagination-sm mb-0 shadow-sm rounded-3 overflow-hidden">';
    html += `<li class="page-item ${meta.current_page === 1 ? 'disabled' : ''}">
        <button class="page-link text-dark" data-page="${meta.current_page - 1}">Previous</button>
    </li>`;

    for (let i = 1; i <= meta.last_page; i++) {
        const isActive = (i === meta.current_page);
        html += `<li class="page-item ${isActive ? 'active' : ''}">
            <button class="page-link ${isActive ? 'bg-emerald text-white fw-bold' : 'text-dark'}" 
                    style="${isActive ? 'background-color: var(--accent-emerald) !important;' : ''}"
                    data-page="${i}">${i}</button>
        </li>`;
    }

    html += `<li class="page-item ${meta.current_page === meta.last_page ? 'disabled' : ''}">
        <button class="page-link text-dark" data-page="${meta.current_page + 1}">Next</button>
    </li>`;
    html += '</ul>';

    container.innerHTML = html;
    container.querySelectorAll('.page-link').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const page = parseInt(e.target.dataset.page);
            if (page && page !== currentPage) {
                loadAdminProducts(page);
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', () => loadAdminProducts(1));
</script>
@endpush
