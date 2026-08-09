@extends('admin.layout')

@section('title', (isset($id) ? 'Edit Product' : 'Create Product') . ' — Admin Panel')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
    <div>
        <h1 class="h2 font-heading fw-bold text-dark mb-1">{{ isset($id) ? 'Edit Product #' . $id : 'Add New Product' }}</h1>
        <p class="text-muted mb-0">{{ isset($id) ? 'Update inventory specifications, price, and category' : 'Create a new item in the catalog inventory' }}</p>
    </div>
    <a href="{{ url('/admin/products') }}" class="btn btn-outline-secondary rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Back to Products
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5" style="background: #FFFFFF;">
            <!-- Validation Alert Container -->
            <div id="form-error-alert" class="alert alert-danger d-none rounded-3 mb-4" role="alert"></div>

            <form id="product-form">
                <div class="mb-3">
                    <label for="name" class="form-label small text-muted fw-semibold">Product Name <span class="text-danger">*</span></label>
                    <input type="text" id="name" class="form-control" placeholder="e.g. Wireless Ergonomic Mechanical Keyboard" required>
                    <div class="invalid-feedback" id="err-name"></div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="category_id" class="form-label small text-muted fw-semibold">Category <span class="text-danger">*</span></label>
                        <select id="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                        </select>
                        <div class="invalid-feedback" id="err-category_id"></div>
                    </div>

                    <div class="col-md-6">
                        <label for="price" class="form-label small text-muted fw-semibold">Price ($) <span class="text-danger">*</span></label>
                        <input type="number" id="price" class="form-control" placeholder="49.99" step="0.01" min="0" required>
                        <div class="invalid-feedback" id="err-price"></div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="stock" class="form-label small text-muted fw-semibold">Stock Quantity <span class="text-danger">*</span></label>
                        <input type="number" id="stock" class="form-control" placeholder="50" min="0" required>
                        <div class="invalid-feedback" id="err-stock"></div>
                    </div>

                    <div class="col-md-6">
                        <label for="image_path" class="form-label small text-muted fw-semibold">Image URL (Optional)</label>
                        <input type="url" id="image_path" class="form-control" placeholder="https://example.com/image.jpg">
                        <div class="invalid-feedback" id="err-image_path"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label small text-muted fw-semibold">Description</label>
                    <textarea id="description" class="form-control" rows="4" placeholder="Detailed product specifications..."></textarea>
                    <div class="invalid-feedback" id="err-description"></div>
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" id="is_active" checked>
                    <label class="form-check-label fw-medium text-dark" for="is_active">Publish item as Active in Storefront</label>
                </div>

                <div class="d-flex justify-content-end gap-3 pt-3 border-top">
                    <a href="{{ url('/admin/products') }}" class="btn btn-outline-secondary px-4 rounded-3">Cancel</a>
                    <button type="submit" id="btn-submit-form" class="btn btn-emerald px-4 rounded-3">
                        <i class="bi bi-check-lg me-1"></i> Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const editProductId = {{ isset($id) ? $id : 'null' }};

async function initForm() {
    // 1. Load categories
    try {
        const catRes = await apiFetch('/categories');
        const select = document.getElementById('category_id');
        select.innerHTML = '<option value="">Select Category</option>' + 
            (catRes.data || []).map(c => `<option value="${c.id}">${c.name}</option>`).join('');
    } catch (e) {
        console.error('Failed to load categories', e);
    }

    // 2. If edit mode, load product data
    if (editProductId) {
        try {
            const pRes = await apiFetch(`/products/${editProductId}`);
            const p = pRes.data;

            document.getElementById('name').value = p.name || '';
            document.getElementById('category_id').value = p.category ? p.category.id : '';
            document.getElementById('price').value = p.price;
            document.getElementById('stock').value = p.stock;
            document.getElementById('image_path').value = p.image_path || '';
            document.getElementById('description').value = p.description || '';
            document.getElementById('is_active').checked = !!p.is_active;
        } catch (e) {
            alert('Failed to load product details for editing.');
            window.location.href = '/admin/products';
        }
    }
}

document.getElementById('product-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    clearValidationErrors();

    const name = document.getElementById('name').value.trim();
    const category_id = parseInt(document.getElementById('category_id').value);
    const price = parseFloat(document.getElementById('price').value);
    const stock = parseInt(document.getElementById('stock').value);
    const image_path = document.getElementById('image_path').value.trim() || null;
    const description = document.getElementById('description').value.trim();
    const is_active = document.getElementById('is_active').checked;

    const payload = { name, category_id, price, stock, image_path, description, is_active };

    const btn = document.getElementById('btn-submit-form');
    const origText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status"></span> Saving...`;

    try {
        const endpoint = editProductId ? `/products/${editProductId}` : '/products';
        const method = editProductId ? 'PUT' : 'POST';

        await apiFetch(endpoint, {
            method,
            body: JSON.stringify(payload)
        });

        window.location.href = '/admin/products';
    } catch (err) {
        btn.disabled = false;
        btn.innerHTML = origText;

        const alertBox = document.getElementById('form-error-alert');
        if (err.errors) {
            alertBox.textContent = 'Please correct the highlighted validation errors below.';
            alertBox.classList.remove('d-none');
            showInlineValidationErrors(err.errors);
        } else {
            alertBox.textContent = err.message || 'An error occurred while saving.';
            alertBox.classList.remove('d-none');
        }
    }
});

function showInlineValidationErrors(errors) {
    for (const key in errors) {
        const input = document.getElementById(key);
        const errDiv = document.getElementById(`err-${key}`);
        if (input) input.classList.add('is-invalid');
        if (errDiv) errDiv.textContent = errors[key][0];
    }
}

function clearValidationErrors() {
    const alertBox = document.getElementById('form-error-alert');
    alertBox.classList.add('d-none');
    document.querySelectorAll('.form-control, .form-select').forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
}

document.addEventListener('DOMContentLoaded', initForm);
</script>
@endpush
