@extends('layouts.app')

@section('title', 'Shopping Cart — AURA Storefront')

@section('content')
<div class="container py-5">
    <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h2 font-heading fw-bold text-dark mb-1">Shopping Cart</h1>
            <p class="text-muted mb-0">Review your selected items and complete your order</p>
        </div>
        <a href="{{ url('/') }}" class="btn btn-sm btn-outline-secondary rounded-3">
            <i class="bi bi-arrow-left me-1"></i> Continue Shopping
        </a>
    </div>

    <!-- Alert Container -->
    <div id="cart-alert-container"></div>

    <div id="cart-content-wrapper">
        <!-- Spinner -->
        <div class="text-center py-5">
            <div class="spinner-border text-emerald" role="status" style="color: var(--accent-emerald);">
                <span class="visually-hidden">Loading cart...</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function loadCart() {
    const wrapper = document.getElementById('cart-content-wrapper');
    const token = getToken();

    if (!token) {
        renderEmptyState('You must be signed in to view your shopping cart.', '/login', 'Sign In Now');
        return;
    }

    try {
        const res = await apiFetch('/cart');
        const items = res.data?.items || [];
        const total = res.data?.total || 0;

        if (items.length === 0) {
            renderEmptyState('Your shopping cart is currently empty.', '/', 'Browse Products');
            return;
        }

        renderCartItems(items, total);
        updateCartBadge();
    } catch (e) {
        wrapper.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-exclamation-triangle text-danger display-4"></i>
                <h4 class="font-heading mt-3">Unable to load cart</h4>
                <p class="text-muted">${e.message || 'Please try again later.'}</p>
            </div>
        `;
    }
}

function renderEmptyState(message, actionUrl, actionText) {
    const wrapper = document.getElementById('cart-content-wrapper');
    wrapper.innerHTML = `
        <div class="text-center py-5">
            <div class="p-5 rounded-4 bg-white shadow-sm d-inline-block" style="max-width: 500px;">
                <div class="mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-emerald-subtle rounded-circle p-4">
                        <i class="bi bi-cart-x display-4 text-emerald" style="color: var(--accent-emerald);"></i>
                    </div>
                </div>
                <h3 class="font-heading fw-bold text-dark mb-2">Cart is Empty</h3>
                <p class="text-muted mb-4">${message}</p>
                <a href="${actionUrl}" class="btn btn-emerald btn-lg px-4 fs-6">
                    <i class="bi bi-bag-plus me-2"></i> ${actionText}
                </a>
            </div>
        </div>
    `;
}

function renderCartItems(items, total) {
    const wrapper = document.getElementById('cart-content-wrapper');
    const shipping = 0.00; // Free shipping banner
    const grandTotal = (typeof total === 'number' ? total : parseFloat(total || 0)) + shipping;

    wrapper.innerHTML = `
        <div class="row g-4">
            <!-- Items List -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: #FFFFFF;">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 text-muted small fw-semibold">PRODUCT</th>
                                    <th class="py-3 text-muted small fw-semibold">PRICE</th>
                                    <th class="py-3 text-muted small fw-semibold" style="width: 140px;">QUANTITY</th>
                                    <th class="py-3 text-muted small fw-semibold">SUBTOTAL</th>
                                    <th class="pe-4 py-3 text-end text-muted small fw-semibold">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${items.map(item => {
                                    const p = item.product || {};
                                    const price = typeof p.price === 'number' ? p.price : parseFloat(p.price || 0);
                                    const subtotal = typeof item.subtotal === 'number' ? item.subtotal : parseFloat(item.subtotal || 0);
                                    return `
                                        <tr>
                                            <td class="ps-4 py-3">
                                                <div class="d-flex align-items-center gap-3">
                                                    <img src="${p.image_path || 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=150&auto=format&fit=crop&q=80'}" 
                                                         alt="${p.name}" class="rounded-3 object-fit-cover" style="width: 60px; height: 60px; background: #F4F3EF;">
                                                    <div>
                                                        <h6 class="font-heading mb-0 fw-bold text-dark">${p.name || 'Item'}</h6>
                                                        <span class="small text-muted">${p.category ? p.category.name : ''}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="fw-semibold">$${price.toFixed(2)}</td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <button class="btn btn-outline-secondary btn-update-qty" data-id="${item.id}" data-qty="${item.quantity - 1}" ${item.quantity <= 1 ? 'disabled' : ''}>-</button>
                                                    <span class="form-control text-center bg-white border-secondary border-opacity-25 px-2">${item.quantity}</span>
                                                    <button class="btn btn-outline-secondary btn-update-qty" data-id="${item.id}" data-qty="${item.quantity + 1}">+</button>
                                                </div>
                                            </td>
                                            <td class="product-price fw-bold">$${subtotal.toFixed(2)}</td>
                                            <td class="pe-4 text-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-remove-item" data-id="${item.id}" title="Remove item">
                                                    <i class="bi bi-trash3 fs-6"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    `;
                                }).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Order Summary & Checkout -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background: #FFFFFF;">
                    <h5 class="font-heading fw-bold text-dark mb-3">Order Summary</h5>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-bold">$${total.toFixed(2)}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Shipping</span>
                        <span class="text-success fw-semibold">FREE</span>
                    </div>

                    <hr class="my-3 opacity-15">

                    <div class="d-flex justify-content-between mb-4">
                        <span class="font-heading fw-bold fs-5 text-dark">Total</span>
                        <span class="product-price fs-4">$${grandTotal.toFixed(2)}</span>
                    </div>

                    <!-- Checkout Shipping Form -->
                    <form id="checkout-form">
                        <div class="mb-3">
                            <label for="shipping_address" class="form-label small text-muted fw-semibold">Shipping Address</label>
                            <textarea id="shipping_address" class="form-control" rows="3" placeholder="Enter full delivery address..." required></textarea>
                            <div class="invalid-feedback" id="address-error">Please provide a valid shipping address.</div>
                        </div>

                        <button type="submit" id="btn-checkout" class="btn btn-emerald btn-lg w-100 py-3 d-flex align-items-center justify-content-center gap-2 shadow-sm">
                            <i class="bi bi-credit-card-2-front"></i>
                            <span>Complete Checkout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    `;

    // Quantity update event listeners
    wrapper.querySelectorAll('.btn-update-qty').forEach(btn => {
        btn.addEventListener('click', async () => {
            const itemId = btn.dataset.id;
            const newQty = parseInt(btn.dataset.qty);
            if (newQty < 1) return;

            try {
                await apiFetch(`/cart/items/${itemId}`, {
                    method: 'PATCH',
                    body: JSON.stringify({ quantity: newQty })
                });
                loadCart();
            } catch (err) {
                showAlert(err.message || 'Could not update quantity', 'danger');
            }
        });
    });

    // Remove item event listeners
    wrapper.querySelectorAll('.btn-remove-item').forEach(btn => {
        btn.addEventListener('click', async () => {
            const itemId = btn.dataset.id;
            try {
                await apiFetch(`/cart/items/${itemId}`, { method: 'DELETE' });
                loadCart();
            } catch (err) {
                showAlert(err.message || 'Could not remove item', 'danger');
            }
        });
    });

    // Checkout form handler
    document.getElementById('checkout-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const address = document.getElementById('shipping_address').value.trim();
        if (!address) return;

        const btn = document.getElementById('btn-checkout');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status"></span> Processing...`;

        try {
            const res = await apiFetch('/checkout', {
                method: 'POST',
                body: JSON.stringify({ shipping_address: address })
            });

            const order = res.data;
            showAlert(`<strong>Order Placed Successfully!</strong> Order ID: #${order?.id || 'CONFIRMED'}. Thank you for shopping with AURA!`, 'success');
            
            // Reload cart (which renders empty state) & update badge count
            setTimeout(() => {
                loadCart();
            }, 1500);

        } catch (err) {
            showAlert(err.message || 'Checkout failed. Please review stock or address.', 'danger');
            btn.disabled = false;
            btn.innerHTML = origText;
        }
    });
}

function showAlert(message, type = 'danger') {
    const alertBox = document.getElementById('cart-alert-container');
    alertBox.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
}

document.addEventListener('DOMContentLoaded', loadCart);
</script>
@endpush
