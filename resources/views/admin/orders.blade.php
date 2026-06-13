@extends('admin.layouts.app')

@section('header', 'Pedidos')

@section('content')
@php
    use App\Services\OrderAdminService;

    $statusLabels = [
        'pending' => 'Pendiente',
        'confirmed' => 'Confirmado',
        'completed' => 'Completado',
        'cancelled' => 'Cancelado',
        'payment_pending' => 'Pago pendiente',
        'paid' => 'Pagado',
    ];
    $statusOptions = ['pending', 'confirmed', 'payment_pending', 'paid', 'completed', 'cancelled'];
    $invoiceLabels = OrderAdminService::INVOICE_STATUSES;
    $canUpdate = auth()->user()?->hasPermission('orders.update') ?? false;
@endphp

<style>
    .orders-page { max-width: 1140px; margin: 0 auto; }
    .orders-top { margin-bottom: .85rem; }
    .orders-top h2 { margin: 0 0 .3rem; font-size: 1.35rem; font-weight: 800; color: #0f172a; }
    .orders-top .lead { margin: 0; font-size: .875rem; color: #64748b; }

    .orders-priority {
        display: grid; grid-template-columns: repeat(5, 1fr);
        gap: .65rem; margin-bottom: .85rem;
    }
    @media (max-width: 900px) { .orders-priority { grid-template-columns: repeat(2, 1fr); } }
    .prio-card {
        background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
        padding: .85rem 1rem; box-shadow: 0 1px 3px rgba(15,23,42,.04);
    }
    .prio-card .lbl { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #64748b; }
    .prio-card .val { font-size: 1.2rem; font-weight: 800; color: #0f172a; margin-top: .15rem; }
    .prio-card.urgent { border-color: #fde68a; background: linear-gradient(180deg, #fffbeb, #fff); }
    .prio-card.urgent .val { color: #b45309; }
    .prio-card.accent .val { color: #047857; }

    .orders-toolbar {
        background: #fff; border: 1px solid #e5e7eb; border-radius: 14px;
        padding: .85rem 1rem; margin-bottom: .85rem;
        display: flex; flex-wrap: wrap; gap: .75rem; align-items: flex-end;
    }
    .orders-export-form {
        display: flex; flex-wrap: wrap; gap: .5rem; align-items: flex-end; margin-left: auto;
    }
    .orders-export-form .field label {
        display: block; font-size: .65rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .03em; color: #64748b; margin-bottom: .2rem;
    }
    .orders-export-form .field input,
    .orders-export-form .field select {
        border: 1px solid #e2e8f0; border-radius: 8px; padding: .4rem .55rem;
        font-size: .8rem; min-width: 0;
    }
    .o-btn.export {
        background: #0f766e; border-color: #0f766e; color: #fff !important;
    }
    .o-btn.export:hover { background: #115e59; }
    .orders-search { position: relative; flex: 1; min-width: 200px; max-width: 340px; }
    .orders-search i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: .85rem; }
    .orders-search input {
        width: 100%; border: 1px solid #e2e8f0; border-radius: 10px;
        padding: .5rem .75rem .5rem 2.1rem; font-size: .875rem;
    }

    .orders-table-wrap {
        background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
        overflow-x: auto; box-shadow: 0 1px 3px rgba(15,23,42,.04);
    }
    .orders-table {
        width: 100%; border-collapse: collapse; font-size: .82rem;
    }
    .orders-table th {
        padding: .55rem .65rem; text-align: left; font-size: .68rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .03em; color: #64748b;
        background: #f8fafc; border-bottom: 1px solid #e5e7eb; white-space: nowrap;
    }
    .orders-table td {
        padding: .5rem .65rem; border-bottom: 1px solid #f1f5f9;
        vertical-align: middle; color: #334155;
    }
    .orders-table tbody tr:hover { background: #fafbfc; }
    .orders-table tbody tr.invoice-pending { background: #fffbeb; }
    .orders-table tbody tr.invoice-pending:hover { background: #fef9c3; }
    .orders-table tbody tr:last-child td { border-bottom: none; }

    .order-cell-name { font-weight: 700; color: #0f172a; white-space: nowrap; }
    .order-cell-id { font-size: .72rem; color: #94a3b8; font-weight: 600; }
    .order-cell-muted { font-size: .78rem; color: #64748b; white-space: nowrap; }
    .order-cell-money { font-weight: 700; color: #0f172a; white-space: nowrap; }

    .order-tags { display: flex; flex-wrap: wrap; gap: .25rem; }
    .o-tag {
        font-size: .62rem; font-weight: 700; padding: .15rem .4rem; border-radius: 999px;
        display: inline-flex; align-items: center; gap: .2rem; white-space: nowrap;
    }
    .o-tag.invoice { background: #fef3c7; color: #92400e; }
    .o-tag.notes { background: #e0e7ff; color: #3730a3; }
    .o-tag.feedback { background: #dcfce7; color: #166534; }
    .o-tag.empty { color: #cbd5e1; }

    .order-status-select {
        appearance: none; border: none; border-radius: 20px;
        padding: .35rem 1.75rem .35rem .75rem; font-size: .75rem; font-weight: 600; cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right .5rem center;
    }
    .order-status-select.status-pending { background-color: #fff8e6; color: #b8860b; }
    .order-status-select.status-confirmed { background-color: #e7f1ff; color: #0d6efd; }
    .order-status-select.status-completed { background-color: #e8f8ef; color: #198754; }
    .order-status-select.status-cancelled { background-color: #fdecea; color: #dc3545; }
    .order-status-select.status-payment_pending { background-color: #fff3e0; color: #e65100; }
    .order-status-select.status-paid { background-color: #ede7f6; color: #6f42c1; }

    .order-row-actions { display: flex; gap: .3rem; align-items: center; justify-content: flex-end; white-space: nowrap; }
    .o-btn {
        display: inline-flex; align-items: center; gap: .3rem; padding: .35rem .55rem;
        border-radius: 8px; font-size: .75rem; font-weight: 600; border: 1px solid #e2e8f0;
        background: #fff; color: #475569; cursor: pointer; text-decoration: none;
    }
    .o-btn.primary { background: linear-gradient(135deg, #128c7e, #075e54); border-color: transparent; color: #fff !important; }

    .orders-empty {
        text-align: center; padding: 3rem; background: #fff; border: 1px dashed #e2e8f0; border-radius: 14px; color: #64748b;
    }

    /* Modal */
    .modal-overlay {
        position: fixed; inset: 0; background: rgba(15,23,42,.45); backdrop-filter: blur(4px);
        z-index: 1050; display: flex; align-items: center; justify-content: center; padding: 1rem;
        opacity: 0; visibility: hidden; transition: opacity .2s, visibility .2s;
    }
    .modal-overlay.is-open { opacity: 1; visibility: visible; }
    .modal-panel {
        background: #fff; border-radius: 16px; width: 100%; max-width: 720px; max-height: 92vh;
        overflow: hidden; display: flex; flex-direction: column;
        box-shadow: 0 20px 50px rgba(0,0,0,.2); transform: translateY(12px); transition: transform .2s;
    }
    .modal-overlay.is-open .modal-panel { transform: translateY(0); }
    .modal-header {
        padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9;
        display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem;
        background: #f8fafc;
    }
    .modal-header h3 { margin: 0; font-size: 1.05rem; font-weight: 700; color: #0f172a; }
    .modal-header .sub { font-size: .78rem; color: #64748b; margin-top: .15rem; }
    .modal-close {
        width: 34px; height: 34px; border: none; background: #e2e8f0; border-radius: 50%;
        cursor: pointer; color: #475569;
    }
    .modal-body { padding: 1rem 1.25rem; overflow-y: auto; flex: 1; }
    .modal-footer {
        padding: .85rem 1.25rem; border-top: 1px solid #f1f5f9;
        display: flex; gap: .5rem; justify-content: flex-end; flex-wrap: wrap; background: #fafbfc;
    }

    .order-section {
        border: 1px solid #e5e7eb; border-radius: 12px; margin-bottom: .85rem; overflow: hidden;
    }
    .order-section-head {
        padding: .65rem .85rem; background: #f8fafc; border-bottom: 1px solid #f1f5f9;
        font-size: .82rem; font-weight: 700; color: #334155;
    }
    .order-section-body { padding: .85rem; }

    .checklist { list-style: none; margin: 0; padding: 0; }
    .checklist li {
        display: flex; gap: .6rem; padding: .5rem 0; border-bottom: 1px solid #f8fafc;
        font-size: .82rem; align-items: flex-start;
    }
    .checklist li:last-child { border-bottom: none; }
    .checklist .chk {
        width: 20px; height: 20px; border-radius: 6px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: .65rem;
    }
    .checklist .chk.done { background: #dcfce7; color: #15803d; }
    .checklist .chk.pending { background: #f1f5f9; color: #94a3b8; }

    .note-item { padding: .55rem 0; border-bottom: 1px solid #f1f5f9; font-size: .82rem; }
    .note-item:last-child { border-bottom: none; }
    .note-meta { font-size: .72rem; color: #94a3b8; margin-bottom: .15rem; }
    .note-meta strong { color: #475569; }

    .products-table { width: 100%; font-size: .82rem; border-collapse: collapse; }
    .products-table th, .products-table td { padding: .45rem .5rem; border-bottom: 1px solid #f1f5f9; }
    .products-table th { font-size: .68rem; text-transform: uppercase; color: #64748b; text-align: left; }

    .toast-orders {
        position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 1100;
        padding: .75rem 1.15rem; border-radius: 10px; color: #fff; font-size: .875rem;
        opacity: 0; transform: translateY(8px); transition: .25s; pointer-events: none;
    }
    .toast-orders.show { opacity: 1; transform: translateY(0); }
    .toast-orders.success { background: #15803d; }
    .toast-orders.error { background: #dc2626; }

    .modal-loading { text-align: center; padding: 2rem; color: #94a3b8; }
    .spinner {
        width: 32px; height: 32px; border: 3px solid #e2e8f0; border-top-color: #128c7e;
        border-radius: 50%; animation: spin .7s linear infinite; margin: 0 auto .65rem;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>

<div class="orders-page">
    <div class="orders-top">
        <h2><i class="fas fa-shopping-bag me-1 text-success"></i> Pedidos</h2>
        <p class="lead">Estado, facturación, observaciones internas y seguimiento con el cliente.</p>
    </div>

    <div class="orders-priority">
        <div class="prio-card urgent">
            <div class="lbl">Facturas pendientes</div>
            <div class="val">{{ $stats['invoice_pending'] ?? 0 }}</div>
        </div>
        <div class="prio-card">
            <div class="lbl">Pendientes</div>
            <div class="val">{{ $stats['pending'] ?? 0 }}</div>
        </div>
        <div class="prio-card">
            <div class="lbl">Confirmados</div>
            <div class="val">{{ $stats['confirmed'] ?? 0 }}</div>
        </div>
        <div class="prio-card">
            <div class="lbl">Completados</div>
            <div class="val">{{ $stats['completed'] ?? 0 }}</div>
        </div>
        <div class="prio-card accent">
            <div class="lbl">Ingresos</div>
            <div class="val">${{ number_format($stats['revenue'] ?? 0, 0) }}</div>
        </div>
    </div>

    <div class="orders-toolbar">
        <div class="orders-search">
            <i class="fas fa-search"></i>
            <input type="text" id="orders-search" placeholder="Buscar por cliente o teléfono..." autocomplete="off">
        </div>
        <span class="text-muted small">{{ $orders->total() }} pedido(s)</span>

        <form class="orders-export-form" method="get" action="{{ route('admin.orders.export') }}" id="orders-export-form">
            <div class="field">
                <label for="export-status">Estado</label>
                <select name="status" id="export-status">
                    <option value="">Todos</option>
                    @foreach($statusLabels as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="export-from">Desde</label>
                <input type="date" name="date_from" id="export-from">
            </div>
            <div class="field">
                <label for="export-to">Hasta</label>
                <input type="date" name="date_to" id="export-to">
            </div>
            <input type="hidden" name="q" id="export-q" value="">
            <button type="submit" class="o-btn export">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </button>
        </form>
    </div>

    <div class="orders-table-wrap" id="orders-list">
        @if($orders->isEmpty())
            <div class="orders-empty">
                <i class="fas fa-inbox fa-2x mb-2 opacity-50 d-block"></i>
                <p class="mb-0 fw-semibold">No hay pedidos registrados</p>
            </div>
        @else
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Prod.</th>
                        <th>Etiquetas</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        @php
                            $itemsCount = $order->items->count();
                            $invoicePending = $order->requires_invoice && in_array($order->invoice_status, ['requested', 'data_ready'], true);
                            $hasTags = $order->requires_invoice || ($order->internal_notes_count ?? 0) > 0 || ($order->feedback_count ?? 0) > 0;
                        @endphp
                        <tr class="{{ $invoicePending ? 'invoice-pending' : '' }}"
                            id="order-row-{{ $order->id }}"
                            data-search="{{ strtolower(($order->contact->name ?? '') . ' ' . ($order->contact->phone_number ?? '')) }}">
                            <td><span class="order-cell-id">#{{ $order->id }}</span></td>
                            <td><span class="order-cell-name">{{ $order->contact->name ?? 'Cliente' }}</span></td>
                            <td class="order-cell-muted"><i class="fab fa-whatsapp text-success me-1"></i>{{ $order->contact->phone_number ?? '—' }}</td>
                            <td class="order-cell-muted">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="order-cell-money">${{ number_format($order->total, 0) }}</td>
                            <td>{{ $itemsCount }}</td>
                            <td>
                                <div class="order-tags">
                                    @if($order->requires_invoice)
                                        <span class="o-tag invoice"><i class="fas fa-file-invoice"></i>{{ $invoiceLabels[$order->invoice_status] ?? 'Factura' }}</span>
                                    @endif
                                    @if(($order->internal_notes_count ?? 0) > 0)
                                        <span class="o-tag notes"><i class="fas fa-sticky-note"></i>{{ $order->internal_notes_count }}</span>
                                    @endif
                                    @if(($order->feedback_count ?? 0) > 0)
                                        <span class="o-tag feedback"><i class="fas fa-comment"></i>{{ $order->feedback_count }}</span>
                                    @endif
                                    @unless($hasTags)
                                        <span class="o-tag empty">—</span>
                                    @endunless
                                </div>
                            </td>
                            <td>
                                @if($canUpdate)
                                    <select class="order-status-select status-{{ $order->status }}"
                                        id="status-select-{{ $order->id }}"
                                        data-current-status="{{ $order->status }}"
                                        onchange="changeOrderStatus({{ $order->id }}, this)"
                                        aria-label="Estado">
                                        @foreach($statusOptions as $status)
                                            <option value="{{ $status }}" @selected($order->status === $status)>{{ $statusLabels[$status] }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <span class="o-tag">{{ $statusLabels[$order->status] ?? $order->status }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="order-row-actions">
                                    @perm('chats.open')
                                        <a href="{{ route('admin.chat', $order->contact_id) }}" class="o-btn" title="Abrir chat"><i class="fas fa-comments"></i></a>
                                    @endperm
                                    <button type="button" class="o-btn primary" onclick="showOrderDetails({{ $order->id }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if($orders->hasPages())
        <div class="mt-3">{{ $orders->links() }}</div>
    @endif
</div>

<div class="modal-overlay" id="orderModal" role="dialog" aria-modal="true">
    <div class="modal-panel">
        <div class="modal-header">
            <div>
                <h3 id="orderModalTitle">Pedido</h3>
                <p class="sub mb-0" id="orderModalSubtitle"></p>
            </div>
            <button type="button" class="modal-close" onclick="closeOrderModal()" aria-label="Cerrar"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="orderDetails">
            <div class="modal-loading"><div class="spinner"></div>Cargando...</div>
        </div>
        <div class="modal-footer" id="orderModalFooter">
            <button type="button" class="o-btn" onclick="closeOrderModal()">Cerrar</button>
        </div>
    </div>
</div>

<div class="toast-orders" id="orders-toast"></div>

<script>
const STATUS_LABELS = @json($statusLabels);
const INVOICE_LABELS = @json($invoiceLabels);
const CHAT_URL_TEMPLATE = @json(url('/admin/chats/__ID__'));
const CAN_UPDATE = @json($canUpdate);
const CSRF = @json(csrf_token());
let currentOrderId = null;

function showToast(msg, type = 'success') {
    const t = document.getElementById('orders-toast');
    t.textContent = msg;
    t.className = 'toast-orders show ' + type;
    clearTimeout(t._t);
    t._t = setTimeout(() => t.classList.remove('show'), 2800);
}

function esc(s) {
    const d = document.createElement('div');
    d.textContent = s ?? '';
    return d.innerHTML;
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleString('es-ES', {
        day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
}

function openModal() {
    document.getElementById('orderModal').classList.add('is-open');
    document.body.style.overflow = 'hidden';
}

function closeOrderModal() {
    document.getElementById('orderModal').classList.remove('is-open');
    document.body.style.overflow = '';
    currentOrderId = null;
}

function renderOrderModal(order) {
    currentOrderId = order.id;
    document.getElementById('orderModalTitle').textContent = 'Pedido #' + order.id;
    document.getElementById('orderModalSubtitle').textContent =
        (order.contact?.name ?? 'Cliente') + ' · ' + formatDate(order.created_at) + ' · $' + parseFloat(order.total).toFixed(2);

    const b = order.billing || {};
    let html = '';

    html += `<div class="order-section"><div class="order-section-head"><i class="fas fa-box me-1"></i> Productos</div><div class="order-section-body">`;
    if (order.items?.length) {
        html += `<table class="products-table"><thead><tr><th>Producto</th><th>Cant.</th><th>Precio</th><th>Subtotal</th></tr></thead><tbody>`;
        order.items.forEach(item => {
            const sub = (parseFloat(item.price) * parseInt(item.quantity)).toFixed(2);
            html += `<tr><td>${esc(item.name)}</td><td>${item.quantity}</td><td>$${parseFloat(item.price).toFixed(2)}</td><td>$${sub}</td></tr>`;
        });
        html += `</tbody></table>`;
    } else {
        html += `<p class="text-muted small mb-0">Sin líneas de producto registradas.</p>`;
    }
    html += `</div></div>`;

    if (order.requires_invoice || CAN_UPDATE) {
        html += `<div class="order-section"><div class="order-section-head"><i class="fas fa-file-invoice me-1 text-warning"></i> Facturación</div><div class="order-section-body">`;
        if (CAN_UPDATE) {
            html += `<form id="invoice-form" onsubmit="saveOrderInvoice(event)">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="requires_invoice" name="requires_invoice" ${order.requires_invoice ? 'checked' : ''}>
                    <label class="form-check-label" for="requires_invoice">Cliente solicita factura</label>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <label class="form-label small">Estado factura</label>
                        <select class="form-select form-select-sm" name="invoice_status" id="invoice_status">
                            ${Object.entries(INVOICE_LABELS).map(([k,v]) => `<option value="${k}" ${order.invoice_status===k?'selected':''}>${esc(v)}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Tipo</label>
                        <select class="form-select form-select-sm" name="billing_type">
                            <option value="cedula" ${b.billing_type==='cedula'?'selected':''}>Cédula</option>
                            <option value="ruc" ${b.billing_type==='ruc'?'selected':''}>RUC</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">${b.billing_type==='ruc'?'RUC':'Cédula'}</label>
                        <input type="text" class="form-control form-control-sm" name="billing_id" value="${esc(b.billing_id || '')}" placeholder="Número">
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Nombre / Razón social</label>
                        <input type="text" class="form-control form-control-sm" name="billing_legal_name" value="${esc(b.billing_legal_name || '')}">
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Dirección fiscal</label>
                        <input type="text" class="form-control form-control-sm" name="address" value="${esc(b.address || '')}">
                    </div>
                </div>
                <p class="text-muted small mb-2"><i class="fas fa-info-circle me-1"></i> Al guardar, los datos fiscales se copian al perfil del cliente.</p>
                <button type="submit" class="o-btn primary btn-sm"><i class="fas fa-save me-1"></i>Guardar facturación</button>
            </form>`;
        } else if (order.requires_invoice) {
            html += `<p class="mb-1"><strong>${esc(order.invoice_status_label)}</strong></p>
                <p class="small text-muted mb-0">${esc(b.billing_type?.toUpperCase())} ${esc(b.billing_id)} · ${esc(b.billing_legal_name)}</p>`;
        }
        if (order.agent_checklist?.length) {
            html += `<ul class="checklist mt-3 pt-2 border-top">`;
            order.agent_checklist.forEach(step => {
                html += `<li>
                    <span class="chk ${step.done?'done':'pending'}"><i class="fas fa-${step.done?'check':'minus'}"></i></span>
                    <div><strong>${esc(step.label)}</strong><br><span class="text-muted">${esc(step.hint)}</span></div>
                </li>`;
            });
            html += `</ul>`;
        }
        html += `</div></div>`;
    }

    html += `<div class="order-section"><div class="order-section-head"><i class="fas fa-sticky-note me-1"></i> Observaciones internas</div><div class="order-section-body">`;
    html += `<div id="internal-notes-list">${renderNotesList(order.notes?.filter(n => n.type === 'internal') || [])}</div>`;
    if (CAN_UPDATE) {
        html += `<form class="mt-2" onsubmit="addOrderNote(event, 'internal')">
            <textarea class="form-control form-control-sm mb-2" name="body" rows="2" placeholder="Nota para el equipo (no la ve el cliente)..." required></textarea>
            <button type="submit" class="o-btn btn-sm"><i class="fas fa-plus me-1"></i>Agregar observación</button>
        </form>`;
    }
    html += `</div></div>`;

    html += `<div class="order-section"><div class="order-section-head"><i class="fas fa-comment-dots me-1 text-success"></i> Feedback con el cliente</div><div class="order-section-body">`;
    html += `<div id="feedback-notes-list">${renderNotesList(order.notes?.filter(n => n.type === 'feedback') || [])}</div>`;
    if (CAN_UPDATE) {
        html += `<form class="mt-2" onsubmit="addOrderNote(event, 'feedback')">
            <textarea class="form-control form-control-sm mb-2" name="body" rows="2" placeholder="Ej: Envié factura PDF por WhatsApp, cliente confirmó recepción..." required></textarea>
            <button type="submit" class="o-btn btn-sm"><i class="fas fa-plus me-1"></i>Registrar feedback</button>
        </form>`;
    }
    html += `</div></div>`;

    document.getElementById('orderDetails').innerHTML = html;

    const footer = document.getElementById('orderModalFooter');
    footer.innerHTML = `<button type="button" class="o-btn" onclick="closeOrderModal()">Cerrar</button>`;
    if (order.contact?.id) {
        footer.innerHTML += `<a href="${CHAT_URL_TEMPLATE.replace('__ID__', order.contact.id)}" class="o-btn primary"><i class="fas fa-comments me-1"></i>Abrir chat</a>`;
    }
}

function renderNotesList(notes) {
    if (!notes.length) return '<p class="text-muted small mb-0">Sin registros aún.</p>';
    return notes.map(n => `<article class="note-item">
        <div class="note-meta"><strong>${esc(n.author)}</strong> · ${formatDate(n.created_at)}</div>
        <div>${esc(n.body).replace(/\n/g, '<br>')}</div>
    </article>`).join('');
}

function showOrderDetails(orderId) {
    openModal();
    document.getElementById('orderDetails').innerHTML = '<div class="modal-loading"><div class="spinner"></div>Cargando...</div>';
    fetch(`/admin/orders/${orderId}/details`)
        .then(r => r.json())
        .then(renderOrderModal)
        .catch(() => {
            document.getElementById('orderDetails').innerHTML = '<p class="text-danger">No se pudo cargar el pedido.</p>';
        });
}

function saveOrderInvoice(e) {
    e.preventDefault();
    if (!currentOrderId) return;
    const fd = new FormData(e.target);
    const payload = {
        requires_invoice: fd.get('requires_invoice') === 'on',
        invoice_status: fd.get('invoice_status'),
        billing_type: fd.get('billing_type'),
        billing_id: fd.get('billing_id'),
        billing_legal_name: fd.get('billing_legal_name'),
        address: fd.get('address'),
        sync_profile: true,
    };
    fetch(`/admin/orders/${currentOrderId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Facturación guardada y perfil actualizado');
            renderOrderModal(data.order);
        } else showToast('Error al guardar', 'error');
    })
    .catch(() => showToast('Error al guardar', 'error'));
}

function addOrderNote(e, type) {
    e.preventDefault();
    if (!currentOrderId) return;
    const body = e.target.body.value.trim();
    if (!body) return;
    fetch(`/admin/orders/${currentOrderId}/notes`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ type, body }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(type === 'feedback' ? 'Feedback registrado' : 'Observación agregada');
            showOrderDetails(currentOrderId);
        } else showToast('Error', 'error');
    })
    .catch(() => showToast('Error', 'error'));
}

function changeOrderStatus(orderId, selectEl) {
    const newStatus = selectEl.value;
    const prev = selectEl.getAttribute('data-current-status');
    fetch(`/admin/orders/${orderId}/status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ status: newStatus }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            selectEl.className = 'order-status-select status-' + newStatus;
            selectEl.setAttribute('data-current-status', newStatus);
            showToast('Estado actualizado');
        } else { selectEl.value = prev; showToast('Error', 'error'); }
    })
    .catch(() => { selectEl.value = prev; showToast('Error', 'error'); });
}

document.getElementById('orders-search')?.addEventListener('input', function() {
    const q = this.value.trim().toLowerCase();
    document.querySelectorAll('[id^="order-row-"]').forEach(row => {
        row.style.display = !q || (row.getAttribute('data-search') || '').includes(q) ? '' : 'none';
    });
});

document.getElementById('orders-export-form')?.addEventListener('submit', function() {
    const q = document.getElementById('orders-search')?.value.trim() || '';
    const hidden = document.getElementById('export-q');
    if (hidden) hidden.value = q;
});

document.getElementById('orderModal')?.addEventListener('click', e => {
    if (e.target.id === 'orderModal') closeOrderModal();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeOrderModal(); });
</script>
@endsection
