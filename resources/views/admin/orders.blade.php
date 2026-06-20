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
    $canBulkCreate = auth()->user()?->hasPermission('bulk_orders.create') ?? false;
    $columnHints = [
        'id' => 'Identificador único del pedido en el sistema. Sirve para buscarlo, exportarlo o referenciarlo en notas internas.',
        'client' => 'Nombre del contacto de WhatsApp vinculado al pedido. Si tiene cédula en su perfil, aparece debajo del nombre.',
        'phone' => 'Número de WhatsApp del cliente. Es el canal usado para confirmaciones, comprobantes y seguimiento.',
        'date' => 'Fecha y hora en que se registró el pedido (hora del servidor).',
        'total' => 'Monto total del pedido: suma de productos, cantidades y ajustes aplicados al momento de la compra.',
        'products' => 'Cantidad de líneas de producto incluidas en el pedido (no es la suma de unidades).',
        'tags' => 'Indicadores rápidos: observaciones internas, feedback con el cliente, espera de confirmación por WhatsApp, comprobante de pago recibido o pendiente.',
        'status' => 'Etapa del pedido: Pendiente (nuevo), Confirmado, Pago pendiente, Pagado, Completado o Cancelado. Puede cambiarse desde aquí si tiene permiso.',
        'actions' => 'Abrir chat con el cliente, ver el detalle completo del pedido o descargar el PDF de la orden.',
    ];
    $sectionHints = [
        'products' => 'Detalle de lo que compró el cliente: productos, cantidades, precios unitarios y subtotales que forman el total del pedido.',
        'payment_proof' => 'Comprobante de pago enviado por WhatsApp (foto o PDF). Aparece automáticamente cuando el cliente lo adjunta tras transferencia o tarjeta.',
        'confirmation' => 'Envía al cliente el PDF del pedido con botones para confirmar, modificar o cancelar. Solo disponible en pedidos pendientes o con pago pendiente.',
        'billing' => 'Datos fiscales para emitir factura. Al guardar, se copian al perfil del cliente para futuros pedidos.',
        'internal_notes' => 'Notas visibles solo para el equipo administrativo. El cliente no las ve en WhatsApp ni en el PDF.',
        'feedback' => 'Registro de comunicaciones y acuerdos con el cliente (envíos, confirmaciones, incidencias). Útil para el historial del pedido.',
    ];
    $fieldHints = [
        'requires_invoice' => 'Actívelo si el cliente pidió factura fiscal. Desbloquea el seguimiento de estado y los datos de facturación.',
        'invoice_status' => 'Progreso de la factura: sin factura, solicitada, datos listos, emitida o entregada al cliente.',
        'billing_type' => 'Tipo de identificación fiscal del cliente: cédula de persona natural o RUC de empresa.',
        'billing_id' => 'Número de cédula o RUC según el tipo seleccionado. Debe coincidir con los datos del SRI.',
        'billing_legal_name' => 'Nombre completo o razón social tal como debe figurar en la factura.',
        'address' => 'Dirección fiscal registrada para la factura electrónica.',
        'confirmation_message' => 'Texto opcional que acompaña el PDF y los botones de confirmación en WhatsApp.',
        'product_name' => 'Nombre del producto o servicio incluido en el pedido.',
        'product_qty' => 'Unidades solicitadas de ese producto.',
        'product_price' => 'Precio unitario al momento de la compra.',
        'product_subtotal' => 'Cantidad × precio unitario para esa línea.',
    ];
@endphp

