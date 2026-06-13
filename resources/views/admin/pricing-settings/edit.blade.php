@extends('admin.layouts.app')

@section('header', 'Parámetros de plataforma')

@section('content')
@php
    $limits = $planLimitsSnapshot;
    $raw = $platformLimits;
    $effective = $limits;
@endphp

<style>
    .platform-params { max-width: 960px; }
    .platform-section {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        margin-bottom: 1.25rem;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
    }
    .platform-section-head {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        background: #f8fafc;
    }
    .platform-section-head h2 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #111827;
    }
    .platform-section-head p {
        margin: .35rem 0 0;
        font-size: .82rem;
        color: #6b7280;
    }
    .platform-section-body { padding: 1.25rem; }
    .usage-box {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        border-radius: 10px;
        padding: .85rem 1rem;
        font-size: .85rem;
        color: #374151;
    }
    .usage-box strong { color: #065f46; }
    .usage-over { color: #dc2626; font-weight: 700; }
    .usage-meter { margin-bottom: .65rem; }
    .usage-meter:last-child { margin-bottom: 0; }
    .usage-meter-head {
        display: flex;
        justify-content: space-between;
        font-size: .78rem;
        margin-bottom: .25rem;
        color: #4b5563;
    }
    .usage-meter-bar {
        height: 7px;
        background: #d1fae5;
        border-radius: 999px;
        overflow: hidden;
    }
    .usage-meter-bar > span {
        display: block;
        height: 100%;
        background: linear-gradient(90deg, #059669, #10b981);
        border-radius: 999px;
    }
    .usage-meter.is-warning .usage-meter-bar > span { background: linear-gradient(90deg, #d97706, #f59e0b); }
    .usage-meter.is-danger .usage-meter-bar > span { background: linear-gradient(90deg, #dc2626, #ef4444); }
    .usage-breakdown {
        margin-top: .75rem;
        padding-top: .65rem;
        border-top: 1px dashed #a7f3d0;
        font-size: .75rem;
        color: #4b5563;
    }
    .usage-breakdown div {
        display: flex;
        justify-content: space-between;
        gap: .5rem;
        padding: .15rem 0;
    }
    .platform-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    @media (max-width: 768px) {
        .platform-grid { grid-template-columns: 1fr; }
    }
    .platform-field label {
        display: block;
        font-size: .82rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: .35rem;
    }
    .platform-field input,
    .platform-field select {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: .5rem .65rem;
        font-size: .875rem;
    }
    .platform-field input[type="checkbox"],
    .platform-field input[type="radio"] {
        width: auto;
        padding: 0;
    }
    .platform-field .hint {
        font-size: .72rem;
        color: #9ca3af;
        margin-top: .25rem;
    }
    .platform-save-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.25rem;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
    }
</style>

<div class="platform-params">
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-4">
        <p class="text-sm text-gray-600 mb-0">
            Panel interno de super administrador: define el <strong>plan contratado</strong>, los <strong>límites de capacidad</strong> del cliente y los <strong>costos Meta WhatsApp</strong> que se reflejan en el dashboard.
        </p>
    </div>

    <form action="{{ route('admin.pricing-settings.update') }}" method="POST" id="form-capacidades">
        @csrf
        @method('PUT')
        <input type="hidden" name="_section" value="capacidades">

        {{-- SECCIÓN 1: Plan y capacidades --}}
        <section class="platform-section" id="capacidades">
            <div class="platform-section-head">
                <h2>📦 Plan contratado y límites de capacidad</h2>
                <p>Controla cuántos productos, categorías y espacio en disco puede usar este cliente.</p>
            </div>
            <div class="platform-section-body">
                <div class="platform-grid mb-4">
                    <div class="platform-field">
                        <label for="subscription_plan">Plan contratado</label>
                        <select id="subscription_plan" name="subscription_plan" required>
                            @foreach($plans as $planKey => $planData)
                                <option value="{{ $planKey }}"
                                    @selected(old('subscription_plan', $raw['subscription_plan'] ?? $effective['plan_key'] ?? 'starter') === $planKey)>
                                    {{ $planData['label'] ?? $planData['name'] }} — {{ $planData['price_label'] ?? '' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="hint">Al cambiar el plan se sugieren los límites por defecto (puedes ajustarlos antes de guardar).</p>
                    </div>

                    <div class="usage-box">
                        <div class="font-semibold text-gray-800 mb-2">Uso actual del cliente</div>

                        <div class="usage-meter {{ ($limits['products_percent'] ?? 0) >= 100 ? 'is-danger' : (($limits['products_percent'] ?? 0) >= 80 ? 'is-warning' : '') }}">
                            <div class="usage-meter-head">
                                <span>Productos</span>
                                <strong class="{{ $limits['products_at_limit'] ? 'usage-over' : '' }}">{{ $limits['usage']['products'] }} / {{ $limits['max_products'] }}</strong>
                            </div>
                            <div class="usage-meter-bar"><span style="width: {{ $limits['products_percent'] ?? 0 }}%"></span></div>
                        </div>

                        <div class="usage-meter {{ ($limits['categories_percent'] ?? 0) >= 100 ? 'is-danger' : (($limits['categories_percent'] ?? 0) >= 80 ? 'is-warning' : '') }}">
                            <div class="usage-meter-head">
                                <span>Categorías</span>
                                <strong class="{{ $limits['categories_at_limit'] ? 'usage-over' : '' }}">{{ $limits['usage']['categories'] }} / {{ $limits['max_categories'] }}</strong>
                            </div>
                            <div class="usage-meter-bar"><span style="width: {{ $limits['categories_percent'] ?? 0 }}%"></span></div>
                        </div>

                        <div class="usage-meter {{ ($limits['storage_percent'] ?? 0) >= 100 ? 'is-danger' : (($limits['storage_percent'] ?? 0) >= 80 ? 'is-warning' : '') }}">
                            <div class="usage-meter-head">
                                <span>Espacio en servidor</span>
                                <strong class="{{ $limits['storage_at_limit'] ? 'usage-over' : '' }}">
                                    {{ $limits['usage']['storage_human'] ?? '0 B' }}
                                    / {{ number_format($limits['storage_gb'], 0) }} GB
                                </strong>
                            </div>
                            <div class="usage-meter-bar"><span style="width: {{ max(1, $limits['storage_percent'] ?? 0) }}%"></span></div>
                            <div class="text-xs text-gray-500 mt-1">
                                Valor registrado manualmente · {{ number_format($limits['usage']['storage_gb'] ?? 0, 3) }} GB
                            </div>
                        </div>
                    </div>

                    <div class="platform-field">
                        <label for="max_products_limit">Máximo de productos</label>
                        <input type="number" id="max_products_limit" name="max_products_limit" min="0" max="100000" required
                            value="{{ old('max_products_limit', $raw['max_products_limit'] ?? $effective['max_products']) }}">
                        <p class="hint">Bloquea la creación de productos al alcanzar este número.</p>
                        @error('max_products_limit')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="platform-field">
                        <label for="max_categories_limit">Máximo de categorías</label>
                        <input type="number" id="max_categories_limit" name="max_categories_limit" min="0" max="10000" required
                            value="{{ old('max_categories_limit', $raw['max_categories_limit'] ?? $effective['max_categories']) }}">
                        @error('max_categories_limit')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="platform-field">
                        <label for="storage_gb_used">Espacio usado en servidor (GB)</label>
                        <input type="number" id="storage_gb_used" name="storage_gb_used" min="0" max="10000" step="0.001" required
                            value="{{ old('storage_gb_used', $raw['storage_gb_used'] ?? $limits['usage']['storage_gb'] ?? 0) }}">
                        <p class="hint">Indica cuánto espacio ocupa este cliente hoy. Actualízalo cuando subas archivos o crezca el uso.</p>
                        @error('storage_gb_used')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="platform-field">
                        <label for="storage_gb_limit">Espacio máximo contratado (GB)</label>
                        <input type="number" id="storage_gb_limit" name="storage_gb_limit" min="0" max="10000" step="0.5" required
                            value="{{ old('storage_gb_limit', $raw['storage_gb_limit'] ?? $effective['storage_gb']) }}">
                        <p class="hint">Límite del plan. Se muestra en el dashboard del cliente junto al espacio usado.</p>
                        @error('storage_gb_limit')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    @if($canManageBulkOrder ?? false)
                    <div class="platform-field" style="grid-column: 1 / -1;">
                        <label class="d-flex align-items-center gap-2" style="cursor:pointer;">
                            <input type="hidden" name="bulk_web_order_enabled" value="0">
                            <input type="checkbox" name="bulk_web_order_enabled" value="1"
                                @checked(old('bulk_web_order_enabled', $bulkWebOrderEnabled ?? false))
                                @disabled(!($bulkWebOrderPlanAllowed ?? false))>
                            <span><strong>Pedido masivo por formulario web</strong> (enlace desde WhatsApp)</span>
                        </label>
                        <p class="hint mb-0">
                            @if($bulkWebOrderPlanAllowed ?? false)
                                Requiere plan Pro o superior. Si está activo, el bot muestra «Armar lista» cuando el carrito tiene {{ config('bulk_order.min_cart_lines', 3) }}+ ítems.
                            @else
                                El plan actual (Starter) no incluye esta función. Cámbialo a Pro o Enterprise para habilitarla.
                            @endif
                        </p>
                    </div>
                    @endif
                </div>
            </div>
            <div class="platform-save-bar" style="border-top: 1px solid #f1f5f9; border-radius: 0; margin: 0;">
                <p class="text-xs text-gray-500 mb-0">Plan, productos, categorías y espacio en disco.</p>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg border-0">
                    <i class="fas fa-save"></i> Guardar plan y límites
                </button>
            </div>
        </section>
    </form>

    {{-- SECCIÓN 2: Facturación (formulario independiente) --}}
    @include('admin.partials.platform-billing-admin')

    <form action="{{ route('admin.pricing-settings.update') }}" method="POST" id="form-meta">
        @csrf
        @method('PUT')
        <input type="hidden" name="_section" value="meta">

        <section class="platform-section" id="costos-meta">
            <div class="platform-section-head">
                <h2>💬 Costos Meta WhatsApp</h2>
                <p>Tarifas internas y tipos de conversación visibles en el dashboard y la página de planes.</p>
            </div>
            <div class="platform-section-body">
                <div class="mb-5 p-4 rounded-xl border border-emerald-200 bg-emerald-50">
                    <h3 class="font-semibold text-gray-900 mb-2 text-sm">Tipos de conversación activos</h3>
                    <p class="text-sm text-gray-600 mb-3">Los desactivados no aparecen en el dashboard del cliente ni en /planes.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @php
                            $categoryLabels = [
                                'service' => '💬 Atención al cliente (cuando escriben)',
                                'utility' => '📋 Avisos automáticos del bot',
                                'marketing' => '📢 Promociones / campañas',
                                'authentication' => '🔐 Códigos de verificación (OTP)',
                            ];
                        @endphp
                        @foreach($categoryKeys as $key)
                            <label class="flex items-start gap-2 text-sm cursor-pointer">
                                <input type="checkbox" name="enabled_categories[]" value="{{ $key }}"
                                    @checked(in_array($key, old('enabled_categories', $enabledCategories), true))
                                    class="mt-1 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                <span>{{ $categoryLabels[$key] ?? ucfirst($key) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                    <div class="platform-field">
                        <label>Factor de ajuste interno</label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="meta_markup" step="0.01" min="1" max="3"
                                value="{{ old('meta_markup', $settings->meta_markup) }}" required>
                            <span class="text-sm text-gray-500 whitespace-nowrap">× (ej. 1.30)</span>
                        </div>
                        @error('meta_markup')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="platform-field">
                        <label>Región</label>
                        <input type="text" name="region" value="{{ old('region', $settings->region) }}" required>
                    </div>
                    <div class="platform-field">
                        <label>Moneda</label>
                        <input type="text" name="currency" maxlength="3" value="{{ old('currency', $settings->currency) }}" class="uppercase" required>
                    </div>
                </div>

                <div class="space-y-4">
                    @foreach($categoryKeys as $key)
                        @php
                            $meta = $categories[$key] ?? [];
                            $rate = $settings->rates[$key] ?? ['min' => 0, 'max' => 0];
                            $appliedMin = round(($rate['min'] ?? 0) * $settings->meta_markup, 4);
                            $appliedMax = round(($rate['max'] ?? 0) * $settings->meta_markup, 4);
                            $isEnabled = in_array($key, old('enabled_categories', $enabledCategories), true);
                        @endphp
                        <div class="border rounded-xl p-4 {{ $isEnabled ? 'border-gray-200 bg-gray-50' : 'border-dashed border-gray-300 bg-gray-100 opacity-80' }}">
                            <div class="flex items-start gap-3 mb-3">
                                <span class="text-2xl">{{ $meta['icon'] ?? '💬' }}</span>
                                <div>
                                    <h3 class="font-semibold text-gray-900 text-sm">
                                        {{ $meta['label'] ?? ucfirst($key) }}
                                        @unless($isEnabled)
                                            <span class="text-xs font-normal text-gray-500">(inactivo para el cliente)</span>
                                        @endunless
                                    </h3>
                                    <p class="text-sm text-gray-500">{{ $meta['description'] ?? '' }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="platform-field">
                                    <label>Tarifa base mín. (USD)</label>
                                    <input type="number" name="rates[{{ $key }}][min]" step="0.0001" min="0"
                                        value="{{ old("rates.{$key}.min", $rate['min']) }}" required>
                                </div>
                                <div class="platform-field">
                                    <label>Tarifa base máx. (USD)</label>
                                    <input type="number" name="rates[{{ $key }}][max]" step="0.0001" min="0"
                                        value="{{ old("rates.{$key}.max", $rate['max']) }}" required>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                Aplicado al dashboard: <strong>${{ number_format($appliedMin, 4) }}</strong> — <strong>${{ number_format($appliedMax, 4) }}</strong> por conversación
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="platform-save-bar">
            <p class="text-xs text-gray-500 mb-0 max-w-lg">
                Tarifas Meta y tipos de conversación visibles en el dashboard del cliente.
            </p>
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg border-0">
                <i class="fas fa-save"></i> Guardar costos Meta
            </button>
        </div>
        </section>
    </form>
</div>
@endsection

@push('scripts')
<script>
const planDefaultsByKey = @json(collect($plans)->mapWithKeys(fn ($plan, $key) => [$key => $plan['limits'] ?? []]));

document.getElementById('subscription_plan')?.addEventListener('change', function () {
    const limits = planDefaultsByKey[this.value] || {};
    const map = {
        max_products: 'max_products_limit',
        max_categories: 'max_categories_limit',
        storage_gb: 'storage_gb_limit',
    };
    Object.entries(map).forEach(([from, to]) => {
        const el = document.getElementById(to);
        if (el && limits[from] != null) {
            el.value = limits[from];
        }
    });
});

if (window.location.hash) {
    const target = document.querySelector(window.location.hash);
    if (target) {
        setTimeout(() => target.scrollIntoView({ behavior: 'smooth', block: 'start' }), 100);
    }
}
</script>
@endpush
