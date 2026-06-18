@php
    $isAgent = ($mode ?? 'public') === 'agent';
    $contactName = $contactName ?? 'Cliente';
    $headerTitle = $headerTitle ?? 'Armar pedido';
    $headerSubtitle = $headerSubtitle ?? (
        $isAgent
            ? 'Selecciona un cliente, agrega productos y registra el pedido desde el panel.'
            : "Hola, {$contactName}. Agrega productos, cantidades y notas. Al enviar, recibirás tu número de pedido por WhatsApp."
    );
    $successWhatsappHint = $successWhatsappHint ?? 'Revisa WhatsApp: recibirás el PDF y los botones para confirmar, modificar o cancelar.';
@endphp

<style>
    .bulk-order-app {
        --wa: #128c7e;
        --wa-dark: #075e54;
        --bg: {{ $isAgent ? '#f8fafc' : '#ece5dd' }};
        --card: #fff;
        --muted: #667781;
        --border: #e9edef;
    }
    .bulk-order-app * { box-sizing: border-box; }
    .bulk-order-app {
        font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
        color: #111;
    }
    @if(!$isAgent)
    .bulk-order-app { min-height: 100vh; padding-bottom: 120px; }
    @endif
    .bulk-order-header {
        background: linear-gradient(135deg, var(--wa-dark), var(--wa));
        color: #fff;
        padding: 16px 18px 20px;
        border-radius: {{ $isAgent ? '12px' : '0' }};
        margin-bottom: {{ $isAgent ? '14px' : '0' }};
    }
    .bulk-order-header h1 { margin: 0 0 4px; font-size: 1.15rem; }
    .bulk-order-header p { margin: 0; font-size: .85rem; opacity: .9; }
    .bulk-order-wrap { max-width: 720px; margin: 0 auto; padding: {{ $isAgent ? '0' : '14px' }}; }
    .bulk-order-panel {
        background: var(--card);
        border-radius: 12px;
        padding: 14px;
        margin-bottom: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
        border: {{ $isAgent ? '1px solid #e5e7eb' : 'none' }};
    }
    .bulk-order-panel h2 {
        margin: 0 0 12px;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--muted);
    }
    .bulk-order-filters { display: grid; gap: 10px; }
    .bulk-order-filters input, .bulk-order-filters select, .bulk-order-app textarea {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 10px 12px;
        font: inherit;
    }
    .bulk-order-product-list { display: grid; gap: 8px; max-height: 420px; overflow: auto; }
    .bulk-order-product-row {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 10px;
        align-items: start;
        padding: 12px;
        border: 1px solid var(--border);
        border-radius: 10px;
    }
    .bulk-order-product-row strong { display: block; font-size: .92rem; margin-bottom: 4px; }
    .bulk-order-product-row small { color: var(--muted); display: block; line-height: 1.4; }
    .bulk-order-product-desc {
        font-size: .82rem;
        color: #374151;
        margin: 4px 0 6px;
        line-height: 1.45;
    }
    .bulk-order-product-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 6px;
    }
    .bulk-order-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: .72rem;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 999px;
        background: #ecfdf5;
        color: #047857;
    }
    .bulk-order-tag.price-tag {
        background: #f0f2f5;
        color: #111;
    }
    .bulk-order-product-actions {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 8px;
    }
    .bulk-order-btn {
        border: none;
        border-radius: 8px;
        padding: 8px 12px;
        font: inherit;
        font-weight: 600;
        cursor: pointer;
    }
    .bulk-order-btn-primary { background: var(--wa); color: #fff; }
    .bulk-order-btn-primary:disabled { opacity: .5; cursor: not-allowed; }
    .bulk-order-btn-ghost { background: #f0f2f5; color: #111; }
    .bulk-order-btn-danger { background: #fee2e2; color: #991b1b; }
    .bulk-order-cart-item {
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 12px;
        margin-bottom: 8px;
    }
    .bulk-order-cart-item-head { display: flex; justify-content: space-between; gap: 8px; margin-bottom: 8px; align-items: flex-start; }
    .bulk-order-cart-item-head strong { font-size: .95rem; line-height: 1.3; }
    .bulk-order-cart-meta {
        font-size: .82rem;
        color: #374151;
        line-height: 1.45;
        margin-bottom: 10px;
    }
    .bulk-order-cart-meta .measurements {
        display: inline-block;
        margin-top: 4px;
        font-size: .78rem;
        font-weight: 600;
        color: #047857;
        background: #ecfdf5;
        padding: 3px 8px;
        border-radius: 999px;
    }
    .bulk-order-cart-foot {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .bulk-order-line-total {
        font-weight: 700;
        color: var(--wa-dark);
        font-size: .95rem;
        white-space: nowrap;
    }
    .bulk-order-qty-stepper {
        display: inline-flex;
        align-items: center;
        border: 1px solid var(--border);
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
    }
    .bulk-order-qty-stepper button {
        width: 38px;
        height: 38px;
        border: none;
        background: #f0f2f5;
        color: #111;
        font-size: 1.15rem;
        font-weight: 700;
        cursor: pointer;
        line-height: 1;
    }
    .bulk-order-qty-stepper button:hover:not(:disabled) { background: #e2e8f0; }
    .bulk-order-qty-stepper button:disabled { opacity: .4; cursor: not-allowed; }
    .bulk-order-qty-stepper input {
        width: 46px;
        height: 38px;
        border: none;
        border-left: 1px solid var(--border);
        border-right: 1px solid var(--border);
        text-align: center;
        font: inherit;
        font-weight: 700;
        padding: 0;
        -moz-appearance: textfield;
    }
    .bulk-order-qty-stepper input::-webkit-outer-spin-button,
    .bulk-order-qty-stepper input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .bulk-order-qty-fixed {
        display: inline-flex;
        align-items: center;
        height: 38px;
        padding: 0 12px;
        border-radius: 10px;
        background: #f0f2f5;
        font-size: .85rem;
        font-weight: 600;
        color: var(--muted);
    }
    .bulk-order-qty-row { display: flex; gap: 8px; align-items: center; }
    .bulk-order-qty-row input[type=number] { width: 72px; }
    .bulk-order-cart-empty { color: var(--muted); font-size: .9rem; text-align: center; padding: 20px 0; }
    .bulk-order-footer {
        position: {{ $isAgent ? 'sticky' : 'fixed' }};
        left: 0; right: 0; bottom: 0;
        background: #fff;
        border-top: 1px solid var(--border);
        padding: 12px 14px calc(12px + env(safe-area-inset-bottom));
        box-shadow: 0 -4px 20px rgba(0,0,0,.08);
        border-radius: {{ $isAgent ? '12px' : '0' }};
        margin-top: {{ $isAgent ? '12px' : '0' }};
        z-index: 5;
    }
    .bulk-order-footer-inner { max-width: 720px; margin: 0 auto; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .bulk-order-total { flex: 1; font-size: .95rem; min-width: 140px; }
    .bulk-order-total strong { display: block; font-size: 1.2rem; color: var(--wa-dark); }
    .bulk-order-toast {
        position: fixed; top: 16px; left: 50%; transform: translateX(-50%);
        background: #111; color: #fff; padding: 10px 16px; border-radius: 8px;
        font-size: .85rem; z-index: 20; display: none;
    }
    .bulk-order-success {
        display: none;
        text-align: center;
        padding: 40px 20px;
    }
    .bulk-order-success .icon { font-size: 3rem; margin-bottom: 12px; }
    .bulk-order-contact-picker { position: relative; }
    .bulk-order-contact-selected {
        display: flex; align-items: center; justify-content: space-between; gap: 10px;
        padding: 10px 12px; border: 1px solid #bbf7d0; background: #f0fdf4; border-radius: 10px;
    }
    .bulk-order-contact-selected strong { display: block; color: #14532d; }
    .bulk-order-contact-selected small { color: #166534; }
    .bulk-order-contact-results {
        position: absolute; left: 0; right: 0; top: calc(100% + 4px);
        background: #fff; border: 1px solid var(--border); border-radius: 10px;
        max-height: 220px; overflow: auto; z-index: 10; display: none;
        box-shadow: 0 8px 24px rgba(0,0,0,.12);
    }
    .bulk-order-contact-results button {
        display: block; width: 100%; text-align: left; border: none; background: #fff;
        padding: 10px 12px; cursor: pointer; font: inherit;
    }
    .bulk-order-contact-results button:hover { background: #f0f2f5; }
    .bulk-order-form-disabled { opacity: .55; pointer-events: none; }
    .bulk-order-notify-row {
        display: flex; align-items: center; gap: 8px; font-size: .85rem; color: var(--muted);
        width: 100%;
    }
    .bulk-order-back-link {
        display: inline-flex; align-items: center; gap: 6px;
        color: #fff; opacity: .9; text-decoration: none; font-size: .82rem; margin-bottom: 8px;
    }
    .bulk-order-back-link:hover { opacity: 1; color: #fff; }
    .bulk-order-btn.is-loading {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-width: 148px;
    }
    .bulk-order-btn-spinner,
    .bulk-order-submit-spinner {
        width: 18px;
        height: 18px;
        border: 2px solid rgba(255,255,255,.35);
        border-top-color: #fff;
        border-radius: 50%;
        animation: bulk-order-spin .7s linear infinite;
        flex-shrink: 0;
    }
    .bulk-order-submit-spinner {
        width: 36px;
        height: 36px;
        border-width: 3px;
        border-color: rgba(18, 140, 126, .2);
        border-top-color: var(--wa);
        margin: 0 auto 14px;
    }
    @keyframes bulk-order-spin {
        to { transform: rotate(360deg); }
    }
    .bulk-order-submit-overlay {
        position: fixed;
        inset: 0;
        z-index: 30;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(17, 24, 39, .45);
        backdrop-filter: blur(2px);
    }
    .bulk-order-submit-overlay.is-visible {
        display: flex;
    }
    .bulk-order-submit-overlay-card {
        width: min(100%, 340px);
        background: #fff;
        border-radius: 14px;
        padding: 24px 20px;
        text-align: center;
        box-shadow: 0 12px 40px rgba(0,0,0,.18);
    }
    .bulk-order-submit-overlay-card strong {
        display: block;
        font-size: 1rem;
        margin-bottom: 8px;
        color: var(--wa-dark);
    }
    .bulk-order-submit-overlay-card p {
        margin: 0;
        font-size: .88rem;
        line-height: 1.45;
        color: var(--muted);
    }
</style>

<div class="bulk-order-app" id="bulkOrderApp" data-mode="{{ $isAgent ? 'agent' : 'public' }}">
    <header class="bulk-order-header">
        @if($isAgent && !empty($ordersUrl))
            <a href="{{ $ordersUrl }}" class="bulk-order-back-link"><i class="fas fa-arrow-left"></i> Volver a pedidos</a>
        @endif
        <h1>{{ $headerTitle }}</h1>
        <p>{{ $headerSubtitle }}</p>
    </header>

    <div class="bulk-order-wrap" id="bulkOrderMain">
        <div id="bulkFormScreen">
            @if($isAgent)
            <section class="bulk-order-panel">
                <h2>Cliente</h2>
                <div class="bulk-order-contact-picker" id="contactPicker">
                    <div id="contactSelectedBox" style="display:none">
                        <div class="bulk-order-contact-selected">
                            <div>
                                <strong id="selectedContactName"></strong>
                                <small id="selectedContactPhone"></small>
                            </div>
                            <button type="button" class="bulk-order-btn bulk-order-btn-ghost" id="changeContactBtn">Cambiar</button>
                        </div>
                    </div>
                    <div id="contactSearchBox">
                        <input type="search" id="contactSearch" placeholder="Buscar por nombre, teléfono o cédula…" autocomplete="off">
                        <div class="bulk-order-contact-results" id="contactResults"></div>
                    </div>
                </div>
            </section>
            @endif

            <div id="bulkOrderFormBody" @if($isAgent) class="bulk-order-form-disabled" @endif>
                <section class="bulk-order-panel">
                    <h2>Catálogo</h2>
                    <div class="bulk-order-filters">
                        <input type="search" id="bulkSearch" placeholder="Buscar por nombre o SKU…" autocomplete="off">
                        <select id="bulkCategory">
                            <option value="">Todas las categorías</option>
                        </select>
                    </div>
                    <div class="bulk-order-product-list" id="bulkProductList" style="margin-top:12px"></div>
                </section>

                <section class="bulk-order-panel">
                    <h2>{{ $isAgent ? 'Lista del pedido' : 'Tu lista' }}</h2>
                    <div id="bulkCartItems"></div>
                    <p class="bulk-order-cart-empty" id="bulkCartEmpty">Aún no agregaste productos.</p>
                    <label for="bulkOrderNote" style="display:block;margin-top:12px;font-size:.85rem;color:var(--muted)">Nota general del pedido (opcional)</label>
                    <textarea id="bulkOrderNote" rows="2" placeholder="Instrucciones de entrega, facturación…"></textarea>
                </section>
            </div>
        </div>

        <div class="bulk-order-success" id="bulkSuccessScreen">
            <div class="icon">✅</div>
            <h2>Pedido enviado</h2>
            <p id="bulkSuccessOrderNumber" style="font-size:1.1rem;font-weight:700;color:var(--wa-dark);margin:12px 0;"></p>
            <p id="bulkSuccessHint">{{ $successWhatsappHint }}</p>
            <p id="bulkSuccessPdfWrap" style="display:none;margin-top:16px">
                <a href="#" id="bulkSuccessPdfLink" class="bulk-order-btn bulk-order-btn-primary" style="display:inline-block;text-decoration:none" target="_blank" rel="noopener">
                    Descargar PDF de la orden
                </a>
            </p>
            @if($isAgent && !empty($ordersUrl))
                <p style="margin-top:16px">
                    <a href="{{ $ordersUrl }}" class="bulk-order-btn bulk-order-btn-primary" style="display:inline-block;text-decoration:none">Ver listado de pedidos</a>
                </p>
            @endif
        </div>
    </div>

    <div class="bulk-order-footer" id="bulkFooterBar">
        <div class="bulk-order-footer-inner">
            @if($isAgent)
            <label class="bulk-order-notify-row">
                <input type="checkbox" id="bulkNotifyWhatsapp" checked>
                Enviar número de pedido al cliente por WhatsApp
            </label>
            @endif
            <div class="bulk-order-total">
                <span id="bulkItemsCount">0 productos</span>
                <strong id="bulkGrandTotal">$0.00</strong>
            </div>
            <button type="button" class="bulk-order-btn bulk-order-btn-primary" id="bulkSubmitBtn" disabled>Enviar pedido</button>
        </div>
    </div>

    <div class="bulk-order-toast" id="bulkToast"></div>

    <div class="bulk-order-submit-overlay" id="bulkSubmitOverlay" aria-hidden="true" aria-live="polite" aria-busy="false">
        <div class="bulk-order-submit-overlay-card">
            <div class="bulk-order-submit-spinner" aria-hidden="true"></div>
            <strong id="bulkSubmitOverlayTitle">Enviando pedido…</strong>
            <p id="bulkSubmitOverlayText">Estamos registrando tu pedido. Esto puede tardar unos segundos.</p>
        </div>
    </div>
</div>

<script>
(function () {
    const isAgent = @json($isAgent);
    const catalogUrl = @json($catalogUrl);
    const submitUrl = @json($submitUrl);
    const contactsSearchUrl = @json($contactsSearchUrl ?? null);
    const initialContact = @json($initialContact ?? null);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    let products = [];
    let cart = [];
    let selectedContact = initialContact ? { ...initialContact } : null;
    let isSubmitting = false;

    const root = document.getElementById('bulkOrderApp');
    const el = (id) => document.getElementById(id);
    const fmt = (n) => '$' + Number(n).toFixed(2);
    const submitBtnDefaultLabel = 'Enviar pedido';

    function setSubmitting(submitting) {
        isSubmitting = submitting;
        const btn = el('bulkSubmitBtn');
        const overlay = el('bulkSubmitOverlay');
        const overlayTitle = el('bulkSubmitOverlayTitle');
        const overlayText = el('bulkSubmitOverlayText');

        if (overlay) {
            overlay.classList.toggle('is-visible', submitting);
            overlay.setAttribute('aria-hidden', submitting ? 'false' : 'true');
            overlay.setAttribute('aria-busy', submitting ? 'true' : 'false');
        }

        if (overlayTitle) {
            overlayTitle.textContent = isAgent ? 'Registrando pedido…' : 'Enviando pedido…';
        }
        if (overlayText) {
            overlayText.textContent = isAgent
                ? 'Guardando el pedido y, si corresponde, enviando WhatsApp al cliente. Un momento.'
                : 'Estamos registrando tu pedido y preparando el mensaje en WhatsApp. Un momento.';
        }

        if (submitting) {
            btn.disabled = true;
            btn.classList.add('is-loading');
            btn.innerHTML = '<span class="bulk-order-btn-spinner" aria-hidden="true"></span> Enviando…';
            document.body.style.overflow = 'hidden';
        } else {
            btn.classList.remove('is-loading');
            btn.textContent = submitBtnDefaultLabel;
            document.body.style.overflow = '';
            updateFormEnabled();
        }
    }

    function toast(msg) {
        const t = el('bulkToast');
        t.textContent = msg;
        t.style.display = 'block';
        setTimeout(() => { t.style.display = 'none'; }, 2800);
    }

    function escapeHtml(str) {
        return String(str || '').replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[c]));
    }

    function debounce(fn, ms) {
        let t;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), ms);
        };
    }

    function productMetaHtml(p, { truncateDesc = 120 } = {}) {
        let html = '';
        if (p.description) {
            const desc = truncateDesc && p.description.length > truncateDesc
                ? p.description.slice(0, truncateDesc - 1) + '…'
                : p.description;
            html += `<p class="bulk-order-product-desc">${escapeHtml(desc)}</p>`;
        }
        html += '<div class="bulk-order-product-meta">';
        if (p.measurements) {
            html += `<span class="bulk-order-tag">📏 ${escapeHtml(p.measurements)}</span>`;
        }
        if (p.sku) {
            html += `<span class="bulk-order-tag price-tag">SKU ${escapeHtml(p.sku)}</span>`;
        }
        html += `<span class="bulk-order-tag price-tag">${fmt(p.price)}</span>`;
        html += '</div>';
        return html;
    }

    function cartMetaHtml(line) {
        let html = '';
        if (line.description) {
            html += `<div>${escapeHtml(line.description)}</div>`;
        }
        if (line.measurements) {
            html += `<span class="measurements">📏 ${escapeHtml(line.measurements)}</span>`;
        }
        return html ? `<div class="bulk-order-cart-meta">${html}</div>` : '';
    }

    function changeQty(idx, delta) {
        const line = cart[idx];
        if (!line || !line.allow_quantity) return;
        line.quantity = Math.min(line.max_qty, Math.max(line.min_qty, line.quantity + delta));
        renderCart();
    }

    function setQty(idx, value) {
        const line = cart[idx];
        if (!line || !line.allow_quantity) return;
        line.quantity = Math.min(line.max_qty, Math.max(line.min_qty, Number(value) || line.min_qty));
        renderCart();
    }

    function updateFormEnabled() {
        const btn = el('bulkSubmitBtn');
        if (isSubmitting) {
            btn.disabled = true;
            return;
        }
        if (!isAgent) {
            btn.disabled = cart.length === 0;
            return;
        }
        const body = el('bulkOrderFormBody');
        const enabled = !!selectedContact;
        body.classList.toggle('bulk-order-form-disabled', !enabled);
        btn.disabled = cart.length === 0 || !enabled;
    }

    function renderSelectedContact() {
        if (!isAgent) return;
        const has = !!selectedContact;
        el('contactSelectedBox').style.display = has ? 'block' : 'none';
        el('contactSearchBox').style.display = has ? 'none' : 'block';
        if (has) {
            el('selectedContactName').textContent = selectedContact.name;
            el('selectedContactPhone').textContent = selectedContact.phone || '';
        } else {
            el('contactSearch').value = '';
            el('contactResults').style.display = 'none';
        }
        updateFormEnabled();
    }

    async function searchContacts(q) {
        if (!contactsSearchUrl || q.trim().length < 2) {
            el('contactResults').style.display = 'none';
            return;
        }
        const res = await fetch(contactsSearchUrl + '?q=' + encodeURIComponent(q.trim()));
        const data = await res.json();
        const box = el('contactResults');
        const list = data.contacts || [];
        if (!list.length) {
            box.innerHTML = '<div style="padding:10px 12px;color:#667781;font-size:.85rem">Sin resultados</div>';
            box.style.display = 'block';
            return;
        }
        box.innerHTML = list.map(c => `
            <button type="button" data-contact-id="${c.id}" data-contact-name="${escapeHtml(c.name)}" data-contact-phone="${escapeHtml(c.phone || '')}">
                <strong>${escapeHtml(c.name)}</strong><br>
                <small style="color:#667781">${escapeHtml(c.phone || '')}</small>
            </button>
        `).join('');
        box.style.display = 'block';
        box.querySelectorAll('[data-contact-id]').forEach(btn => {
            btn.addEventListener('click', () => {
                selectedContact = {
                    id: Number(btn.dataset.contactId),
                    name: btn.dataset.contactName,
                    phone: btn.dataset.contactPhone,
                };
                renderSelectedContact();
            });
        });
    }

    async function loadCatalog() {
        const q = el('bulkSearch').value.trim();
        const cat = el('bulkCategory').value;
        const params = new URLSearchParams();
        if (q) params.set('q', q);
        if (cat) params.set('category', cat);
        const res = await fetch(catalogUrl + '?' + params.toString());
        const data = await res.json();
        products = data.products || [];

        const sel = el('bulkCategory');
        const current = sel.value;
        if (sel.options.length <= 1) {
            (data.categories || []).forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = (c.icon ? c.icon + ' ' : '') + c.title;
                sel.appendChild(opt);
            });
            sel.value = current;
        }

        renderProducts();
    }

    function renderProducts() {
        const box = el('bulkProductList');
        if (!products.length) {
            box.innerHTML = '<p class="bulk-order-cart-empty">No hay productos con ese filtro.</p>';
            return;
        }
        box.innerHTML = products.map(p => `
            <div class="bulk-order-product-row">
                <div>
                    <strong>${escapeHtml(p.name)}</strong>
                    ${productMetaHtml(p)}
                </div>
                <div class="bulk-order-product-actions">
                    <button type="button" class="bulk-order-btn bulk-order-btn-primary" data-add="${p.id}">+ Agregar</button>
                </div>
            </div>
        `).join('');

        box.querySelectorAll('[data-add]').forEach(btn => {
            btn.addEventListener('click', () => addProduct(Number(btn.dataset.add)));
        });
    }

    function addProduct(id) {
        const p = products.find(x => x.id === id);
        if (!p) return;
        const existing = cart.find(x => x.product_id === id);
        if (existing) {
            existing.quantity = Math.min(existing.max_qty, existing.quantity + 1);
        } else {
            cart.push({
                product_id: id,
                name: p.name,
                description: p.description || '',
                measurements: p.measurements || '',
                price: p.price,
                quantity: p.min_qty || 1,
                min_qty: p.min_qty || 1,
                max_qty: p.max_qty || 99,
                allow_quantity: p.allow_quantity,
                note: '',
            });
        }
        renderCart();
        toast('Producto agregado');
    }

    function removeLine(idx) {
        cart.splice(idx, 1);
        renderCart();
    }

    function renderCart() {
        const box = el('bulkCartItems');
        el('bulkCartEmpty').style.display = cart.length ? 'none' : 'block';
        box.innerHTML = cart.map((line, idx) => `
            <div class="bulk-order-cart-item">
                <div class="bulk-order-cart-item-head">
                    <strong>${escapeHtml(line.name)}</strong>
                    <button type="button" class="bulk-order-btn bulk-order-btn-danger" data-remove="${idx}">Quitar</button>
                </div>
                ${cartMetaHtml(line)}
                <div class="bulk-order-cart-foot">
                    ${line.allow_quantity ? `
                        <div class="bulk-order-qty-stepper">
                            <button type="button" aria-label="Menos" data-qty-minus="${idx}" ${line.quantity <= line.min_qty ? 'disabled' : ''}>−</button>
                            <input type="number" min="${line.min_qty}" max="${line.max_qty}" value="${line.quantity}" data-qty="${idx}" aria-label="Cantidad">
                            <button type="button" aria-label="Más" data-qty-plus="${idx}" ${line.quantity >= line.max_qty ? 'disabled' : ''}>+</button>
                        </div>
                    ` : `
                        <span class="bulk-order-qty-fixed">1 unidad</span>
                    `}
                    <span class="bulk-order-line-total">${fmt(line.price * line.quantity)}</span>
                </div>
                <textarea rows="2" placeholder="Nota de esta línea (opcional)" data-note="${idx}" style="margin-top:10px">${escapeHtml(line.note)}</textarea>
            </div>
        `).join('');

        box.querySelectorAll('[data-remove]').forEach(btn => {
            btn.addEventListener('click', () => removeLine(Number(btn.dataset.remove)));
        });
        box.querySelectorAll('[data-qty-minus]').forEach(btn => {
            btn.addEventListener('click', () => changeQty(Number(btn.dataset.qtyMinus), -1));
        });
        box.querySelectorAll('[data-qty-plus]').forEach(btn => {
            btn.addEventListener('click', () => changeQty(Number(btn.dataset.qtyPlus), 1));
        });
        box.querySelectorAll('[data-qty]').forEach(input => {
            input.addEventListener('change', () => setQty(Number(input.dataset.qty), input.value));
        });
        box.querySelectorAll('[data-note]').forEach(ta => {
            ta.addEventListener('input', () => {
                cart[Number(ta.dataset.note)].note = ta.value;
            });
        });

        const total = cart.reduce((s, l) => s + l.price * l.quantity, 0);
        const count = cart.reduce((s, l) => s + l.quantity, 0);
        el('bulkItemsCount').textContent = count + (count === 1 ? ' unidad' : ' unidades');
        el('bulkGrandTotal').textContent = fmt(total);
        updateFormEnabled();
    }

    el('bulkSearch').addEventListener('input', debounce(loadCatalog, 300));
    el('bulkCategory').addEventListener('change', loadCatalog);

    if (isAgent) {
        el('contactSearch').addEventListener('input', debounce((e) => searchContacts(e.target.value), 300));
        el('changeContactBtn').addEventListener('click', () => {
            selectedContact = null;
            renderSelectedContact();
        });
        document.addEventListener('click', (e) => {
            if (!el('contactPicker').contains(e.target)) {
                el('contactResults').style.display = 'none';
            }
        });
        renderSelectedContact();
    }

    el('bulkSubmitBtn').addEventListener('click', async () => {
        if (isSubmitting) return;
        if (isAgent && !selectedContact) {
            toast('Selecciona un cliente');
            return;
        }
        setSubmitting(true);
        try {
            const payload = {
                items: cart.map(l => ({
                    product_id: l.product_id,
                    quantity: l.quantity,
                    note: l.note || null,
                })),
                order_note: el('bulkOrderNote').value.trim() || null,
            };
            if (isAgent) {
                payload.contact_id = selectedContact.id;
                payload.notify_whatsapp = el('bulkNotifyWhatsapp').checked;
            }

            const res = await fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (!res.ok || !data.ok) {
                throw new Error(data.message || 'No se pudo enviar el pedido.');
            }
            el('bulkFormScreen').style.display = 'none';
            el('bulkFooterBar').style.display = 'none';
            el('bulkSuccessScreen').style.display = 'block';
            document.body.style.overflow = '';
            if (data.order_number) {
                el('bulkSuccessOrderNumber').textContent = data.order_number;
            }
            if (data.pdf_url) {
                const pdfWrap = el('bulkSuccessPdfWrap');
                const pdfLink = el('bulkSuccessPdfLink');
                pdfLink.href = data.pdf_url;
                pdfWrap.style.display = 'block';
            }
            if (isAgent) {
                const hint = el('bulkSuccessHint');
                const parts = ['Pedido registrado para ' + (data.contact_name || 'el cliente') + '.'];
                if (el('bulkNotifyWhatsapp').checked) {
                    parts.push('Se envió el PDF con botones de confirmación por WhatsApp.');
                }
                hint.textContent = parts.join(' ');
            }
        } catch (e) {
            toast(e.message || 'Error al enviar');
            setSubmitting(false);
        }
    });

    loadCatalog();
})();
</script>