<style>
    .orders-page { max-width: 1140px; margin: 0 auto; }
    .orders-top { margin-bottom: .85rem; }
    .orders-top h2 { margin: 0 0 .3rem; font-size: 1.35rem; font-weight: 800; color: #0f172a; }
    .orders-top .lead { margin: 0; font-size: .875rem; color: #64748b; }

    .orders-priority {
        display: grid; grid-template-columns: repeat(4, 1fr);
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
    .th-label-row {
        display: inline-flex; align-items: center; gap: .3rem; white-space: nowrap;
    }
    .metric-info-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 16px; height: 16px; padding: 0; border: none; background: transparent;
        color: #94a3b8; cursor: help; border-radius: 50%; font-size: .72rem;
        line-height: 1; vertical-align: middle;
    }
    .metric-info-btn:hover { color: #128c7e; }
    .orders-table td {
        padding: .5rem .65rem; border-bottom: 1px solid #f1f5f9;
        vertical-align: middle; color: #334155;
    }
    .orders-table tbody tr:hover { background: #fafbfc; }
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
    .o-tag.notes { background: #e0e7ff; color: #3730a3; }
    .o-tag.feedback { background: #dcfce7; color: #166534; }
    .o-tag.confirm { background: #fef3c7; color: #92400e; }
    .o-tag.proof-ok { background: #dbeafe; color: #1d4ed8; }
    .o-tag.proof-wait { background: #ffedd5; color: #c2410c; }
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
    .modal-body {
        padding: 1rem 1.15rem 1.25rem; overflow-y: auto; flex: 1;
        background: linear-gradient(180deg, #e8edf3 0%, #eef2f7 100%);
    }
    .modal-footer {
        padding: .85rem 1.25rem; border-top: 1px solid #e2e8f0;
        display: flex; gap: .5rem; justify-content: flex-end; flex-wrap: wrap;
        background: #fff; box-shadow: 0 -4px 12px rgba(15, 23, 42, .04);
    }

    .order-sections-stack { display: flex; flex-direction: column; gap: .9rem; }

    .order-section {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
        overflow: hidden; box-shadow: 0 2px 10px rgba(15, 23, 42, .06);
    }
    .order-section[data-theme="products"] { border-left: 4px solid #64748b; }
    .order-section[data-theme="payment"] { border-left: 4px solid #2563eb; }
    .order-section[data-theme="confirmation"] { border-left: 4px solid #16a34a; }
    .order-section[data-theme="billing"] { border-left: 4px solid #d97706; }
    .order-section[data-theme="notes"] { border-left: 4px solid #6366f1; }
    .order-section[data-theme="feedback"] { border-left: 4px solid #0d9488; }

    .order-section-head {
        padding: .75rem 1rem; border-bottom: 1px solid #f1f5f9;
        display: flex; align-items: center; justify-content: space-between; gap: .5rem;
    }
    .order-section[data-theme="products"] .order-section-head { background: linear-gradient(90deg, #f8fafc 0%, #fff 100%); }
    .order-section[data-theme="payment"] .order-section-head { background: linear-gradient(90deg, #eff6ff 0%, #fff 100%); }
    .order-section[data-theme="confirmation"] .order-section-head { background: linear-gradient(90deg, #ecfdf5 0%, #fff 100%); }
    .order-section[data-theme="billing"] .order-section-head { background: linear-gradient(90deg, #fffbeb 0%, #fff 100%); }
    .order-section[data-theme="notes"] .order-section-head { background: linear-gradient(90deg, #eef2ff 0%, #fff 100%); }
    .order-section[data-theme="feedback"] .order-section-head { background: linear-gradient(90deg, #f0fdfa 0%, #fff 100%); }

    .order-section-head-main {
        display: flex; align-items: center; gap: .55rem;
        font-size: .84rem; font-weight: 700; color: #0f172a; min-width: 0;
    }
    .order-section-icon {
        width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: .88rem;
    }
    .order-section[data-theme="products"] .order-section-icon { background: #e2e8f0; color: #475569; }
    .order-section[data-theme="payment"] .order-section-icon { background: #dbeafe; color: #1d4ed8; }
    .order-section[data-theme="confirmation"] .order-section-icon { background: #dcfce7; color: #15803d; }
    .order-section[data-theme="billing"] .order-section-icon { background: #fef3c7; color: #b45309; }
    .order-section[data-theme="notes"] .order-section-icon { background: #e0e7ff; color: #4338ca; }
    .order-section[data-theme="feedback"] .order-section-icon { background: #ccfbf1; color: #0f766e; }

    .order-section-body { padding: 1rem; background: #fff; }
    .order-section-body.flush { padding: 0; }

    .section-info-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 18px; height: 18px; padding: 0; border: none; background: transparent;
        color: #94a3b8; cursor: help; border-radius: 50%; font-size: .78rem;
        line-height: 1; flex-shrink: 0; margin-left: .15rem;
    }
    .section-info-btn:hover { color: #128c7e; }

    .field-label-row {
        display: inline-flex; align-items: center; gap: .25rem; flex-wrap: wrap;
    }
    .field-info-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 15px; height: 15px; padding: 0; border: none; background: transparent;
        color: #94a3b8; cursor: help; border-radius: 50%; font-size: .68rem;
        line-height: 1; vertical-align: middle;
    }
    .field-info-btn:hover { color: #128c7e; }

    .order-callout {
        padding: .65rem .75rem; border-radius: 10px; font-size: .8rem; margin-bottom: .75rem;
        border: 1px solid transparent;
    }
    .order-callout.warning { background: #fffbeb; border-color: #fde68a; color: #92400e; }
    .order-callout.info { background: #f8fafc; border-color: #e2e8f0; color: #64748b; }
    .order-callout.sync { background: #eff6ff; border-color: #bfdbfe; color: #1e40af; }

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
    .products-table .th-label-row {
        display: inline-flex; align-items: center; gap: .25rem; white-space: nowrap;
    }

    /* Comprobante de pago */
    .payment-proof-card {
        border-radius: 14px;
        overflow: hidden;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfeff 55%, #eff6ff 100%);
        border: 1px solid #bbf7d0;
        box-shadow: 0 4px 18px rgba(15, 118, 110, .08);
    }
    .payment-proof-card.is-awaiting {
        background: linear-gradient(135deg, #fffbeb 0%, #fff7ed 100%);
        border-color: #fed7aa;
        box-shadow: 0 4px 18px rgba(234, 88, 12, .06);
    }
    .payment-proof-top {
        display: flex; align-items: flex-start; justify-content: space-between; gap: .75rem;
        padding: 1rem 1.1rem .75rem;
    }
    .payment-proof-title-wrap { display: flex; align-items: center; gap: .75rem; min-width: 0; }
    .payment-proof-icon {
        width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #059669, #0d9488);
        color: #fff; font-size: 1.1rem;
        box-shadow: 0 6px 16px rgba(5, 150, 105, .25);
    }
    .payment-proof-card.is-awaiting .payment-proof-icon {
        background: linear-gradient(135deg, #ea580c, #f59e0b);
        box-shadow: 0 6px 16px rgba(234, 88, 12, .2);
    }
    .payment-proof-title { margin: 0; font-size: .95rem; font-weight: 800; color: #0f172a; }
    .payment-proof-sub { margin: .15rem 0 0; font-size: .75rem; color: #64748b; }
    .payment-proof-badge {
        font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em;
        padding: .35rem .65rem; border-radius: 999px; white-space: nowrap;
    }
    .payment-proof-badge.ok { background: #dcfce7; color: #166534; }
    .payment-proof-badge.wait { background: #ffedd5; color: #c2410c; }
    .payment-proof-meta {
        display: flex; flex-wrap: wrap; gap: .5rem 1rem;
        padding: 0 1.1rem .85rem; font-size: .78rem; color: #475569;
    }
    .payment-proof-meta span { display: inline-flex; align-items: center; gap: .35rem; }
    .payment-proof-meta i { color: #0f766e; font-size: .72rem; }
    .payment-proof-preview {
        margin: 0 1.1rem 1rem;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.8);
    }
    .payment-proof-preview img {
        display: block; width: 100%; max-height: 320px; object-fit: contain;
        background: #0f172a; cursor: zoom-in;
    }
    .payment-proof-doc {
        display: flex; align-items: center; gap: .85rem;
        padding: 1rem 1.1rem;
    }
    .payment-proof-doc-icon {
        width: 52px; height: 52px; border-radius: 12px; flex-shrink: 0;
        background: linear-gradient(135deg, #ef4444, #f97316);
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-size: .72rem; font-weight: 800; letter-spacing: .03em;
    }
    .payment-proof-doc-name {
        font-size: .88rem; font-weight: 700; color: #0f172a;
        word-break: break-word;
    }
    .payment-proof-doc-hint { font-size: .75rem; color: #64748b; margin-top: .15rem; }
    .payment-proof-actions {
        display: flex; flex-wrap: wrap; gap: .5rem;
        padding: 0 1.1rem 1rem;
    }
    .payment-proof-btn {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .5rem .85rem; border-radius: 10px; font-size: .78rem; font-weight: 700;
        text-decoration: none; border: 1px solid transparent; cursor: pointer;
    }
    .payment-proof-btn.primary {
        background: linear-gradient(135deg, #128c7e, #075e54); color: #fff !important;
    }
    .payment-proof-btn.ghost {
        background: #fff; border-color: #cbd5e1; color: #334155 !important;
    }
    .payment-proof-empty {
        padding: 1rem 1.1rem 1.1rem; font-size: .82rem; color: #92400e;
    }
    .payment-proof-empty i { margin-right: .35rem; }

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
        <p class="lead">Estado, observaciones internas y seguimiento con el cliente.</p>
    </div>

    <div class="orders-priority">
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
            <input type="text" id="orders-search" placeholder="Buscar por cliente, teléfono o cédula..." autocomplete="off">
        </div>
        @if($canBulkCreate)
            <a href="{{ route('admin.orders.bulk.create') }}" class="o-btn primary">
                <i class="fas fa-plus"></i> Nuevo pedido
            </a>
        @endif
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
                        <th>
                            <span class="th-label-row">
                                #
                                <button type="button" class="metric-info-btn" title="{{ $columnHints['id'] }}" aria-label="Qué significa el número de pedido">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </span>
                        </th>
                        <th>
                            <span class="th-label-row">
                                Cliente
                                <button type="button" class="metric-info-btn" title="{{ $columnHints['client'] }}" aria-label="Qué significa Cliente">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </span>
                        </th>
                        <th>
                            <span class="th-label-row">
                                Teléfono
                                <button type="button" class="metric-info-btn" title="{{ $columnHints['phone'] }}" aria-label="Qué significa Teléfono">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </span>
                        </th>
                        <th>
                            <span class="th-label-row">
                                Fecha
                                <button type="button" class="metric-info-btn" title="{{ $columnHints['date'] }}" aria-label="Qué significa Fecha">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </span>
                        </th>
                        <th>
                            <span class="th-label-row">
                                Total
                                <button type="button" class="metric-info-btn" title="{{ $columnHints['total'] }}" aria-label="Qué significa Total">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </span>
                        </th>
                        <th>
                            <span class="th-label-row">
                                Prod.
                                <button type="button" class="metric-info-btn" title="{{ $columnHints['products'] }}" aria-label="Qué significa Prod.">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </span>
                        </th>
                        <th>
                            <span class="th-label-row">
                                Etiquetas
                                <button type="button" class="metric-info-btn" title="{{ $columnHints['tags'] }}" aria-label="Qué significan las etiquetas">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </span>
                        </th>
                        <th>
                            <span class="th-label-row">
                                Estado
                                <button type="button" class="metric-info-btn" title="{{ $columnHints['status'] }}" aria-label="Qué significa Estado">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </span>
                        </th>
                        <th class="text-end">
                            <span class="th-label-row">
                                Acciones
                                <button type="button" class="metric-info-btn" title="{{ $columnHints['actions'] }}" aria-label="Qué significa Acciones">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        @php
                            $itemsCount = $order->items->count();
                            $contact = $order->contact;
                            $clientNationalId = $contact?->national_id
                                ?: (($contact?->billing_type === 'cedula' && $contact?->billing_id) ? $contact->billing_id : null);
                            $hasTags = ($order->internal_notes_count ?? 0) > 0
                                || ($order->feedback_count ?? 0) > 0
                                || (($order->metadata['awaiting_client_confirmation'] ?? false) && $order->status === 'pending')
                                || $order->hasPaymentProof()
                                || $order->isAwaitingPaymentProof();
                        @endphp
                        <tr id="order-row-{{ $order->id }}"
                            data-search="{{ strtolower(trim(($contact->name ?? '') . ' ' . ($contact->phone_number ?? '') . ' ' . ($clientNationalId ?? ''))) }}">
                            <td><span class="order-cell-id">#{{ $order->id }}</span></td>
                            <td>
                                <span class="order-cell-name">{{ $contact->name ?? 'Cliente' }}</span>
                                @if($clientNationalId)
                                    <div class="order-cell-muted"><i class="fas fa-id-card me-1"></i>{{ $clientNationalId }}</div>
                                @endif
                            </td>
                            <td class="order-cell-muted"><i class="fab fa-whatsapp text-success me-1"></i>{{ $contact->phone_number ?? '—' }}</td>
                            <td class="order-cell-muted">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="order-cell-money">${{ number_format($order->total, 0) }}</td>
                            <td>{{ $itemsCount }}</td>
                            <td>
                                <div class="order-tags">
                                    @if(($order->internal_notes_count ?? 0) > 0)
                                        <span class="o-tag notes"><i class="fas fa-sticky-note"></i>{{ $order->internal_notes_count }}</span>
                                    @endif
                                    @if(($order->feedback_count ?? 0) > 0)
                                        <span class="o-tag feedback"><i class="fas fa-comment"></i>{{ $order->feedback_count }}</span>
                                    @endif
                                    @if(($order->metadata['awaiting_client_confirmation'] ?? false) && $order->status === 'pending')
                                        <span class="o-tag confirm"><i class="fas fa-clock"></i> Espera cliente</span>
                                    @endif
                                    @if($order->hasPaymentProof())
                                        <span class="o-tag proof-ok"><i class="fas fa-receipt"></i> Comprobante</span>
                                    @elseif($order->isAwaitingPaymentProof())
                                        <span class="o-tag proof-wait"><i class="fas fa-hourglass-half"></i> Sin comprobante</span>
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
                                    <a href="{{ route('admin.orders.pdf', $order->id) }}" class="o-btn" title="Descargar PDF" target="_blank" rel="noopener">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
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
const SECTION_HINTS = @json($sectionHints);
const FIELD_HINTS = @json($fieldHints);
const CHAT_URL_TEMPLATE = @json(url('/admin/chats/__ID__'));
const CAN_UPDATE = @json($canUpdate);
const CSRF = @json(csrf_token());
let currentOrderId = null;

function infoBtn(hint, ariaLabel, btnClass = 'section-info-btn') {
    if (!hint) return '';
    return `<button type="button" class="${btnClass}" title="${esc(hint)}" aria-label="${esc(ariaLabel || hint)}"><i class="fas fa-info-circle"></i></button>`;
}

function sectionHead(title, icon, theme, hintKey) {
    return `<div class="order-section-head">
        <div class="order-section-head-main">
            <span class="order-section-icon"><i class="${icon}"></i></span>
            <span>${esc(title)}</span>
            ${infoBtn(SECTION_HINTS[hintKey] || '', 'Información: ' + title)}
        </div>
    </div>`;
}

function fieldLabel(text, hintKey) {
    return `<span class="field-label-row">${esc(text)} ${infoBtn(FIELD_HINTS[hintKey] || '', text, 'field-info-btn')}</span>`;
}

function productTh(label, hintKey) {
    return `<span class="th-label-row">${esc(label)} ${infoBtn(FIELD_HINTS[hintKey] || '', label, 'field-info-btn')}</span>`;
}

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
    let html = '<div class="order-sections-stack">';

    html += `<div class="order-section" data-theme="products">${sectionHead('Productos', 'fas fa-box', 'products', 'products')}<div class="order-section-body">`;
    if (order.items?.length) {
        html += `<table class="products-table"><thead><tr>
            <th>${productTh('Producto', 'product_name')}</th>
            <th>${productTh('Cant.', 'product_qty')}</th>
            <th>${productTh('Precio', 'product_price')}</th>
            <th>${productTh('Subtotal', 'product_subtotal')}</th>
        </tr></thead><tbody>`;
        order.items.forEach(item => {
            const sub = (parseFloat(item.price) * parseInt(item.quantity)).toFixed(2);
            html += `<tr><td>${esc(item.name)}</td><td>${item.quantity}</td><td>$${parseFloat(item.price).toFixed(2)}</td><td>$${sub}</td></tr>`;
        });
        html += `</tbody></table>`;
    } else {
        html += `<p class="text-muted small mb-0">Sin líneas de producto registradas.</p>`;
    }
    html += `</div></div>`;

    html += renderPaymentProofSection(order);

    if (CAN_UPDATE && ['pending', 'payment_pending'].includes(order.status)) {
        html += `<div class="order-section" data-theme="confirmation">${sectionHead('Confirmación del cliente', 'fab fa-whatsapp', 'confirmation', 'confirmation')}<div class="order-section-body">`;
        if (order.awaiting_client_confirmation) {
            html += `<div class="order-callout warning"><i class="fas fa-clock me-1"></i> Esperando respuesta del cliente (PDF y botones enviados).</div>`;
        } else {
            html += `<div class="order-callout info">Envía el PDF de la orden con botones para confirmar, modificar o cancelar.</div>`;
        }
        html += `<label class="form-label small d-block mb-1">${fieldLabel('Mensaje opcional', 'confirmation_message')}</label>`;
        html += `<textarea class="form-control form-control-sm mb-2" id="confirmationMessage" rows="2" placeholder="Texto que acompaña el PDF en WhatsApp…"></textarea>`;
        html += `<button type="button" class="o-btn primary" onclick="sendOrderConfirmation()"><i class="fab fa-whatsapp me-1"></i>Enviar confirmación por WhatsApp</button>`;
        html += `</div></div>`;
    }

    if (order.requires_invoice || CAN_UPDATE) {
        html += `<div class="order-section" data-theme="billing">${sectionHead('Facturación', 'fas fa-file-invoice', 'billing', 'billing')}<div class="order-section-body">`;
        if (CAN_UPDATE) {
            html += `<form id="invoice-form" onsubmit="saveOrderInvoice(event)">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="requires_invoice" name="requires_invoice" ${order.requires_invoice ? 'checked' : ''}>
                    <label class="form-check-label" for="requires_invoice">${fieldLabel('Cliente solicita factura', 'requires_invoice')}</label>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <label class="form-label small d-block">${fieldLabel('Estado factura', 'invoice_status')}</label>
                        <select class="form-select form-select-sm" name="invoice_status" id="invoice_status">
                            ${Object.entries(INVOICE_LABELS).map(([k,v]) => `<option value="${k}" ${order.invoice_status===k?'selected':''}>${esc(v)}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small d-block">${fieldLabel('Tipo', 'billing_type')}</label>
                        <select class="form-select form-select-sm" name="billing_type">
                            <option value="cedula" ${b.billing_type==='cedula'?'selected':''}>Cédula</option>
                            <option value="ruc" ${b.billing_type==='ruc'?'selected':''}>RUC</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small d-block">${fieldLabel(b.billing_type==='ruc'?'RUC':'Cédula', 'billing_id')}</label>
                        <input type="text" class="form-control form-control-sm" name="billing_id" value="${esc(b.billing_id || '')}" placeholder="Número">
                    </div>
                    <div class="col-12">
                        <label class="form-label small d-block">${fieldLabel('Nombre / Razón social', 'billing_legal_name')}</label>
                        <input type="text" class="form-control form-control-sm" name="billing_legal_name" value="${esc(b.billing_legal_name || '')}">
                    </div>
                    <div class="col-12">
                        <label class="form-label small d-block">${fieldLabel('Dirección fiscal', 'address')}</label>
                        <input type="text" class="form-control form-control-sm" name="address" value="${esc(b.address || '')}">
                    </div>
                </div>
                <div class="order-callout sync mb-2"><i class="fas fa-sync-alt me-1"></i> Al guardar, los datos fiscales se copian al perfil del cliente.</div>
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

    html += `<div class="order-section" data-theme="notes">${sectionHead('Observaciones internas', 'fas fa-sticky-note', 'notes', 'internal_notes')}<div class="order-section-body">`;
    html += `<div id="internal-notes-list">${renderNotesList(order.notes?.filter(n => n.type === 'internal') || [])}</div>`;
    if (CAN_UPDATE) {
        html += `<form class="mt-3 pt-2 border-top" onsubmit="addOrderNote(event, 'internal')">
            <textarea class="form-control form-control-sm mb-2" name="body" rows="2" placeholder="Nota para el equipo (no la ve el cliente)..." required></textarea>
            <button type="submit" class="o-btn btn-sm"><i class="fas fa-plus me-1"></i>Agregar observación</button>
        </form>`;
    }
    html += `</div></div>`;

    html += `<div class="order-section" data-theme="feedback">${sectionHead('Feedback con el cliente', 'fas fa-comment-dots', 'feedback', 'feedback')}<div class="order-section-body">`;
    html += `<div id="feedback-notes-list">${renderNotesList(order.notes?.filter(n => n.type === 'feedback') || [])}</div>`;
    if (CAN_UPDATE) {
        html += `<form class="mt-3 pt-2 border-top" onsubmit="addOrderNote(event, 'feedback')">
            <textarea class="form-control form-control-sm mb-2" name="body" rows="2" placeholder="Ej: Envié factura PDF por WhatsApp, cliente confirmó recepción..." required></textarea>
            <button type="submit" class="o-btn btn-sm"><i class="fas fa-plus me-1"></i>Registrar feedback</button>
        </form>`;
    }
    html += `</div></div>`;

    html += '</div>';

    document.getElementById('orderDetails').innerHTML = html;

    const footer = document.getElementById('orderModalFooter');
    footer.innerHTML = `<button type="button" class="o-btn" onclick="closeOrderModal()">Cerrar</button>`;
    footer.innerHTML += `<a href="/admin/orders/${order.id}/pdf" class="o-btn primary" target="_blank" rel="noopener"><i class="fas fa-file-pdf me-1"></i>Descargar PDF</a>`;
    if (order.contact?.id) {
        footer.innerHTML += `<a href="${CHAT_URL_TEMPLATE.replace('__ID__', order.contact.id)}" class="o-btn primary"><i class="fas fa-comments me-1"></i>Abrir chat</a>`;
    }
}

function renderPaymentProofSection(order) {
    const payment = order.payment;
    if (!payment?.visible) return '';

    const state = payment.state;
    const isAwaiting = state === 'awaiting';
    const isSubmitted = state === 'submitted';
    const cardClass = isAwaiting ? 'payment-proof-card is-awaiting' : 'payment-proof-card';
    const badgeClass = isSubmitted ? 'ok' : 'wait';
    const badgeText = isSubmitted ? 'Recibido' : (isAwaiting ? 'Pendiente' : 'Sin envío');

    let html = `<div class="order-section" data-theme="payment">${sectionHead('Comprobante de pago del cliente', 'fas fa-receipt', 'payment', 'payment_proof')}<div class="order-section-body flush">`;
    html += `<div class="${cardClass}">`;
    html += `<div class="payment-proof-top">
        <div class="payment-proof-title-wrap">
            <div class="payment-proof-icon"><i class="fas fa-${isSubmitted ? 'file-circle-check' : 'file-invoice-dollar'}"></i></div>
            <div>
                <h4 class="payment-proof-title">${isSubmitted ? 'Comprobante enviado por WhatsApp' : 'Esperando comprobante del cliente'}</h4>
                <p class="payment-proof-sub">${esc(payment.method_label)} · ${esc(payment.status_label)}</p>
            </div>
        </div>
        <span class="payment-proof-badge ${badgeClass}">${badgeText}</span>
    </div>`;

    html += `<div class="payment-proof-meta">`;
    html += `<span><i class="fas fa-credit-card"></i> Método: <strong>${esc(payment.method_label)}</strong></span>`;
    if (isSubmitted && payment.proof?.received_at) {
        html += `<span><i class="fas fa-clock"></i> Recibido: <strong>${formatDate(payment.proof.received_at)}</strong></span>`;
    }
    html += `</div>`;

    if (isSubmitted && payment.proof?.media_url) {
        const url = esc(payment.proof.media_url);
        const filename = esc(payment.proof.filename || 'comprobante');
        if (payment.proof.type === 'image') {
            html += `<div class="payment-proof-preview">
                <img src="${url}" alt="Comprobante de pago" onclick="window.open('${url}', '_blank')" loading="lazy">
            </div>`;
        } else {
            const ext = filename.split('.').pop().toUpperCase().slice(0, 4) || 'PDF';
            html += `<div class="payment-proof-preview">
                <div class="payment-proof-doc">
                    <div class="payment-proof-doc-icon">${ext}</div>
                    <div>
                        <div class="payment-proof-doc-name">${filename}</div>
                        <div class="payment-proof-doc-hint">Documento enviado por el cliente desde WhatsApp</div>
                    </div>
                </div>
            </div>`;
        }
        html += `<div class="payment-proof-actions">
            <a href="${url}" target="_blank" rel="noopener" class="payment-proof-btn primary"><i class="fas fa-expand"></i> Ver en tamaño completo</a>
            <a href="${url}" download class="payment-proof-btn ghost"><i class="fas fa-download"></i> Descargar</a>
        </div>`;
    } else if (isAwaiting) {
        html += `<div class="payment-proof-empty">
            <i class="fas fa-info-circle"></i>
            El cliente aún no ha enviado foto o PDF del comprobante. Aparecerá aquí automáticamente cuando lo mande por WhatsApp.
        </div>`;
    }

    html += `</div></div></div>`;
    return html;
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

function sendOrderConfirmation() {
    if (!currentOrderId) return;
    const message = document.getElementById('confirmationMessage')?.value?.trim() || null;
    fetch(`/admin/orders/${currentOrderId}/send-confirmation`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ message }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Confirmación enviada');
            if (data.order) renderOrderModal(data.order);
        } else {
            showToast(data.message || 'No se pudo enviar', 'error');
        }
    })
    .catch(() => showToast('Error al enviar confirmación', 'error'));
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
