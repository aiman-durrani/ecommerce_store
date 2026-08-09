@extends('layouts.app')

@section('title', 'Explore Catalog — AURA Storefront')

@section('content')
<div class="container py-5">
    <!-- Hero Header -->
    <div class="mb-5 text-center text-md-start">
        <span class="badge bg-emerald-subtle rounded-pill px-3 py-1.5 small mb-2">Curated Collection</span>
        <h1 class="display-5 font-heading fw-bold text-dark mb-2">Premium Products</h1>
        <p class="text-muted fs-5 mb-0" style="max-width: 600px;">Explore high-quality crafted items designed for comfort, utility, and modern lifestyle.</p>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: #FFFFFF;">
        <div class="card-body p-3 p-md-4">
            <form id="filter-form" class="row g-3 align-items-center" onsubmit="return false;">
                <!-- Search Input -->
                <div class="col-12 col-md-3">
                    <label for="filter-search" class="form-label small text-muted fw-semibold mb-1">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" id="filter-search" class="form-control border-start-0 ps-0" placeholder="Product name...">
                    </div>
                </div>

                <!-- Category Select -->
                <div class="col-6 col-md-2">
                    <label for="filter-category" class="form-label small text-muted fw-semibold mb-1">Category</label>
                    <select id="filter-category" class="form-select">
                        <option value="">All Categories</option>
                    </select>
                </div>

                <!-- Price Range -->
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted fw-semibold mb-1">Price Range ($)</label>
                    <div class="input-group">
                        <input type="number" id="filter-min-price" class="form-control" placeholder="Min" min="0" step="0.01">
                        <span class="input-group-text bg-light text-muted border-0">–</span>
                        <input type="number" id="filter-max-price" class="form-control" placeholder="Max" min="0" step="0.01">
                    </div>
                </div>

                <!-- Sort dropdown -->
                <div class="col-6 col-md-2">
                    <label for="filter-sort" class="form-label small text-muted fw-semibold mb-1">Sort By</label>
                    <select id="filter-sort" class="form-select">
                        <option value="">Featured</option>
                        <option value="price">Price: Low to High</option>
                        <option value="-price">Price: High to Low</option>
                    </select>
                </div>

                <!-- In-Stock Check & Clear CTA -->
                <div class="col-6 col-md-2 d-flex flex-column justify-content-end">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="filter-instock">
                        <label class="form-check-label text-muted small fw-medium" for="filter-instock">In Stock Only</label>
                    </div>
                    <button type="button" id="btn-reset-filters" class="btn btn-sm btn-outline-secondary rounded-3 w-100">
                        <i class="bi bi-x-circle me-1"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Grid Container -->
    <div id="products-grid" class="row g-4 grid-transition">
        <!-- Skeleton Loading Placeholders -->
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-emerald" role="status" style="color: var(--accent-emerald);">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2">Loading catalog items...</p>
        </div>
    </div>

    <!-- Pagination Controls -->
    <div class="d-flex justify-content-center mt-5" id="pagination-container">
        <!-- Rendered dynamically -->
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentPage = 1;

async function loadCategories() {
    try {
        const res = await apiFetch('/categories');
        const select = document.getElementById('filter-category');
        select.innerHTML = '<option value="">All Categories</option>' + 
            res.data.map(cat => `<option value="${cat.slug}">${cat.name}</option>`).join('');
    } catch (e) {
        console.error('Failed to load categories', e);
    }
}

