@extends('layouts.app')

@section('title', 'Product Details — AURA Storefront')

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-decoration-none text-muted">Products</a></li>
            <li class="breadcrumb-item active text-dark fw-semibold" id="breadcrumb-title" aria-current="page">Loading...</li>
        </ol>
    </nav>

    <div id="product-detail-container">
        <!-- Skeleton Loader -->
        <div class="row g-5 align-items-center py-4">
            <div class="col-md-6 text-center">
                <div class="spinner-border text-emerald" role="status" style="color: var(--accent-emerald);">
                    <span class="visually-hidden">Loading product details...</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const productId = {{ $id }};

async function loadProductDetail() {
    const container = document.getElementById('product-detail-container');
    try {
        const res = await apiFetch(`/products/${productId}`);
        const p = res.data;

        document.getElementById('breadcrumb-title').textContent = p.name;
        document.title = `${p.name} — AURA Storefront`;

        const priceFormatted = (typeof p.price === 'number' ? p.price : parseFloat(p.price || 0)).toFixed(2);
        const inStock = p.stock > 0;

        container.innerHTML = `
            <div class="row g-5 align-items-center">
                <!-- Image Column -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: #F4F3EF;">
                        <div class="ratio ratio-4x3">
                            <img src="${p.image_path || 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&auto=format&fit=crop&q=80'}" 
                                 alt="${p.name}" class="img-fluid object-fit-cover">
                        </div>
                    </div>
                </div>

                <!-- Info Column -->
                <div class="col-lg-6">
                    <div class="ps-lg-3">
                        <span class="badge bg-emerald-subtle rounded-pill px-3 py-1.5 small mb-3">
                            ${p.category ? p.category.name : 'General'}
                        </span>
                        
                        <h1 class="display-6 font-heading fw-bold text-dark mb-3">${p.name}</h1>
                        
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <span class="product-price display-6 fs-2">$${priceFormatted}</span>
                            <span class="badge ${inStock ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'} rounded-pill px-3 py-1.5 fw-medium">
                                ${inStock ? `<i class="bi bi-check-circle me-1"></i> ${p.stock} units in stock` : '<i class="bi bi-x-circle me-1"></i> Out of stock'}
                            </span>
                        </div>

                        <p class="text-muted fs-6 mb-4 lead" style="line-height: 1.7;">
                            ${p.description || 'No detailed description available for this item.'}
                        </p>

                        <hr class="my-4 text-secondary opacity-15">

                        <!-- Actions -->
                        <div class="row g-3 align-items-center">
                            <div class="col-auto">
                                <label class="form-label small text-muted fw-semibold mb-1">Quantity</label>
                                <div class="input-group" style="width: 140px;">
                                    <button class="btn btn-outline-secondary" type="button" id="btn-qty-minus" ${!inStock ? 'disabled' : ''}>-</button>
                                    <input type="number" id="input-qty" class="form-control text-center border-secondary border-opacity-25" value="1" min="1" max="${p.stock}" ${!inStock ? 'disabled' : ''}>
                                    <button class="btn btn-outline-secondary" type="button" id="btn-qty-plus" ${!inStock ? 'disabled' : ''}>+</button>
                                </div>
                            </div>

                            <div class="col">
                                <label class="form-label d-block small text-muted fw-semibold mb-1">&nbsp;</label>
                                <button type="button" id="btn-add-detail-cart" 
                                        class="btn btn-emerald btn-lg w-100 d-flex align-items-center justify-content-center gap-2 shadow-sm"
                                        ${!inStock ? 'disabled' : ''}>
                                    <i class="bi bi-bag-plus fs-5"></i>
                                    <span>Add to Shopping Cart</span>
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 p-3 rounded-3 bg-white border border-light shadow-sm d-flex align-items-center gap-3">
                            <i class="bi bi-truck text-emerald fs-4" style="color: var(--accent-emerald);"></i>
                            <div>
                                <h6 class="font-heading mb-0 text-dark fw-bold">Fast & Free Shipping</h6>
                                <p class="small text-muted mb-0">Complimentary standard delivery on orders over $50.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Quantity controls
        const qtyInput = document.getElementById('input-qty');
        document.getElementById('btn-qty-minus')?.addEventListener('click', () => {
            let val = parseInt(qtyInput.value) || 1;
            if (val > 1) qtyInput.value = val - 1;
        });
        document.getElementById('btn-qty-plus')?.addEventListener('click', () => {
            let val = parseInt(qtyInput.value) || 1;
            if (val < p.stock) qtyInput.value = val + 1;
        });

        // Add to cart micro-interaction
        document.getElementById('btn-add-detail-cart')?.addEventListener('click', async () => {
            if (!getToken()) {
                window.location.href = '/login';
                return;
            }

            const btn = document.getElementById('btn-add-detail-cart');
            const qty = parseInt(qtyInput.value) || 1;
            const origText = btn.innerHTML;
            btn.disabled = true;

            try {
                await apiFetch('/cart/items', {
                    method: 'POST',
                    body: JSON.stringify({ product_id: p.id, quantity: qty })
                });

                btn.classList.add('btn-added-success');
                btn.innerHTML = `<i class="bi bi-check-lg fs-5"></i> Added ✓`;
                updateCartBadge();

                setTimeout(() => {
                    btn.classList.remove('btn-added-success');
                    btn.innerHTML = origText;
                    btn.disabled = false;
                }, 1400);
            } catch (err) {
                alert(err.message || 'Could not add to cart.');
                btn.disabled = false;
            }
        });

    } catch (e) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-exclamation-octagon text-danger display-4"></i>
                <h3 class="font-heading mt-3">Product Not Found</h3>
                <p class="text-muted">The requested product could not be located or has been deactivated.</p>
                <a href="/" class="btn btn-emerald mt-2">Back to Catalog</a>
            </div>
        `;
    }
}

document.addEventListener('DOMContentLoaded', loadProductDetail);
</script>
@endpush
