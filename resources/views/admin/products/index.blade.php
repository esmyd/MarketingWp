@extends('admin.layouts.app')

@section('header', 'Productos')

@section('content')
<style>
    .products-page {
        --wa-green: #25d366;
        --wa-dark: #128c7e;
        --wa-teal: #075e54;
    }

    .products-hero {
        background: linear-gradient(135deg, var(--wa-dark) 0%, var(--wa-teal) 100%);
        color: #fff;
        border-radius: 14px;
        padding: 1.5rem 1.75rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 4px 14px rgba(7, 94, 84, 0.2);
    }

    .products-hero h2 {
        font-size: 1.35rem;
        font-weight: 600;
        margin: 0 0 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .products-hero p {
        margin: 0;
        opacity: 0.9;
        font-size: 0.9rem;
    }

    .products-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }

    .products-stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 1rem 1.1rem;
        border: 1px solid #e9ecef;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
    }

    .products-stat-card .label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 0.25rem;
    }

    .products-stat-card .value {
        font-size: 1.35rem;
        font-weight: 700;
        color: #212529;
    }

    .products-toolbar {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
    }

    .products-toolbar .search-wrap {
        flex: 1;
        min-width: 200px;
        position: relative;
    }

    .products-toolbar .search-wrap i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #adb5bd;
    }

    .products-toolbar .search-wrap input {
        padding-left: 2.25rem;
        border-radius: 8px;
    }

    .products-table-wrap {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
    }

    .products-table {
        margin: 0;
    }

    .products-table thead th {
        background: #f8f9fa;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6c757d;
        border-bottom: 1px solid #e9ecef;
        white-space: nowrap;
    }

    .products-table tbody td {
        vertical-align: middle;
        font-size: 0.875rem;
    }

    .product-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .product-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: #f0fdf4;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
    }

    .product-name {
        font-weight: 600;
        color: #212529;
        line-height: 1.2;
    }

    .product-desc {
        color: #6c757d;
        font-size: 0.78rem;
        margin-top: 0.15rem;
    }

    .price-regular {
        font-weight: 600;
        color: #212529;
    }

    .price-promo {
        color: #dc3545;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .badge-product {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.35em 0.65em;
        border-radius: 6px;
    }

    .badge-active { background: #dcfce7; color: #166534; }
    .badge-inactive { background: #fee2e2; color: #991b1b; }
    .badge-promo { background: #fef3c7; color: #92400e; }
    .badge-stock-ok { background: #e0f2fe; color: #0369a1; }
    .badge-stock-low { background: #ffedd5; color: #c2410c; }
    .badge-stock-out { background: #f3f4f6; color: #6b7280; }

    .btn-wa {
        background: var(--wa-dark);
        border-color: var(--wa-dark);
        color: #fff;
    }

    .btn-wa:hover {
        background: var(--wa-teal);
        border-color: var(--wa-teal);
        color: #fff;
    }

    .btn-wa:focus,
    .btn-wa:active {
        background: var(--wa-teal) !important;
        border-color: var(--wa-teal) !important;
        color: #fff !important;
        box-shadow: 0 0 0 0.2rem rgba(18, 140, 126, 0.35);
    }

    .btn-action {
        width: 34px;
        height: 34px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    .modal-product {
        --wa-green: #25d366;
        --wa-dark: #128c7e;
        --wa-teal: #075e54;
    }

    .modal-product .modal-content {
        max-height: calc(100vh - 2rem);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .modal-product .modal-product-form {
        display: flex;
        flex-direction: column;
        min-height: 0;
        flex: 1 1 auto;
    }

    .modal-product .modal-body {
        overflow-y: auto;
        flex: 1 1 auto;
    }

    .modal-product .modal-footer {
        flex-shrink: 0;
        background: #fff;
        border-top: 1px solid #dee2e6;
        position: sticky;
        bottom: 0;
        z-index: 5;
        gap: 0.5rem;
        justify-content: flex-end;
        padding: 0.85rem 1rem;
    }

    .modal-product .modal-header {
        background: linear-gradient(135deg, var(--wa-dark), var(--wa-teal));
        color: #fff;
    }

    .modal-product .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }

    .form-hint {
        font-size: 0.78rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 2.5rem;
        color: #dee2e6;
        margin-bottom: 0.75rem;
    }

    #productToast {
        position: fixed;
        top: 1rem;
        right: 1rem;
        z-index: 1090;
        min-width: 280px;
    }
</style>

<div class="products-page">
    <div id="productToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="productToastBody"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>

    <div class="products-hero d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h2><i class="fas fa-box-open"></i> Catálogo de productos</h2>
            <p>Gestiona precios, stock y categorías del chatbot de WhatsApp</p>
        </div>
        <button type="button" class="btn btn-light btn-sm px-3" onclick="openCreateModal()" @if($planLimits['products_at_limit'] ?? false) disabled title="Límite de productos alcanzado" @endif>
            <i class="fas fa-plus me-1"></i> Nuevo producto
        </button>
    </div>

    @include('admin.partials.plan-limits-widget', ['planLimits' => $planLimits])

    <div class="products-stats">
        <div class="products-stat-card">
            <div class="label">Cuota productos</div>
            <div class="value" style="color:{{ ($planLimits['products_at_limit'] ?? false) ? '#dc2626' : '#128c7e' }}">{{ $planLimits['usage']['products'] }}/{{ $planLimits['max_products'] }}</div>
        </div>
        <div class="products-stat-card">
            <div class="label">Total</div>
            <div class="value">{{ $stats['total'] }}</div>
        </div>
        <div class="products-stat-card">
            <div class="label">Activos</div>
            <div class="value" style="color:#128c7e">{{ $stats['active'] }}</div>
        </div>
        <div class="products-stat-card">
            <div class="label">En promo</div>
            <div class="value" style="color:#d97706">{{ $stats['promo'] }}</div>
        </div>
        <div class="products-stat-card">
            <div class="label">Sin stock</div>
            <div class="value" style="color:#6c757d">{{ $stats['no_stock'] }}</div>
        </div>
    </div>

    <div class="products-toolbar">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" id="search" class="form-control form-control-sm" placeholder="Buscar por nombre, SKU o categoría...">
        </div>
        <select id="category-filter" class="form-select form-select-sm" style="width:auto;min-width:160px">
            <option value="">Todas las categorías</option>
            @foreach($categories as $category)
                <option value="{{ $category->title }}">{{ $category->icon }} {{ $category->title }}</option>
            @endforeach
        </select>
        <select id="status-filter" class="form-select form-select-sm" style="width:auto;min-width:130px">
            <option value="">Todos</option>
            <option value="1">Activos</option>
            <option value="0">Inactivos</option>
        </select>
    </div>

    <div class="products-table-wrap">
        @if($products->isEmpty())
            <div class="empty-state">
                <div><i class="fas fa-box-open"></i></div>
                <h5>No hay productos</h5>
                <p class="mb-3">Crea el primer producto para el catálogo del bot.</p>
                <button type="button" class="btn btn-wa btn-sm" onclick="openCreateModal()">
                    <i class="fas fa-plus me-1"></i> Crear producto
                </button>
            </div>
        @else
            <div class="table-responsive">
                <table class="table products-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Producto</th>
                            <th>SKU</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th class="text-end pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="productsTableBody">
                        @foreach($products as $product)
                        <tr class="product-row"
                            data-search="{{ strtolower($product->sku . ' ' . $product->name . ' ' . ($product->menuCategory?->title ?? $product->category)) }}"
                            data-category="{{ $product->menuCategory?->title ?? $product->category }}"
                            data-status="{{ $product->is_active ? '1' : '0' }}">
                            <td class="ps-3">
                                <div class="product-cell">
                                    <div class="product-icon">{{ $product->icon }}</div>
                                    <div>
                                        <div class="product-name">{{ $product->name }}</div>
                                        @if($product->description)
                                            <div class="product-desc">{{ Str::limit($product->description, 55) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td><code>{{ $product->sku }}</code></td>
                            <td>{{ $product->menuCategory?->title ?? $product->category ?? '—' }}</td>
                            <td>
                                @if($product->is_promo && $product->promo_price)
                                    <div class="price-promo">${{ number_format($product->promo_price, 2) }}</div>
                                    <div class="text-muted text-decoration-line-through" style="font-size:0.78rem">${{ number_format($product->price, 2) }}</div>
                                @else
                                    <div class="price-regular">${{ number_format($product->price, 2) }}</div>
                                @endif
                            </td>
                            <td>
                                @if($product->stock <= 0)
                                    <span class="badge badge-product badge-stock-out">Agotado</span>
                                @elseif($product->stock <= 5)
                                    <span class="badge badge-product badge-stock-low">{{ $product->stock }} uds.</span>
                                @else
                                    <span class="badge badge-product badge-stock-ok">{{ $product->stock }} uds.</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-product {{ $product->is_active ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                                @if($product->is_promo)
                                    <span class="badge badge-product badge-promo ms-1">Promo</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-outline-secondary btn-action me-1" title="Editar" onclick="openEditModal({{ $product->id }})">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-action" title="Eliminar" onclick="deleteProduct({{ $product->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="modal fade modal-product" id="productModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form id="productForm" class="modal-product-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle"><i class="fas fa-box me-2"></i>Nuevo producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="sku" id="sku" required maxlength="4" pattern="[A-Za-z0-9]{1,4}" title="Hasta 4 caracteres alfanuméricos">
                            <div class="form-hint">Máximo 4 caracteres (ej: 1001, A01)</div>
                        </div>
                        <div class="col-md-8">
                            <label for="menu_item_id" class="form-label">Categoría <span class="text-danger">*</span></label>
                            <select name="menu_item_id" id="menu_item_id" class="form-select form-select-sm" required>
                                <option value="">Seleccione categoría</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->icon }} {{ $category->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="name" id="name" required maxlength="255">
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label">Descripción</label>
                            <textarea id="description" name="description" rows="2" class="form-control form-control-sm"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="price" class="form-label">Precio <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" name="price" id="price" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="promo_price" class="form-label">Precio promocional</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" name="promo_price" id="promo_price" class="form-control">
                            </div>
                            <div class="form-hint">Debe ser menor al precio regular</div>
                        </div>
                        <div class="col-md-4">
                            <label for="stock" class="form-label">Stock</label>
                            <input type="number" min="0" name="stock" id="stock" class="form-control form-control-sm" value="0">
                        </div>
                        <div class="col-md-4">
                            <label for="min_quantity" class="form-label">Cant. mínima</label>
                            <input type="number" min="1" name="min_quantity" id="min_quantity" class="form-control form-control-sm" value="1">
                        </div>
                        <div class="col-md-4">
                            <label for="max_quantity" class="form-label">Cant. máxima</label>
                            <input type="number" min="1" name="max_quantity" id="max_quantity" class="form-control form-control-sm" value="999">
                        </div>
                        <div class="col-md-6">
                            <label for="benefits" class="form-label">Beneficios</label>
                            <textarea id="benefits" name="benefits" rows="3" class="form-control form-control-sm"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="characteristics" class="form-label">Características</label>
                            <textarea id="characteristics" name="characteristics" rows="3" class="form-control form-control-sm" placeholder="Una característica por línea"></textarea>
                            <div class="form-hint">Se muestran en el detalle del producto en WhatsApp</div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">Producto activo en el catálogo</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="allow_quantity_selection" name="allow_quantity_selection" checked>
                                <label class="form-check-label" for="allow_quantity_selection">Permitir elegir cantidad</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-wa btn-sm px-4" id="saveProductBtn">
                        <i class="fas fa-save me-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentProductId = null;
let productModal = null;
let productToast = null;

document.addEventListener('DOMContentLoaded', function() {
    productModal = new bootstrap.Modal(document.getElementById('productModal'));
    productToast = new bootstrap.Toast(document.getElementById('productToast'), { delay: 4500 });

    document.getElementById('search')?.addEventListener('input', filterTable);
    document.getElementById('category-filter')?.addEventListener('change', filterTable);
    document.getElementById('status-filter')?.addEventListener('change', filterTable);
    document.getElementById('productForm')?.addEventListener('submit', submitProductForm);
});

const productsAtLimit = @json($planLimits['products_at_limit'] ?? false);
const productLimitMessage = @json($planLimits['products_at_limit'] ? ($planLimits['usage']['products'] . '/' . $planLimits['max_products'] . ' productos — límite alcanzado') : '');

function openCreateModal() {
    if (productsAtLimit) {
        showToast(productLimitMessage || 'Has alcanzado el límite de productos.', 'danger');
        return;
    }
    currentProductId = null;
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus me-2"></i>Nuevo producto';
    document.getElementById('productForm').reset();
    document.getElementById('is_active').checked = true;
    document.getElementById('allow_quantity_selection').checked = true;
    document.getElementById('min_quantity').value = 1;
    document.getElementById('max_quantity').value = 999;
    document.getElementById('stock').value = 0;
    hideFormErrors();
    productModal.show();
}

function openEditModal(id) {
    currentProductId = id;
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-pen me-2"></i>Editar producto';
    hideFormErrors();

    fetch(`/admin/products/${id}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => {
        if (!r.ok) throw new Error('No se pudo cargar el producto');
        return r.json();
    })
    .then(data => {
        document.getElementById('sku').value = data.sku || '';
        document.getElementById('name').value = data.name || '';
        document.getElementById('description').value = data.description || '';
        document.getElementById('menu_item_id').value = data.menu_item_id || '';
        document.getElementById('price').value = data.price ?? '';
        document.getElementById('promo_price').value = data.promo_price ?? '';
        document.getElementById('benefits').value = data.benefits || '';
        document.getElementById('characteristics').value = data.characteristics || '';
        document.getElementById('stock').value = data.stock ?? 0;
        document.getElementById('min_quantity').value = data.min_quantity ?? 1;
        document.getElementById('max_quantity').value = data.max_quantity ?? 999;
        document.getElementById('is_active').checked = !!data.is_active;
        document.getElementById('allow_quantity_selection').checked = data.allow_quantity_selection !== false;
        productModal.show();
    })
    .catch(err => {
        showToast(err.message || 'Error al cargar el producto', 'danger');
    });
}

function submitProductForm(e) {
    e.preventDefault();
    hideFormErrors();

    const btn = document.getElementById('saveProductBtn');
    btn.disabled = true;

    const payload = {
        sku: document.getElementById('sku').value.trim(),
        name: document.getElementById('name').value.trim(),
        menu_item_id: document.getElementById('menu_item_id').value,
        price: document.getElementById('price').value,
        promo_price: document.getElementById('promo_price').value,
        description: document.getElementById('description').value,
        benefits: document.getElementById('benefits').value,
        characteristics: document.getElementById('characteristics').value,
        stock: document.getElementById('stock').value,
        min_quantity: document.getElementById('min_quantity').value,
        max_quantity: document.getElementById('max_quantity').value,
        is_active: document.getElementById('is_active').checked,
        allow_quantity_selection: document.getElementById('allow_quantity_selection').checked,
    };

    const isEdit = currentProductId !== null;
    const url = isEdit ? `/admin/products/${currentProductId}` : '{{ route("admin.products.store") }}';
    const method = isEdit ? 'PUT' : 'POST';

    fetch(url, {
        method,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
    })
    .then(async r => {
        const data = await r.json();
        if (!r.ok) {
            if (data.errors) {
                showFormErrors(data.errors);
            }
            throw new Error(data.message || 'Error al guardar');
        }
        return data;
    })
    .then(data => {
        productModal.hide();
        showToast(data.message || 'Guardado correctamente', 'success');
        setTimeout(() => window.location.reload(), 800);
    })
    .catch(err => {
        if (!document.getElementById('formErrors').classList.contains('d-none')) return;
        showToast(err.message || 'Error al guardar el producto', 'danger');
    })
    .finally(() => {
        btn.disabled = false;
    });
}

function deleteProduct(id) {
    if (!confirm('¿Eliminar este producto? Esta acción no se puede deshacer.')) return;

    fetch(`/admin/products/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
    })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
        if (!ok) throw new Error(data.message || 'Error al eliminar');
        showToast(data.message || 'Producto eliminado', 'success');
        setTimeout(() => window.location.reload(), 800);
    })
    .catch(err => showToast(err.message || 'Error al eliminar', 'danger'));
}

function filterTable() {
    const search = (document.getElementById('search')?.value || '').toLowerCase();
    const category = document.getElementById('category-filter')?.value || '';
    const status = document.getElementById('status-filter')?.value || '';

    document.querySelectorAll('.product-row').forEach(row => {
        const matchesSearch = !search || row.dataset.search.includes(search);
        const matchesCategory = !category || row.dataset.category === category;
        const matchesStatus = !status || row.dataset.status === status;
        row.style.display = matchesSearch && matchesCategory && matchesStatus ? '' : 'none';
    });
}

function showFormErrors(errors) {
    const el = document.getElementById('formErrors');
    const messages = Object.values(errors).flat();
    el.innerHTML = messages.map(m => `<div>${m}</div>`).join('');
    el.classList.remove('d-none');
}

function hideFormErrors() {
    const el = document.getElementById('formErrors');
    el.classList.add('d-none');
    el.innerHTML = '';
}

function showToast(message, type = 'success') {
    const toastEl = document.getElementById('productToast');
    const body = document.getElementById('productToastBody');
    toastEl.className = `toast align-items-center border-0 text-white bg-${type === 'success' ? 'success' : 'danger'}`;
    body.textContent = message;
    productToast.show();
}
</script>
@endpush
