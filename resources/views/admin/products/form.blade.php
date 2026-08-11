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
                        <label for="image" class="form-label small text-muted fw-semibold">Product Image (Optional)</label>
                        <div id="image-preview-container" class="mb-2 d-none">
                            <img id="image-preview" src="" alt="Product Image Preview" class="img-thumbnail rounded-3" style="max-height: 120px; object-fit: cover;">
                        </div>
                        <input type="file" id="image" class="form-control" accept="image/*">
                        <div class="invalid-feedback" id="err-image"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="keywords" class="form-label small text-muted fw-semibold">AI Keywords</label>
                    <input type="text" id="keywords" class="form-control form-control-sm" placeholder="e.g. ergonomic, wireless, long battery life">
                    <div class="invalid-feedback" id="err-keywords"></div>
                </div>

                <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label for="description" class="form-label small text-muted fw-semibold mb-0">Description</label>
                        <button type="button" id="btn-generate-ai" class="btn btn-sm btn-outline-emerald rounded-2">
                            <i class="bi bi-sparkles me-1"></i> Generate with AI
                        </button>
                    </div>
                    <textarea id="description" class="form-control" rows="4" placeholder="Detailed product specifications..."></textarea>
                    <div class="invalid-feedback" id="err-description"></div>
                    <div id="ai-error-alert" class="alert alert-danger d-none rounded-3 mt-2 py-2 px-3 small mb-0" role="alert"></div>
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
            document.getElementById('description').value = p.description || '';
            document.getElementById('is_active').checked = !!p.is_active;

            if (p.image_path) {
                const previewImg = document.getElementById('image-preview');
                const previewContainer = document.getElementById('image-preview-container');
                previewImg.src = p.image_path;
                previewContainer.classList.remove('d-none');
            }
        } catch (e) {
            alert('Failed to load product details for editing.');
            window.location.href = '/admin/products';
        }
    }
}

document.getElementById('image').addEventListener('change', (e) => {
    const file = e.target.files[0];
    const previewImg = document.getElementById('image-preview');
    const previewContainer = document.getElementById('image-preview-container');

    if (file) {
        const reader = new FileReader();
        reader.onload = (event) => {
            previewImg.src = event.target.result;
            previewContainer.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    }
});

document.getElementById('product-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    clearValidationErrors();

    const name = document.getElementById('name').value.trim();
    const category_id = document.getElementById('category_id').value;
    const price = document.getElementById('price').value;
    const stock = document.getElementById('stock').value;
    const description = document.getElementById('description').value.trim();
    const is_active = document.getElementById('is_active').checked ? '1' : '0';
    const imageFile = document.getElementById('image').files[0];

    const formData = new FormData();
    formData.append('name', name);
    formData.append('category_id', category_id);
    formData.append('price', price);
    formData.append('stock', stock);
    formData.append('description', description);
    formData.append('is_active', is_active);

    if (imageFile) {
        formData.append('image', imageFile);
    }

    if (editProductId) {
        formData.append('_method', 'PUT');
    }

    const btn = document.getElementById('btn-submit-form');
    const origText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status"></span> Saving...`;

    try {
        const endpoint = editProductId ? `/products/${editProductId}` : '/products';
        await apiFetch(endpoint, {
            method: 'POST',
            body: formData
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

document.getElementById('btn-generate-ai').addEventListener('click', async () => {
    clearValidationErrors();

    const nameInput = document.getElementById('name');
    const keywordsInput = document.getElementById('keywords');
    const aiErrorAlert = document.getElementById('ai-error-alert');

    const name = nameInput.value.trim();
    const keywords = keywordsInput.value.trim();

    let hasValidationError = false;
    if (!name) {
        nameInput.classList.add('is-invalid');
        const errName = document.getElementById('err-name');
        if (errName) errName.textContent = 'Product name is required for AI description.';
        hasValidationError = true;
    }
    if (!keywords) {
        keywordsInput.classList.add('is-invalid');
        const errKw = document.getElementById('err-keywords');
        if (errKw) errKw.textContent = 'Keywords are required for AI description.';
        hasValidationError = true;
    }

    if (hasValidationError) {
        return;
    }

    const btn = document.getElementById('btn-generate-ai');
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Generating...`;

    try {
        const res = await apiFetch('/admin/products/generate-description', {
            method: 'POST',
            body: JSON.stringify({ name, keywords })
        });

        if (res && res.description) {
            document.getElementById('description').value = res.description;
        }
    } catch (err) {
        if (err.errors) {
            showInlineValidationErrors(err.errors);
        }
        if (aiErrorAlert) {
            aiErrorAlert.textContent = err.message || 'Failed to generate description. Please try again.';
            aiErrorAlert.classList.remove('d-none');
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = origHtml;
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
    if (alertBox) alertBox.classList.add('d-none');
    const aiErrorAlert = document.getElementById('ai-error-alert');
    if (aiErrorAlert) {
        aiErrorAlert.classList.add('d-none');
        aiErrorAlert.textContent = '';
    }
    document.querySelectorAll('.form-control, .form-select').forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
}

document.addEventListener('DOMContentLoaded', initForm);
</script>
@endpush