async function fetchProducts(page = 1) {
    currentPage = page;
    const grid = document.getElementById('products-grid');
    grid.classList.add('grid-fade-out');

    const params = new URLSearchParams();
    params.set('page', page);

    const search = document.getElementById('filter-search').value.trim();
    if (search) params.set('search', search);

    const category = document.getElementById('filter-category').value;
    if (category) params.set('category', category);

    const minPrice = document.getElementById('filter-min-price').value;
    if (minPrice) params.set('min_price', minPrice);

    const maxPrice = document.getElementById('filter-max-price').value;
    if (maxPrice) params.set('max_price', maxPrice);

    const inStock = document.getElementById('filter-instock').checked;
    if (inStock) params.set('in_stock', '1');

    const sort = document.getElementById('filter-sort').value;
    if (sort) params.set('sort', sort);

    try {
        const res = await apiFetch(`/products?${params.toString()}`);
        renderProducts(res.data);
        renderPagination(res.meta);
    } catch (e) {
        grid.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="bi bi-exclamation-triangle text-danger display-4"></i>
                <h4 class="font-heading mt-3">Unable to load products</h4>
                <p class="text-muted">Please check your connection and try again.</p>
            </div>
        `;
    } finally {
        grid.classList.remove('grid-fade-out');
    }
}

function renderProducts(products) {
    const grid = document.getElementById('products-grid');
    if (!products || products.length === 0) {
        grid.innerHTML = `
            <div class="col-12 py-5 text-center">
                <div class="p-4 rounded-4 bg-white shadow-sm d-inline-block" style="max-width: 450px;">
                    <div class="mb-3"><i class="bi bi-search text-muted display-3"></i></div>
                    <h4 class="font-heading fw-bold text-dark">No products found</h4>
                    <p class="text-muted mb-4">We couldn't find any products matching your current search or filter criteria.</p>
                    <button onclick="document.getElementById('btn-reset-filters').click()" class="btn btn-emerald px-4">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Clear Filters
                    </button>
                </div>
            </div>
        `;
        return;
    }

    grid.innerHTML = products.map(p => `
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card product-card h-100">
                <a href="/products/${p.id}" class="text-decoration-none text-dark">
                    <div class="product-img-wrapper">
                        <img src="${p.image_path || 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop&q=80'}" alt="${p.name}" loading="lazy">
                    </div>
                </a>
                <div class="card-body d-flex flex-column p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-emerald-subtle rounded-pill px-2.5 py-1 small">${p.category ? p.category.name : 'General'}</span>
                        <span class="small ${p.stock > 0 ? 'text-success' : 'text-danger'} fw-medium">
                            ${p.stock > 0 ? `${p.stock} in stock` : 'Out of Stock'}
                        </span>
                    </div>
                    <a href="/products/${p.id}" class="text-decoration-none text-dark">
                        <h5 class="card-title font-heading fs-6 fw-bold mb-2 text-truncate" title="${p.name}">${p.name}</h5>
                    </a>
                    <p class="card-text text-muted small mb-3 flex-grow-1 line-clamp-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.4rem;">
                        ${p.description || 'No detailed description available for this item.'}
                    </p>
                    <div class="d-flex align-items-center justify-content-between mt-auto pt-2 border-top">
                        <span class="product-price fs-5">$${(typeof p.price === 'number' ? p.price : parseFloat(p.price || 0)).toFixed(2)}</span>
                        <button type="button" 
                                class="btn btn-sm btn-emerald btn-add-cart d-flex align-items-center gap-1"
                                data-id="${p.id}"
                                ${p.stock <= 0 ? 'disabled' : ''}>
                            <i class="bi bi-cart-plus"></i> Add
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');

    // Attach click event listeners for Add to Cart micro-interaction
    grid.querySelectorAll('.btn-add-cart').forEach(btn => {
        btn.addEventListener('click', async () => {
            const productId = btn.dataset.id;
            if (!getToken()) {
                window.location.href = '/login';
                return;
            }

            const originalContent = btn.innerHTML;
            btn.disabled = true;

            try {
                await apiFetch('/cart/items', {
                    method: 'POST',
                    body: JSON.stringify({ product_id: parseInt(productId), quantity: 1 })
                });

                // Micro-interaction: emerald color flash & "Added ✓" state
                btn.classList.add('btn-added-success');
                btn.innerHTML = `<i class="bi bi-check-lg"></i> Added ✓`;

                // Update sticky navbar cart count with bounce animation
                updateCartBadge();

                setTimeout(() => {
                    btn.classList.remove('btn-added-success');
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                }, 1200);
            } catch (err) {
                alert(err.message || 'Unable to add item to cart.');
                btn.disabled = false;
            }
        });
    });
}

function renderPagination(meta) {
    const container = document.getElementById('pagination-container');
    if (!meta || meta.last_page <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '<ul class="pagination pagination-md mb-0 shadow-sm rounded-3 overflow-hidden">';
    
    // Previous Page
    html += `<li class="page-item ${meta.current_page === 1 ? 'disabled' : ''}">
        <button class="page-link text-dark border-0" data-page="${meta.current_page - 1}" ${meta.current_page === 1 ? 'disabled' : ''}>
            <i class="bi bi-chevron-left"></i> Previous
        </button>
    </li>`;

    // Numeric Pages
    for (let i = 1; i <= meta.last_page; i++) {
        const isActive = (i === meta.current_page);
        html += `<li class="page-item ${isActive ? 'active' : ''}">
            <button class="page-link border-0 ${isActive ? 'bg-emerald text-white fw-bold' : 'text-dark'}" 
                    style="${isActive ? 'background-color: var(--accent-emerald) !important;' : ''}" 
                    data-page="${i}">${i}</button>
        </li>`;
    }

    // Next Page
    html += `<li class="page-item ${meta.current_page === meta.last_page ? 'disabled' : ''}">
        <button class="page-link text-dark border-0" data-page="${meta.current_page + 1}" ${meta.current_page === meta.last_page ? 'disabled' : ''}>
            Next <i class="bi bi-chevron-right"></i>
        </button>
    </li>`;

    html += '</ul>';
    container.innerHTML = html;

    container.querySelectorAll('.page-link').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const pageAttr = e.currentTarget.dataset.page;
            if (pageAttr) {
                const targetPage = parseInt(pageAttr);
                if (targetPage && targetPage !== currentPage) {
                    fetchProducts(targetPage);
                }
            }
        });
    });
}

// Event Listeners for Filters
document.getElementById('filter-search').addEventListener('input', debounce(() => fetchProducts(1), 300));
document.getElementById('filter-category').addEventListener('change', () => fetchProducts(1));
document.getElementById('filter-min-price').addEventListener('input', debounce(() => fetchProducts(1), 300));
document.getElementById('filter-max-price').addEventListener('input', debounce(() => fetchProducts(1), 300));
document.getElementById('filter-instock').addEventListener('change', () => fetchProducts(1));
document.getElementById('filter-sort').addEventListener('change', () => fetchProducts(1));

document.getElementById('btn-reset-filters').addEventListener('click', () => {
    document.getElementById('filter-form').reset();
    fetchProducts(1);
});

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialize page
document.addEventListener('DOMContentLoaded', () => {
    loadCategories();
    fetchProducts(1);
});
</script>
@endpush
