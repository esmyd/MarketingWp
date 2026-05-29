@extends('admin.layouts.app')

@section('header', 'Pedidos')

@section('content')
@php
    $statusLabels = [
        'pending' => 'Pendiente',
        'confirmed' => 'Confirmado',
        'completed' => 'Completado',
        'cancelled' => 'Cancelado',
        'payment_pending' => 'Pago pendiente',
        'paid' => 'Pagado',
    ];
    $statusOptions = ['pending', 'confirmed', 'payment_pending', 'paid', 'completed', 'cancelled'];
@endphp

<style>
    .orders-page {
        --wa-green: #25d366;
        --wa-dark: #128c7e;
        --wa-teal: #075e54;
    }

    .orders-hero {
        background: linear-gradient(135deg, var(--wa-dark) 0%, var(--wa-teal) 100%);
        color: #fff;
        border-radius: 14px;
        padding: 1.5rem 1.75rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 4px 14px rgba(7, 94, 84, 0.2);
    }

    .orders-hero h2 {
        font-size: 1.35rem;
        font-weight: 600;
        margin: 0 0 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .orders-hero p {
        margin: 0;
        opacity: 0.9;
        font-size: 0.9rem;
    }

    .orders-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }

    .orders-stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 1rem 1.1rem;
        border: 1px solid #e9ecef;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
    }

    .orders-stat-card .label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 0.25rem;
    }

    .orders-stat-card .value {
        font-size: 1.35rem;
        font-weight: 700;
        color: #212529;
        line-height: 1.2;
    }

    .orders-stat-card .value.revenue {
        color: var(--wa-dark);
    }

    .orders-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .orders-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #eef1f4;
        background: #fafbfc;
    }

    .orders-search {
        position: relative;
        flex: 1;
        min-width: 200px;
        max-width: 320px;
    }

    .orders-search i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #adb5bd;
        font-size: 0.85rem;
    }

    .orders-search input {
        width: 100%;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 0.5rem 0.75rem 0.5rem 2.25rem;
        font-size: 0.875rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .orders-search input:focus {
        outline: none;
        border-color: var(--wa-green);
        box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.15);
    }

    .orders-table-wrap {
        overflow-x: auto;
    }

    .orders-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }

    .orders-table thead th {
        background: #f8f9fa;
        color: #495057;
        font-weight: 600;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 0.85rem 1.25rem;
        text-align: left;
        border-bottom: 1px solid #e9ecef;
        white-space: nowrap;
    }

    .orders-table thead th.text-end { text-align: right; }
    .orders-table thead th.text-center { text-align: center; }

    .orders-table tbody tr {
        border-bottom: 1px solid #f1f3f5;
        transition: background 0.15s;
    }

    .orders-table tbody tr:hover {
        background: #f8fdf9;
    }

    .orders-table tbody tr:last-child {
        border-bottom: none;
    }

    .orders-table tbody td {
        padding: 1rem 1.25rem;
        vertical-align: middle;
    }

    .order-client {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .order-client-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #25d366, #128c7e);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.95rem;
        flex-shrink: 0;
    }

    .order-client-name {
        font-weight: 600;
        color: #212529;
        border: none;
        background: none;
        padding: 0;
        cursor: pointer;
        text-align: left;
        transition: color 0.15s;
    }

    .order-client-name:hover {
        color: var(--wa-dark);
        text-decoration: underline;
    }

    .order-client-phone {
        font-size: 0.8rem;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 0.35rem;
        margin-top: 2px;
    }

    .btn-copy-phone {
        border: none;
        background: transparent;
        color: #adb5bd;
        padding: 2px 4px;
        cursor: pointer;
        border-radius: 4px;
        transition: color 0.15s, background 0.15s;
    }

    .btn-copy-phone:hover {
        color: var(--wa-dark);
        background: #e8f8ef;
    }

    .order-date {
        color: #495057;
    }

    .order-date small {
        display: block;
        color: #adb5bd;
        font-size: 0.75rem;
        margin-top: 2px;
    }

    .order-status-select {
        appearance: none;
        border: none;
        border-radius: 20px;
        padding: 0.35rem 2rem 0.35rem 0.85rem;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236c757d' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.6rem center;
        transition: opacity 0.15s, transform 0.15s;
    }

    .order-status-select:hover { opacity: 0.9; }
    .order-status-select:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.25);
    }

    .order-status-select.status-pending { background: #fff8e6; color: #b8860b; }
    .order-status-select.status-confirmed { background: #e7f1ff; color: #0d6efd; }
    .order-status-select.status-completed { background: #e8f8ef; color: #198754; }
    .order-status-select.status-cancelled { background: #fdecea; color: #dc3545; }
    .order-status-select.status-payment_pending { background: #fff3e0; color: #e65100; }
    .order-status-select.status-paid { background: #ede7f6; color: #6f42c1; }

    .order-total {
        font-weight: 700;
        font-size: 1rem;
        color: #212529;
        text-align: right;
    }

    .order-id-tag {
        font-size: 0.7rem;
        color: #adb5bd;
        font-weight: 500;
    }

    .btn-order-detail {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 0.9rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--wa-dark);
        background: #e8f8ef;
        border: 1px solid rgba(37, 211, 102, 0.35);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.15s;
    }

    .btn-order-detail:hover {
        background: var(--wa-green);
        color: #fff;
        border-color: var(--wa-green);
    }

    .orders-empty {
        text-align: center;
        padding: 3rem 1.5rem;
        color: #6c757d;
    }

    .orders-empty i {
        font-size: 2.5rem;
        color: #dee2e6;
        margin-bottom: 0.75rem;
    }

    .orders-pagination {
        padding: 1rem 1.25rem;
        border-top: 1px solid #eef1f4;
    }

    /* Modales */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(4px);
        z-index: 1050;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s, visibility 0.2s;
    }

    .modal-overlay.is-open {
        opacity: 1;
        visibility: visible;
    }

    .modal-panel {
        background: #fff;
        border-radius: 16px;
        width: 100%;
        max-width: 520px;
        max-height: 90vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
        transform: translateY(12px) scale(0.98);
        transition: transform 0.2s;
    }

    .modal-overlay.is-open .modal-panel {
        transform: translateY(0) scale(1);
    }

    .modal-panel.modal-lg {
        max-width: 560px;
    }

    .modal-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #eef1f4;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        background: linear-gradient(135deg, #f8fdf9 0%, #fff 100%);
    }

    .modal-header h3 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        color: #212529;
    }

    .modal-header .subtitle {
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 0.2rem;
    }

    .modal-close {
        width: 36px;
        height: 36px;
        border: none;
        background: #f1f3f5;
        border-radius: 50%;
        color: #495057;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: background 0.15s;
    }

    .modal-close:hover {
        background: #e9ecef;
        color: #212529;
    }

    .modal-body {
        padding: 1.25rem 1.5rem;
        overflow-y: auto;
        flex: 1;
    }

    .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #eef1f4;
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
        background: #fafbfc;
    }

    .btn-modal-secondary {
        padding: 0.5rem 1rem;
        border: 1px solid #dee2e6;
        background: #fff;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        color: #495057;
        cursor: pointer;
        transition: background 0.15s;
    }

    .btn-modal-secondary:hover { background: #f8f9fa; }

    .btn-modal-primary {
        padding: 0.5rem 1.25rem;
        border: none;
        background: var(--wa-green);
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        color: #fff;
        cursor: pointer;
        transition: background 0.15s;
    }

    .btn-modal-primary:hover { background: var(--wa-dark); }

    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }

    .detail-item {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 0.75rem 1rem;
    }

    .detail-item.full { grid-column: 1 / -1; }

    .detail-item .label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6c757d;
        margin-bottom: 0.2rem;
    }

    .detail-item .value {
        font-size: 0.9rem;
        font-weight: 600;
        color: #212529;
    }

    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.65rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-badge.status-pending { background: #fff8e6; color: #b8860b; }
    .status-badge.status-confirmed { background: #e7f1ff; color: #0d6efd; }
    .status-badge.status-completed { background: #e8f8ef; color: #198754; }
    .status-badge.status-cancelled { background: #fdecea; color: #dc3545; }
    .status-badge.status-payment_pending { background: #fff3e0; color: #e65100; }
    .status-badge.status-paid { background: #ede7f6; color: #6f42c1; }

    .products-section h4 {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6c757d;
        margin: 0 0 0.75rem;
        font-weight: 600;
    }

    .products-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
    }

    .products-table th {
        text-align: left;
        padding: 0.5rem 0.75rem;
        background: #f8f9fa;
        color: #6c757d;
        font-weight: 600;
        font-size: 0.7rem;
        text-transform: uppercase;
    }

    .products-table th:not(:first-child) { text-align: center; }
    .products-table th:last-child { text-align: right; }

    .products-table td {
        padding: 0.65rem 0.75rem;
        border-bottom: 1px solid #f1f3f5;
    }

    .products-table td:not(:first-child) { text-align: center; }
    .products-table td:last-child { text-align: right; font-weight: 600; }

    .products-table tbody tr:last-child td { border-bottom: none; }

    .order-summary {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 2px dashed #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .order-summary .total-label {
        font-size: 0.9rem;
        color: #6c757d;
    }

    .order-summary .total-amount {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--wa-dark);
    }

    .modal-loading {
        text-align: center;
        padding: 2.5rem;
        color: #adb5bd;
    }

    .modal-loading .spinner {
        width: 36px;
        height: 36px;
        border: 3px solid #e9ecef;
        border-top-color: var(--wa-green);
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
        margin: 0 auto 0.75rem;
    }

    @keyframes spin { to { transform: rotate(360deg); } }

    .toast-orders {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        z-index: 1100;
        padding: 0.75rem 1.25rem;
        background: #212529;
        color: #fff;
        border-radius: 10px;
        font-size: 0.875rem;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        opacity: 0;
        transform: translateY(10px);
        transition: opacity 0.25s, transform 0.25s;
        pointer-events: none;
    }

    .toast-orders.show {
        opacity: 1;
        transform: translateY(0);
    }

    .toast-orders.success { background: #198754; }
    .toast-orders.error { background: #dc3545; }

    @media (max-width: 768px) {
        .detail-grid { grid-template-columns: 1fr; }
        .orders-table thead { display: none; }
        .orders-table tbody tr {
            display: block;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border: 1px solid #e9ecef;
            border-radius: 10px;
        }
        .orders-table tbody td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.4rem 0;
            border: none;
        }
        .orders-table tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            font-size: 0.75rem;
            color: #6c757d;
            text-transform: uppercase;
        }
        .orders-table tbody td.text-end { justify-content: space-between; }
    }
</style>

<div class="orders-page">
    <div class="orders-hero">
        <h2><i class="fas fa-shopping-bag"></i> Pedidos</h2>
        <p>Gestiona y da seguimiento a los pedidos realizados por WhatsApp</p>
    </div>

    <div class="orders-stats">
        <div class="orders-stat-card">
            <div class="label">Total pedidos</div>
            <div class="value">{{ $stats['total'] ?? 0 }}</div>
        </div>
        <div class="orders-stat-card">
            <div class="label">Pendientes</div>
            <div class="value">{{ $stats['pending'] ?? 0 }}</div>
        </div>
        <div class="orders-stat-card">
            <div class="label">Confirmados</div>
            <div class="value">{{ $stats['confirmed'] ?? 0 }}</div>
        </div>
        <div class="orders-stat-card">
            <div class="label">Completados</div>
            <div class="value">{{ $stats['completed'] ?? 0 }}</div>
        </div>
        <div class="orders-stat-card">
            <div class="label">Ingresos</div>
            <div class="value revenue">${{ number_format($stats['revenue'] ?? 0, 2) }}</div>
        </div>
    </div>

    <div class="orders-card">
        <div class="orders-toolbar">
            <div class="orders-search">
                <i class="fas fa-search"></i>
                <input type="text" id="orders-search" placeholder="Buscar por cliente o teléfono..." autocomplete="off">
            </div>
            <span class="text-muted small">{{ $orders->total() }} pedido(s) en total</span>
        </div>

        <div class="orders-table-wrap">
            <table class="orders-table" id="orders-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            $initial = strtoupper(mb_substr($order->contact->name ?? 'C', 0, 1));
                            $itemsCount = $order->items->count();
                        @endphp
                        <tr id="order-row-{{ $order->id }}" data-search="{{ strtolower(($order->contact->name ?? '') . ' ' . ($order->contact->phone_number ?? '')) }}">
                            <td data-label="Cliente">
                                <div class="order-client">
                                    <div class="order-client-avatar">{{ $initial }}</div>
                                    <div>
                                        <button type="button" class="order-client-name" onclick="showContactModal({{ $order->contact->id }})">
                                            {{ $order->contact->name ?? 'Cliente' }}
                                        </button>
                                        <div class="order-client-phone">
                                            <span>{{ $order->contact->phone_number ?? 'Sin teléfono' }}</span>
                                            @if($order->contact->phone_number)
                                                <button type="button" class="btn-copy-phone" title="Copiar teléfono" onclick="copyPhone('{{ $order->contact->phone_number }}', this)">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Fecha" class="order-date">
                                {{ $order->created_at->format('d/m/Y') }}
                                <small>{{ $order->created_at->format('H:i') }}</small>
                            </td>
                            <td data-label="Estado">
                                <select
                                    class="order-status-select status-{{ $order->status }}"
                                    id="status-select-{{ $order->id }}"
                                    data-current-status="{{ $order->status }}"
                                    onchange="changeOrderStatus({{ $order->id }}, this)"
                                    aria-label="Estado del pedido"
                                >
                                    @foreach($statusOptions as $status)
                                        <option value="{{ $status }}" @selected($order->status === $status)>
                                            {{ $statusLabels[$status] ?? ucfirst($status) }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td data-label="Total" class="text-end">
                                <div class="order-total">${{ number_format($order->total, 2) }}</div>
                                <div class="order-id-tag">#{{ $order->id }} · {{ $itemsCount }} {{ $itemsCount === 1 ? 'producto' : 'productos' }}</div>
                            </td>
                            <td data-label="Acciones" class="text-center">
                                <button type="button" class="btn-order-detail" onclick="showOrderDetails({{ $order->id }})">
                                    <i class="fas fa-eye"></i> Ver detalles
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="orders-empty">
                                    <i class="fas fa-inbox d-block"></i>
                                    <p class="mb-0 fw-semibold">No hay pedidos registrados</p>
                                    <p class="small text-muted mb-0">Los pedidos de tus clientes aparecerán aquí</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="orders-pagination">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal pedido -->
<div class="modal-overlay" id="orderModal" role="dialog" aria-modal="true" aria-labelledby="orderModalTitle">
    <div class="modal-panel modal-lg">
        <div class="modal-header">
            <div>
                <h3 id="orderModalTitle">Detalles del pedido</h3>
                <p class="subtitle mb-0" id="orderModalSubtitle">Cargando...</p>
            </div>
            <button type="button" class="modal-close" onclick="closeOrderModal()" aria-label="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="orderDetails">
            <div class="modal-loading">
                <div class="spinner"></div>
                <span>Cargando pedido...</span>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-modal-secondary" onclick="closeOrderModal()">Cerrar</button>
        </div>
    </div>
</div>

<!-- Modal contacto -->
<div class="modal-overlay" id="contactModal" role="dialog" aria-modal="true" aria-labelledby="contactModalTitle">
    <div class="modal-panel">
        <div class="modal-header">
            <div>
                <h3 id="contactModalTitle">Datos del contacto</h3>
                <p class="subtitle mb-0" id="contactModalSubtitle"></p>
            </div>
            <button type="button" class="modal-close" onclick="closeContactModal()" aria-label="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="contactDetails">
            <div class="modal-loading">
                <div class="spinner"></div>
                <span>Cargando contacto...</span>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-modal-secondary" onclick="closeContactModal()">Cerrar</button>
        </div>
    </div>
</div>

<div class="toast-orders" id="orders-toast" role="status"></div>

<script>
const STATUS_LABELS = @json($statusLabels);

function showToast(message, type = 'success') {
    const toast = document.getElementById('orders-toast');
    toast.textContent = message;
    toast.className = 'toast-orders show ' + type;
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => toast.classList.remove('show'), 2800);
}

function copyPhone(phone, btn) {
    navigator.clipboard.writeText(phone).then(() => {
        const icon = btn.querySelector('i');
        if (icon) {
            icon.className = 'fas fa-check';
            setTimeout(() => { icon.className = 'fas fa-copy'; }, 1500);
        }
        showToast('Teléfono copiado');
    });
}

function openModal(id) {
    const modal = document.getElementById(id);
    modal.classList.add('is-open');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    const modal = document.getElementById(id);
    modal.classList.remove('is-open');
    if (!document.querySelector('.modal-overlay.is-open')) {
        document.body.style.overflow = '';
    }
}

function statusBadgeHtml(status) {
    const label = STATUS_LABELS[status] || status;
    return `<span class="status-badge status-${status}">${label}</span>`;
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleString('es-ES', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}

function showOrderDetails(orderId) {
    openModal('orderModal');
    const detailsDiv = document.getElementById('orderDetails');
    const subtitle = document.getElementById('orderModalSubtitle');
    subtitle.textContent = 'Cargando...';
    detailsDiv.innerHTML = '<div class="modal-loading"><div class="spinner"></div><span>Cargando pedido...</span></div>';

    fetch(`/admin/orders/${orderId}/details`)
        .then(res => res.json())
        .then(order => {
            subtitle.textContent = `Pedido #${order.id} · ${formatDate(order.created_at)}`;
            let html = `
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="label">Cliente</div>
                        <div class="value">${order.contact?.name ?? 'Cliente'}</div>
                    </div>
                    <div class="detail-item">
                        <div class="label">Teléfono</div>
                        <div class="value">${order.contact?.phone_number ?? '—'}</div>
                    </div>
                    <div class="detail-item">
                        <div class="label">Estado</div>
                        <div class="value">${statusBadgeHtml(order.status)}</div>
                    </div>
                    <div class="detail-item">
                        <div class="label">Fecha</div>
                        <div class="value">${formatDate(order.created_at)}</div>
                    </div>
                </div>
                <div class="products-section">
                    <h4><i class="fas fa-box-open me-1"></i> Productos</h4>`;

            if (order.items && order.items.length > 0) {
                html += `<table class="products-table">
                    <thead><tr>
                        <th>Producto</th>
                        <th>Cant.</th>
                        <th>Precio</th>
                        <th>Subtotal</th>
                    </tr></thead><tbody>`;
                order.items.forEach(item => {
                    const subtotal = (parseFloat(item.price) * parseInt(item.quantity)).toFixed(2);
                    html += `<tr>
                        <td>${item.name}</td>
                        <td>${item.quantity}</td>
                        <td>$${parseFloat(item.price).toFixed(2)}</td>
                        <td>$${subtotal}</td>
                    </tr>`;
                });
                html += `</tbody></table>`;
            } else {
                html += `<p class="text-muted small mb-0">Sin productos en este pedido.</p>`;
            }

            html += `
                </div>
                <div class="order-summary">
                    <span class="total-label">Total del pedido</span>
                    <span class="total-amount">$${parseFloat(order.total).toFixed(2)}</span>
                </div>`;

            detailsDiv.innerHTML = html;
        })
        .catch(() => {
            subtitle.textContent = '';
            detailsDiv.innerHTML = '<p class="text-danger mb-0"><i class="fas fa-exclamation-circle me-1"></i> No se pudo cargar el detalle del pedido.</p>';
        });
}

function closeOrderModal() {
    closeModal('orderModal');
}

function changeOrderStatus(orderId, selectEl) {
    const newStatus = selectEl.value;
    const prevStatus = selectEl.getAttribute('data-current-status');

    fetch(`/admin/orders/${orderId}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            selectEl.className = 'order-status-select status-' + newStatus;
            selectEl.setAttribute('data-current-status', newStatus);
            showToast('Estado actualizado correctamente');
        } else {
            selectEl.value = prevStatus;
            showToast('No se pudo actualizar el estado', 'error');
        }
    })
    .catch(() => {
        selectEl.value = prevStatus;
        showToast('Error al actualizar el estado', 'error');
    });
}

function showContactModal(contactId) {
    openModal('contactModal');
    const detailsDiv = document.getElementById('contactDetails');
    const subtitle = document.getElementById('contactModalSubtitle');
    subtitle.textContent = '';
    detailsDiv.innerHTML = '<div class="modal-loading"><div class="spinner"></div><span>Cargando contacto...</span></div>';

    fetch(`/admin/contacts/${contactId}`)
        .then(res => res.json())
        .then(contact => {
            subtitle.textContent = contact.phone_number ?? '';
            let html = `
                <div class="detail-grid">
                    <div class="detail-item full">
                        <div class="label">Nombre</div>
                        <div class="value">${contact.name ?? 'Sin nombre'}</div>
                    </div>
                    <div class="detail-item">
                        <div class="label">Teléfono</div>
                        <div class="value">${contact.phone_number ?? '—'}</div>
                    </div>
                    <div class="detail-item">
                        <div class="label">Email</div>
                        <div class="value">${contact.email ?? '—'}</div>
                    </div>
                    <div class="detail-item full">
                        <div class="label">Registrado</div>
                        <div class="value">${contact.created_at ? formatDate(contact.created_at) : '—'}</div>
                    </div>
                </div>`;
            if (contact.phone_number) {
                html += `<button type="button" class="btn-order-detail w-100 justify-content-center" onclick="copyPhone('${contact.phone_number}', this)">
                    <i class="fas fa-copy"></i> Copiar teléfono
                </button>`;
            }
            detailsDiv.innerHTML = html;
        })
        .catch(() => {
            detailsDiv.innerHTML = '<p class="text-danger mb-0">No se pudo cargar el contacto.</p>';
        });
}

function closeContactModal() {
    closeModal('contactModal');
}

document.getElementById('orders-search')?.addEventListener('input', function() {
    const q = this.value.trim().toLowerCase();
    document.querySelectorAll('#orders-table tbody tr[id^="order-row-"]').forEach(row => {
        const text = row.getAttribute('data-search') || '';
        row.style.display = !q || text.includes(q) ? '' : 'none';
    });
});

document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
        if (e.target === overlay) {
            overlay.classList.remove('is-open');
            document.body.style.overflow = '';
        }
    });
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeOrderModal();
        closeContactModal();
    }
});
</script>
@endsection
