@extends('admin.layouts.app')

@section('header')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
    body.dashboard-page { background: #f4f6f9 !important; }

    .dash-wrap { max-width: 1400px; margin: 0 auto; }

    .dash-top {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .dash-top h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a1d21;
        margin: 0 0 0.2rem;
    }

    .dash-top p { margin: 0; color: #6c757d; font-size: 0.9rem; }

    .dash-filter {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        background: #fff;
        border: 1px solid #e3e7ee;
        border-radius: 10px;
        padding: 0.5rem 0.75rem;
    }

    .dash-filter input {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 0.4rem 0.6rem;
        font-size: 0.85rem;
    }

    .dash-filter button {
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 0.45rem 1rem;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
    }

    .dash-filter button:hover { background: #1d4ed8; }

    .kpi-row {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 0.85rem;
        margin-bottom: 0.85rem;
    }

    .kpi-row-6 { grid-template-columns: repeat(6, 1fr); }

    @media (max-width: 1200px) {
        .kpi-row { grid-template-columns: repeat(3, 1fr); }
        .kpi-row-6 { grid-template-columns: repeat(3, 1fr); }
    }

    @media (max-width: 768px) {
        .kpi-row, .kpi-row-6 { grid-template-columns: repeat(2, 1fr); }
    }

    .kpi-card {
        background: #fff;
        border: 1px solid #e8ecf1;
        border-radius: 12px;
        padding: 1rem 1.1rem;
        position: relative;
        min-height: 108px;
    }

    .kpi-card .kpi-label {
        font-size: 0.68rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6c757d;
        margin-bottom: 0.35rem;
    }

    .kpi-card .kpi-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        line-height: 1.2;
    }

    .kpi-card .kpi-sub {
        font-size: 0.78rem;
        color: #6c757d;
        margin-top: 0.35rem;
    }

    .kpi-card .kpi-trend {
        font-size: 0.75rem;
        font-weight: 600;
        margin-top: 0.25rem;
    }

    .kpi-trend.up { color: #16a34a; }
    .kpi-trend.down { color: #dc2626; }
    .kpi-trend.neutral { color: #6c757d; }

    .kpi-icon {
        position: absolute;
        top: 0.85rem;
        right: 0.85rem;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
    }

    .kpi-icon.green { background: #dcfce7; color: #16a34a; }
    .kpi-icon.blue { background: #dbeafe; color: #2563eb; }
    .kpi-icon.amber { background: #fef3c7; color: #d97706; }
    .kpi-icon.purple { background: #ede9fe; color: #7c3aed; }
    .kpi-icon.rose { background: #ffe4e6; color: #e11d48; }
    .kpi-icon.teal { background: #ccfbf1; color: #0d9488; }

    .dash-panels {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 0.85rem;
        margin-bottom: 0.85rem;
    }

    @media (max-width: 992px) {
        .dash-panels { grid-template-columns: 1fr; }
    }

    .panel {
        background: #fff;
        border: 1px solid #e8ecf1;
        border-radius: 12px;
        padding: 1.1rem 1.25rem;
    }

    .panel h3 {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1a1d21;
        margin: 0 0 1rem;
    }

    .panel-chart { height: 260px; position: relative; }

    .portfolio-bar {
        display: flex;
        height: 14px;
        border-radius: 7px;
        overflow: hidden;
        background: #f1f3f5;
        margin-bottom: 0.75rem;
    }

    .portfolio-bar span { display: block; height: 100%; }

    .pipeline-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
    }

    .pipeline-item {
        background: #f8fafc;
        border: 1px solid #eef2f7;
        border-radius: 8px;
        padding: 0.65rem 0.75rem;
        text-align: center;
    }

    .pipeline-item .num {
        font-size: 1.25rem;
        font-weight: 700;
        color: #111827;
    }

    .pipeline-item .lbl {
        font-size: 0.7rem;
        color: #6c757d;
        text-transform: capitalize;
    }

    .sender-bars { display: flex; flex-direction: column; gap: 0.65rem; }

    .sender-row {
        display: grid;
        grid-template-columns: 80px 1fr 48px;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8rem;
    }

    .sender-track {
        height: 8px;
        background: #eef2f7;
        border-radius: 4px;
        overflow: hidden;
    }

    .sender-fill { height: 100%; border-radius: 4px; }

    .list-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.65rem 0;
        border-bottom: 1px solid #f1f3f5;
        font-size: 0.85rem;
    }

    .list-row:last-child { border-bottom: none; }

    .badge-status {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        text-transform: capitalize;
    }

    .badge-status.confirmed, .badge-status.completed, .badge-status.paid { background: #dcfce7; color: #166534; }
    .badge-status.pending, .badge-status.payment_pending { background: #fef3c7; color: #92400e; }
    .badge-status.cancelled { background: #fee2e2; color: #991b1b; }

    .dash-footnote {
        font-size: 0.75rem;
        color: #9ca3af;
        margin-top: 0.5rem;
    }

    .dash-bottom {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.85rem;
    }

    @media (max-width: 768px) {
        .dash-bottom { grid-template-columns: 1fr; }
    }
</style>
<script>document.body.classList.add('dashboard-page');</script>
@endsection

@section('content')
@php
    $m = $metrics;
    $statusLabels = [
        'pending' => 'Pendiente',
        'confirmed' => 'Confirmado',
        'completed' => 'Completado',
        'cancelled' => 'Cancelado',
        'payment_pending' => 'Pago pend.',
        'paid' => 'Pagado',
        'active' => 'Activo',
    ];
    $senderTotal = max(1, array_sum($m['messages_by_sender']));
@endphp

<div class="dash-wrap">
    <div class="dash-top">
        <div>
            <h1>Dashboard Ejecutivo</h1>
            <p>Resumen de WhatsApp · {{ $from->format('d/m/Y') }} — {{ $to->format('d/m/Y') }}</p>
        </div>
        <form class="dash-filter" method="get" action="{{ route('admin.dashboard') }}">
            <input type="hidden" name="period" id="period-preset" value="{{ $periodPreset ?? 'all' }}">
            <div class="btn-group btn-group-sm me-1" role="group" aria-label="Período rápido">
                @foreach(['7d' => '7 días', '30d' => '30 días', '90d' => '90 días', 'all' => 'Todo'] as $key => $label)
                    <button type="button"
                        class="btn {{ ($periodPreset ?? 'all') === $key ? 'btn-primary' : 'btn-outline-secondary' }} period-preset-btn"
                        data-period="{{ $key }}">{{ $label }}</button>
                @endforeach
            </div>
            <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" aria-label="Desde">
            <span style="color:#adb5bd">—</span>
            <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" aria-label="Hasta">
            <button type="submit"><i class="fas fa-filter me-1"></i> Filtrar</button>
        </form>
    </div>

    @if(($totalOrdersAllTime ?? 0) > 0 && ($metrics['orders_count'] ?? 0) === 0)
        <div class="alert alert-warning py-2 px-3 mb-3" style="font-size:0.88rem;border-radius:10px;">
            <i class="fas fa-exclamation-triangle me-1"></i>
            Hay <strong>{{ $totalOrdersAllTime }}</strong> pedido(s) en el sistema, pero ninguno en el período
            <strong>{{ $from->format('d/m/Y') }} — {{ $to->format('d/m/Y') }}</strong>.
            Usa el botón <strong>Todo</strong> o amplía las fechas.
        </div>
    @endif

    {{-- Fila principal KPIs --}}
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-icon green"><i class="fas fa-comments"></i></div>
            <div class="kpi-label">Mensajes en período</div>
            <div class="kpi-value">{{ number_format($m['period_messages']) }}</div>
            <div class="kpi-sub">Recibidos: {{ number_format($m['received']) }} · Enviados: {{ number_format($m['sent']) }}</div>
            <div class="kpi-trend {{ $m['message_growth'] >= 0 ? 'up' : 'down' }}">
                {{ $m['message_growth'] >= 0 ? '+' : '' }}{{ $m['message_growth'] }}% vs período anterior
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon blue"><i class="fas fa-reply"></i></div>
            <div class="kpi-label">Tasa de respuesta</div>
            <div class="kpi-value">{{ number_format($m['response_rate'], 1) }}%</div>
            <div class="kpi-sub">Clientes atendidos en 24 h</div>
            <div class="kpi-trend {{ $m['response_rate_growth'] >= 0 ? 'up' : 'down' }}">
                {{ $m['response_rate_growth'] >= 0 ? '+' : '' }}{{ $m['response_rate_growth'] }}% vs anterior
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon teal"><i class="fas fa-stopwatch"></i></div>
            <div class="kpi-label">Tiempo prom. respuesta</div>
            <div class="kpi-value">{{ $m['avg_response_time_formatted'] }}</div>
            <div class="kpi-sub">Desde mensaje del cliente</div>
            <div class="kpi-trend {{ $m['response_time_growth'] <= 0 ? 'up' : 'down' }}">
                {{ $m['response_time_growth'] >= 0 ? '+' : '' }}{{ $m['response_time_growth'] }}% vs anterior
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon purple"><i class="fas fa-users"></i></div>
            <div class="kpi-label">Clientes activos</div>
            <div class="kpi-value">{{ number_format($m['active_clients']) }}</div>
            <div class="kpi-sub">{{ number_format($m['new_contacts']) }} nuevos en período</div>
            <div class="kpi-trend {{ $m['active_clients_growth'] >= 0 ? 'up' : 'down' }}">
                {{ $m['active_clients_growth'] >= 0 ? '+' : '' }}{{ $m['active_clients_growth'] }}% vs anterior
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon green"><i class="fas fa-dollar-sign"></i></div>
            <div class="kpi-label">Ingresos pedidos</div>
            <div class="kpi-value">${{ number_format($m['total_revenue'], 0) }}</div>
            <div class="kpi-sub">{{ $m['orders_count'] }} pedidos · Ticket ${{ number_format($m['avg_order_value'], 0) }}</div>
            <div class="kpi-trend {{ $m['revenue_growth'] >= 0 ? 'up' : 'down' }}">
                {{ $m['revenue_growth'] >= 0 ? '+' : '' }}{{ $m['revenue_growth'] }}% vs anterior
            </div>
        </div>
    </div>

    {{-- Segunda fila KPIs secundarios --}}
    <div class="kpi-row kpi-row-6">
        <div class="kpi-card" style="min-height:90px">
            <div class="kpi-icon amber"><i class="fas fa-shopping-cart"></i></div>
            <div class="kpi-label">Pedidos</div>
            <div class="kpi-value" style="font-size:1.25rem">{{ number_format($m['orders_count']) }}</div>
        </div>
        <div class="kpi-card" style="min-height:90px">
            <div class="kpi-icon green"><i class="fas fa-check-circle"></i></div>
            <div class="kpi-label">Cobrado</div>
            <div class="kpi-value" style="font-size:1.25rem">${{ number_format($m['collected_revenue'], 0) }}</div>
            <div class="kpi-sub">{{ $m['collection_rate'] }}% del total</div>
        </div>
        <div class="kpi-card" style="min-height:90px">
            <div class="kpi-icon rose"><i class="fas fa-clock"></i></div>
            <div class="kpi-label">Pendiente</div>
            <div class="kpi-value" style="font-size:1.25rem">${{ number_format($m['pending_revenue'], 0) }}</div>
        </div>
        <div class="kpi-card" style="min-height:90px">
            <div class="kpi-icon blue"><i class="fas fa-percentage"></i></div>
            <div class="kpi-label">Conversión</div>
            <div class="kpi-value" style="font-size:1.25rem">{{ number_format($m['conversion_rate'], 1) }}%</div>
            <div class="kpi-sub">Pedidos / mensajes cliente</div>
        </div>
        <div class="kpi-card" style="min-height:90px">
            <div class="kpi-icon purple"><i class="fas fa-robot"></i></div>
            <div class="kpi-label">Bot / Humano</div>
            <div class="kpi-value" style="font-size:1.25rem">{{ number_format($m['bot_messages']) }} / {{ number_format($m['human_messages']) }}</div>
        </div>
        <div class="kpi-card" style="min-height:90px">
            <div class="kpi-icon teal"><i class="fas fa-clock"></i></div>
            <div class="kpi-label">Hora pico</div>
            <div class="kpi-value" style="font-size:1.25rem">{{ $m['peak_hour'] ?? '—' }}</div>
        </div>
    </div>

    <div class="dash-panels">
        <div class="panel">
            <h3>Actividad de mensajes</h3>
            <div class="panel-chart">
                <canvas id="messagesChart"></canvas>
            </div>
        </div>
        <div class="panel">
            <h3>Estado de ingresos</h3>
            @if($m['total_revenue'] > 0)
                <div class="portfolio-bar">
                    <span style="width:{{ $m['collection_rate'] }}%;background:#22c55e"></span>
                    <span style="width:{{ 100 - $m['collection_rate'] }}%;background:#f59e0b"></span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:0.8rem;margin-bottom:1rem">
                    <span><span style="color:#22c55e">●</span> Cobrado ${{ number_format($m['collected_revenue'], 0) }} ({{ $m['collection_rate'] }}%)</span>
                    <span><span style="color:#f59e0b">●</span> Pendiente ${{ number_format($m['pending_revenue'], 0) }}</span>
                </div>
            @else
                <p class="text-muted small mb-3">Sin ingresos en este período.</p>
            @endif

            <h3 style="margin-top:0.5rem">Pipeline de pedidos</h3>
            <div class="pipeline-grid">
                @forelse($ordersByStatus as $row)
                    <div class="pipeline-item">
                        <div class="num">{{ $row->total }}</div>
                        <div class="lbl">{{ $statusLabels[$row->status] ?? $row->status }}</div>
                    </div>
                @empty
                    <div class="pipeline-item" style="grid-column:1/-1">
                        <div class="lbl">Sin pedidos en el período</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="dash-panels">
        <div class="panel">
            <h3>Mensajes por remitente</h3>
            <div class="sender-bars">
                @foreach(['client' => ['Cliente', '#2563eb'], 'system' => ['Bot', '#25d366'], 'humano' => ['Humano', '#8b5cf6']] as $key => [$label, $color])
                    @php $count = $m['messages_by_sender'][$key] ?? 0; $pct = round(($count / $senderTotal) * 100); @endphp
                    <div class="sender-row">
                        <span>{{ $label }}</span>
                        <div class="sender-track">
                            <div class="sender-fill" style="width:{{ $pct }}%;background:{{ $color }}"></div>
                        </div>
                        <strong>{{ number_format($count) }}</strong>
                    </div>
                @endforeach
            </div>
            <p class="dash-footnote">Total histórico en sistema: {{ number_format($m['total_historical_messages']) }} mensajes · {{ number_format($m['total_contacts']) }} contactos</p>
        </div>
        <div class="panel">
            <h3>Tipos de mensaje</h3>
            <div class="panel-chart" style="height:220px">
                <canvas id="messageTypesChart"></canvas>
            </div>
        </div>
    </div>

    <div class="dash-bottom">
        <div class="panel">
            <h3>Últimos pedidos</h3>
            @forelse($orders as $order)
                <div class="list-row">
                    <div>
                        <strong>{{ $order->contact->name ?? 'Cliente' }}</strong>
                        <div class="text-muted" style="font-size:0.75rem">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="text-end">
                        <div><strong>${{ number_format($order->total, 2) }}</strong></div>
                        <span class="badge-status {{ $order->status }}">{{ $statusLabels[$order->status] ?? $order->status }}</span>
                    </div>
                </div>
            @empty
                <p class="text-muted small">No hay pedidos en este período.</p>
            @endforelse
            <a href="{{ route('admin.orders') }}" class="small text-decoration-none">Ver todos →</a>
        </div>
        <div class="panel">
            <h3>Actividad reciente</h3>
            @forelse($messages as $message)
                <div class="list-row">
                    <div style="flex:1;min-width:0">
                        <strong>{{ $message->contact->name ?? 'Cliente' }}</strong>
                        <div class="text-muted text-truncate" style="font-size:0.75rem;max-width:280px">
                            {{ \Illuminate\Support\Str::limit($message->content, 60) }}
                        </div>
                    </div>
                    <span class="text-muted" style="font-size:0.75rem;white-space:nowrap">{{ $message->created_at->format('d/m H:i') }}</span>
                </div>
            @empty
                <p class="text-muted small">No hay mensajes en este período.</p>
            @endforelse
            <a href="{{ route('admin.chats') }}" class="small text-decoration-none">Ir a chats →</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartOpts = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } }
    };

    const msgLabels = {!! json_encode($messagesData->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))) !!};
    new Chart(document.getElementById('messagesChart'), {
        type: 'line',
        data: {
            labels: msgLabels.length ? msgLabels : ['Sin datos'],
            datasets: [{
                label: 'Enviados',
                data: {!! json_encode($messagesData->pluck('sent')) !!},
                borderColor: '#25d366',
                backgroundColor: 'rgba(37,211,102,0.08)',
                tension: 0.35,
                fill: true
            }, {
                label: 'Recibidos',
                data: {!! json_encode($messagesData->pluck('received')) !!},
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,0.06)',
                tension: 0.35,
                fill: true
            }]
        },
        options: { ...chartOpts, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });

    const types = {!! json_encode($messageTypesDistribution) !!};
    const typeLabels = Object.keys(types).length ? Object.keys(types) : ['Sin datos'];
    const typeValues = Object.keys(types).length ? Object.values(types) : [1];
    new Chart(document.getElementById('messageTypesChart'), {
        type: 'doughnut',
        data: {
            labels: typeLabels,
            datasets: [{
                data: typeValues,
                backgroundColor: ['#25d366','#2563eb','#8b5cf6','#f59e0b','#94a3b8','#e11d48']
            }]
        },
        options: { ...chartOpts, cutout: '55%' }
    });

    document.querySelectorAll('.period-preset-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const form = this.closest('form');
            const periodInput = form.querySelector('#period-preset');
            if (periodInput) {
                periodInput.value = this.dataset.period;
            }
            form.querySelectorAll('input[name="from"], input[name="to"]').forEach(function(el) {
                el.removeAttribute('name');
            });
            form.submit();
        });
    });
});
</script>
@endsection
