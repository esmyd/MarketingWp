@extends('admin.layouts.app')

@section('header')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
    body.dashboard-page {
        background: linear-gradient(180deg, #eef2f7 0%, #f8fafc 40%, #f1f5f9 100%) !important;
    }

    .dash-wrap { max-width: 1200px; margin: 0 auto; }

    .dash-top {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .dash-top h1 { font-size: 1.45rem; font-weight: 700; color: #1a1d21; margin: 0 0 .25rem; }
    .dash-top p { margin: 0; color: #6c757d; font-size: .88rem; }

    .dash-filter {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .5rem;
        background: #fff;
        border: 1px solid #e3e7ee;
        border-radius: 10px;
        padding: .5rem .75rem;
    }

    .dash-filter input {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: .4rem .6rem;
        font-size: .85rem;
    }

    .dash-filter button {
        background: #128c7e;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: .45rem 1rem;
        font-size: .85rem;
        font-weight: 600;
        cursor: pointer;
    }

    .hero-cost {
        background: linear-gradient(135deg, #075e54 0%, #128c7e 100%);
        color: #fff;
        border-radius: 16px;
        padding: 1.5rem 1.75rem;
        margin-bottom: 1rem;
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 1rem;
        align-items: center;
    }

    @media (max-width: 768px) {
        .hero-cost { grid-template-columns: 1fr; }
    }

    .hero-cost .label {
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        opacity: .85;
        margin-bottom: .35rem;
    }

    .hero-cost .amount {
        font-size: clamp(1.75rem, 4vw, 2.35rem);
        font-weight: 800;
        line-height: 1.15;
    }

    .hero-cost .sub {
        font-size: .88rem;
        opacity: .9;
        margin-top: .5rem;
    }

    .hero-cost .month-box {
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.2);
        border-radius: 12px;
        padding: 1rem 1.25rem;
        min-width: 200px;
    }

    .hero-cost .month-box .lbl { font-size: .72rem; opacity: .8; text-transform: uppercase; }
    .hero-cost .month-box .val { font-size: 1.25rem; font-weight: 700; margin-top: .25rem; }

    .strategy-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    @media (max-width: 768px) {
        .strategy-grid { grid-template-columns: 1fr; }
    }

    .strategy-card {
        position: relative;
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.06);
        border-radius: 16px;
        padding: 1.15rem 1.25rem 1rem;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06), 0 1px 3px rgba(15, 23, 42, 0.04);
        overflow: hidden;
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .strategy-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--accent, #128c7e);
    }

    .strategy-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.09);
    }

    .strategy-card[data-category="service"] {
        --accent: #6366f1;
        --accent-soft: #eef2ff;
        --accent-mid: #c7d2fe;
        --accent-text: #4338ca;
    }

    .strategy-card[data-category="utility"] {
        --accent: #0d9488;
        --accent-soft: #ecfdf5;
        --accent-mid: #99f6e4;
        --accent-text: #0f766e;
    }

    .strategy-card[data-category="marketing"] {
        --accent: #ea580c;
        --accent-soft: #fff7ed;
        --accent-mid: #fdba74;
        --accent-text: #c2410c;
    }

    .strategy-card[data-category="authentication"] {
        --accent: #7c3aed;
        --accent-soft: #f5f3ff;
        --accent-mid: #ddd6fe;
        --accent-text: #6d28d9;
    }

    .strategy-card .head {
        display: flex;
        align-items: flex-start;
        gap: .85rem;
        margin-bottom: 1rem;
    }

    .strategy-card .icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: var(--accent-soft, #ecfdf5);
        border: 1px solid var(--accent-mid, #99f6e4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        line-height: 1;
        flex-shrink: 0;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.8);
    }

    .strategy-card h3 { font-size: .98rem; font-weight: 700; color: #0f172a; margin: 0 0 .25rem; }
    .strategy-card .desc { font-size: .8rem; color: #64748b; line-height: 1.45; margin: 0; }

    .strategy-card .stats {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .75rem;
        padding: .85rem 1rem;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--accent-soft, #f8fafc) 0%, #fff 100%);
        border: 1px solid rgba(15, 23, 42, 0.05);
    }

    .strategy-card .count { font-size: 1.65rem; font-weight: 800; color: #0f172a; line-height: 1; }
    .strategy-card .count-lbl { font-size: .68rem; color: #64748b; text-transform: uppercase; letter-spacing: .04em; margin-top: .2rem; font-weight: 600; }

    .strategy-card .cost-box {
        text-align: right;
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.06);
        border-radius: 10px;
        padding: .45rem .7rem;
        min-width: 88px;
    }

    .strategy-card .cost-lbl { font-size: .62rem; color: #94a3b8; text-transform: uppercase; letter-spacing: .04em; font-weight: 600; }
    .strategy-card .cost { font-size: 1rem; font-weight: 800; color: var(--accent-text, #128c7e); line-height: 1.2; }
    .strategy-card .cost-range { display: block; font-size: .72rem; color: #64748b; font-weight: 500; margin-top: .1rem; }

    .sales-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    @media (max-width: 992px) {
        .sales-row { grid-template-columns: repeat(2, 1fr); }
    }

    .sales-card {
        position: relative;
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.06);
        border-radius: 14px;
        padding: 1rem 1rem 1rem 3.25rem;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
        overflow: hidden;
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .sales-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.08);
    }

    .sales-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--sales-accent, #128c7e);
    }

    .sales-card .sales-icon {
        position: absolute;
        left: .85rem;
        top: 1rem;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .9rem;
        color: var(--sales-accent, #128c7e);
        background: var(--sales-soft, #ecfdf5);
    }

    .sales-card[data-metric="orders"] { --sales-accent: #059669; --sales-soft: #ecfdf5; }
    .sales-card[data-metric="revenue"] { --sales-accent: #0284c7; --sales-soft: #e0f2fe; }
    .sales-card[data-metric="clients"] { --sales-accent: #7c3aed; --sales-soft: #f3e8ff; }
    .sales-card[data-metric="attention"] { --sales-accent: #d97706; --sales-soft: #fffbeb; }

    .sales-card .lbl { font-size: .68rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: .04em; }
    .sales-card .val { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-top: .2rem; line-height: 1.15; }
    .sales-card .sub { font-size: .76rem; color: #64748b; margin-top: .35rem; }

    .sales-card .sub.positive { color: #059669; font-weight: 600; }
    .sales-card .sub.negative { color: #dc2626; font-weight: 600; }

    .panel {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.06);
        border-radius: 14px;
        padding: 1.1rem 1.25rem;
        margin-bottom: 1rem;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
    }

    .panel h3 { font-size: .95rem; font-weight: 600; color: #1a1d21; margin: 0 0 1rem; }
    .panel-chart { height: 220px; position: relative; }

    .list-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: .65rem 0;
        border-bottom: 1px solid #f1f3f5;
        font-size: .85rem;
    }

    .list-row:last-child { border-bottom: none; }

    .badge-status {
        font-size: .7rem;
        font-weight: 600;
        padding: .2rem .5rem;
        border-radius: 6px;
        text-transform: capitalize;
    }

    .badge-status.confirmed, .badge-status.completed, .badge-status.paid { background: #dcfce7; color: #166534; }
    .badge-status.pending, .badge-status.payment_pending { background: #fef3c7; color: #92400e; }
    .badge-status.cancelled { background: #fee2e2; color: #991b1b; }

    .dash-note {
        font-size: .75rem;
        color: #9ca3af;
        margin-top: .75rem;
        line-height: 1.5;
    }

    .dash-note a { color: #128c7e; }

    .demo-reset-panel {
        border: 1px solid #fde68a;
        background: #fffbeb;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        margin-bottom: 1rem;
    }

    .demo-reset-panel h3 {
        font-size: .95rem;
        font-weight: 700;
        color: #92400e;
        margin: 0 0 .35rem;
    }

    .demo-reset-panel p {
        margin: 0;
        font-size: .84rem;
        color: #a16207;
        line-height: 1.45;
        max-width: 640px;
    }

    .demo-reset-panel .demo-reset-actions {
        display: flex;
        align-items: center;
        gap: .75rem;
        flex-wrap: wrap;
    }

    .demo-reset-btn {
        background: #d97706;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: .5rem 1rem;
        font-size: .85rem;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
    }

    .demo-reset-btn:hover { background: #b45309; color: #fff; }

    .demo-reset-check {
        display: flex;
        align-items: center;
        gap: .4rem;
        font-size: .8rem;
        color: #92400e;
    }
</style>
<script>document.body.classList.add('dashboard-page');</script>
@endsection

@section('content')
@php
    $m = $metrics;
    $c = $consumptionReport;
    $statusLabels = [
        'pending' => 'Pendiente',
        'confirmed' => 'Confirmado',
        'completed' => 'Completado',
        'cancelled' => 'Cancelado',
        'payment_pending' => 'Pago pend.',
        'paid' => 'Pagado',
    ];
@endphp

<div class="dash-wrap">
    <div class="dash-top">
        <div>
            <h1>Resumen del negocio</h1>
            <p>Consumo WhatsApp y ventas · {{ $from->format('d/m/Y') }} — {{ $to->format('d/m/Y') }}</p>
        </div>
        <form class="dash-filter" method="get" action="{{ route('admin.dashboard') }}">
            <input type="hidden" name="period" id="period-preset" value="{{ $periodPreset ?? 'month' }}">
            <div class="btn-group btn-group-sm me-1" role="group">
                @foreach(['month' => 'Este mes', '7d' => '7 días', '30d' => '30 días', '90d' => '90 días'] as $key => $label)
                    <button type="button"
                        class="btn {{ ($periodPreset ?? 'month') === $key ? 'btn-success' : 'btn-outline-secondary' }} period-preset-btn"
                        data-period="{{ $key }}">{{ $label }}</button>
                @endforeach
            </div>
            <input type="date" name="from" value="{{ $from->format('Y-m-d') }}">
            <span style="color:#adb5bd">—</span>
            <input type="date" name="to" value="{{ $to->format('Y-m-d') }}">
            <button type="submit"><i class="fas fa-filter me-1"></i> Filtrar</button>
        </form>
    </div>

    @perm('demo.reset')
    <section class="demo-reset-panel" aria-label="Reiniciar demo">
        <div class="demo-reset-actions">
            <div style="flex:1; min-width:220px;">
                <h3><i class="fas fa-rotate-left me-1"></i> Reiniciar demo</h3>
                <p>Elimina todos los chats y pedidos para volver a mostrar la demo desde cero. No borra productos, categorías ni configuración del bot.</p>
            </div>
            <form method="post" action="{{ route('admin.demo.reset') }}"
                onsubmit="return confirm('¿Reiniciar la demo? Se borrarán todos los mensajes y pedidos.');">
                @csrf
                <label class="demo-reset-check mb-2">
                    <input type="checkbox" name="confirm" value="1" required>
                    Entiendo que se borrarán los datos
                </label>
                <button type="submit" class="demo-reset-btn">
                    <i class="fas fa-trash-restore me-1"></i> Reiniciar demo
                </button>
            </form>
        </div>
    </section>
    @endperm

    {{-- Consumo Meta destacado --}}
    <section class="hero-cost" aria-label="Consumo estimado WhatsApp">
        <div>
            <div class="label">Consumo estimado en el período</div>
            <div class="amount">
                ${{ number_format($c['total_min'], 2) }}
                @if($c['total_max'] > $c['total_min'])
                    — ${{ number_format($c['total_max'], 2) }}
                @endif
            </div>
            <div class="sub">
                Costos estimados de WhatsApp · {{ $c['currency'] }}
                · No incluye el plan de plataforma
            </div>
        </div>
        <div class="month-box">
            <div class="lbl">Mes actual ({{ $c['month_label'] }})</div>
            <div class="val">${{ number_format($c['month_min'], 2) }} — ${{ number_format($c['month_max'], 2) }}</div>
            <div class="sub" style="margin-top:.5rem;font-size:.8rem">
                Proyección fin de mes: ~${{ number_format($c['projected_min'], 2) }} — ${{ number_format($c['projected_max'], 2) }}
            </div>
        </div>
    </section>

    {{-- Desglose por estrategia Meta --}}
    <section class="strategy-grid" aria-label="Consumo por tipo de conversación">
        @foreach($c['categories'] as $key => $cat)
            <article class="strategy-card" data-category="{{ $key }}">
                <div class="head">
                    <span class="icon-wrap" aria-hidden="true">{{ $cat['icon'] }}</span>
                    <div>
                        <h3>{{ $cat['label'] }}</h3>
                        <p class="desc">{{ $cat['description'] }}</p>
                    </div>
                </div>
                <div class="stats">
                    <div>
                        <div class="count">{{ number_format($cat['count']) }}</div>
                        <div class="count-lbl">conversaciones est.</div>
                    </div>
                    <div class="cost-box">
                        <div class="cost-lbl">Costo est.</div>
                        <div class="cost">${{ number_format($cat['cost_min'], 2) }}</div>
                        @if($cat['cost_max'] > $cat['cost_min'])
                            <span class="cost-range">hasta ${{ number_format($cat['cost_max'], 2) }}</span>
                        @endif
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    {{-- Ventas resumidas --}}
    <section class="sales-row" aria-label="Indicadores de ventas">
        <div class="sales-card" data-metric="orders">
            <span class="sales-icon"><i class="fas fa-shopping-bag"></i></span>
            <div class="lbl">Pedidos</div>
            <div class="val">{{ number_format($m['orders_count']) }}</div>
            <div class="sub {{ $m['orders_growth'] >= 0 ? 'positive' : 'negative' }}">{{ $m['orders_growth'] >= 0 ? '+' : '' }}{{ $m['orders_growth'] }}% vs anterior</div>
        </div>
        <div class="sales-card" data-metric="revenue">
            <span class="sales-icon"><i class="fas fa-dollar-sign"></i></span>
            <div class="lbl">Ingresos</div>
            <div class="val">${{ number_format($m['total_revenue'], 0) }}</div>
            <div class="sub">Ticket prom. ${{ number_format($m['avg_order_value'], 0) }}</div>
        </div>
        <div class="sales-card" data-metric="clients">
            <span class="sales-icon"><i class="fas fa-users"></i></span>
            <div class="lbl">Clientes activos</div>
            <div class="val">{{ number_format($m['active_clients']) }}</div>
            <div class="sub">{{ number_format($m['new_contacts']) }} nuevos</div>
        </div>
        <div class="sales-card" data-metric="attention">
            <span class="sales-icon"><i class="fas fa-headset"></i></span>
            <div class="lbl">Atención</div>
            <div class="val">{{ number_format($m['response_rate'], 0) }}%</div>
            <div class="sub">Respuesta en 24 h · {{ $m['avg_response_time_formatted'] }}</div>
        </div>
    </section>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="panel">
                <h3>Tendencia de consumo diario (estimado)</h3>
                <div class="panel-chart">
                    <canvas id="consumptionChart"></canvas>
                </div>
                <p class="dash-note">
                    Estimación basada en mensajes registrados. Meta puede facturar distinto según categoría y país.
                </p>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="panel">
                <h3>Últimos pedidos</h3>
                @forelse($orders as $order)
                    <div class="list-row">
                        <div>
                            <strong>{{ $order->contact->name ?? 'Cliente' }}</strong>
                            <div class="text-muted" style="font-size:.75rem">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="text-end">
                            <div><strong>${{ number_format($order->total, 2) }}</strong></div>
                            <span class="badge-status {{ $order->status }}">{{ $statusLabels[$order->status] ?? $order->status }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small mb-0">No hay pedidos en este período.</p>
                @endforelse
                <a href="{{ route('admin.orders') }}" class="small text-decoration-none d-inline-block mt-2">Ver todos los pedidos →</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const trend = {!! json_encode($c['daily_trend']) !!};
    const labels = trend.length ? trend.map(d => d.label) : ['Sin datos'];
    const data = trend.length ? trend.map(d => d.cost) : [0];

    new Chart(document.getElementById('consumptionChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Consumo est. (USD)',
                data,
                backgroundColor: 'rgba(18, 140, 126, 0.65)',
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => '$' + v } }
            }
        }
    });

    document.querySelectorAll('.period-preset-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const form = this.closest('form');
            form.querySelector('#period-preset').value = this.dataset.period;
            form.querySelectorAll('input[name="from"], input[name="to"]').forEach(el => el.removeAttribute('name'));
            form.submit();
        });
    });
});
</script>
@endsection
