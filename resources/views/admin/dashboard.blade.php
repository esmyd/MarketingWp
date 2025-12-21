@extends('admin.layouts.app')

@section('header')
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
    :root {
        --whatsapp-green: #25d366;
        --whatsapp-dark-green: #128C7E;
        --whatsapp-teal: #075E54;
        --whatsapp-light-green: #dcf8c6;
        --whatsapp-blue: #34B7F1;
        --whatsapp-gray: #8696a0;
        --whatsapp-dark-gray: #202c33;
    }

    body {
        background: linear-gradient(135deg, #e0f2e9 0%, #f0f9f5 100%) !important;
    }

    .dashboard-header {
        background: linear-gradient(135deg, var(--whatsapp-dark-green) 0%, var(--whatsapp-teal) 100%);
        color: white;
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .dashboard-header h1 {
        font-size: 2rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .dashboard-header .subtitle {
        font-size: 0.95rem;
        opacity: 0.9;
        margin-top: 0.5rem;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border-left: 4px solid var(--whatsapp-green);
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, rgba(37, 211, 102, 0.1) 0%, rgba(18, 140, 126, 0.05) 100%);
        border-radius: 50%;
        transform: translate(30px, -30px);
    }

    .stat-card.primary { border-left-color: var(--whatsapp-green); }
    .stat-card.success { border-left-color: #10b981; }
    .stat-card.info { border-left-color: var(--whatsapp-blue); }
    .stat-card.warning { border-left-color: #f59e0b; }
    .stat-card.danger { border-left-color: #ef4444; }
    .stat-card.purple { border-left-color: #8b5cf6; }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .stat-card.primary .stat-icon { background: rgba(37, 211, 102, 0.1); color: var(--whatsapp-green); }
    .stat-card.success .stat-icon { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .stat-card.info .stat-icon { background: rgba(52, 183, 241, 0.1); color: var(--whatsapp-blue); }
    .stat-card.warning .stat-icon { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
    .stat-card.danger .stat-icon { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    .stat-card.purple .stat-icon { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }

    .stat-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 0.5rem;
        line-height: 1.2;
    }

    .stat-change {
        font-size: 0.875rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .stat-change.positive { color: #10b981; }
    .stat-change.negative { color: #ef4444; }

    .chart-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 1.5rem;
    }

    .chart-card h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .chart-card h3 i {
        color: var(--whatsapp-green);
    }

    .info-tooltip {
        cursor: help;
        color: var(--whatsapp-gray);
        font-size: 0.875rem;
    }

    .metric-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin: 2rem 0 1.5rem 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .section-title::before {
        content: '';
        width: 4px;
        height: 24px;
        background: var(--whatsapp-green);
        border-radius: 2px;
    }

    .list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        border-bottom: 1px solid #e5e7eb;
        transition: background 0.2s;
    }

    .list-item:hover {
        background: #f9fafb;
    }

    .list-item:last-child {
        border-bottom: none;
    }

    .badge {
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .badge-success { background: #d1fae5; color: #065f46; }
    .badge-info { background: #dbeafe; color: #1e40af; }
    .badge-warning { background: #fef3c7; color: #92400e; }

    .progress-bar-container {
        background: #e5e7eb;
        border-radius: 8px;
        height: 8px;
        overflow: hidden;
        margin-top: 0.5rem;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, var(--whatsapp-green) 0%, var(--whatsapp-dark-green) 100%);
        border-radius: 8px;
        transition: width 0.3s ease;
    }

    .insight-card {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border: 1px solid #bbf7d0;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .insight-card h4 {
        color: #166534;
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .insight-card p {
        color: #15803d;
        font-size: 0.875rem;
        margin: 0;
    }
</style>
@endsection

@section('content')
<!-- Dashboard Header -->
<div class="dashboard-header">
    <h1>
        <i class="fab fa-whatsapp"></i>
        Dashboard de Marketing WhatsApp
    </h1>
    <div class="subtitle">
        <i class="fas fa-calendar-alt"></i>
        Período: Últimos 30 días |
        <i class="fas fa-clock"></i>
        Actualizado: {{ now()->format('d/m/Y H:i') }}
    </div>
</div>

<!-- KPIs Principales -->
<div class="section-title">
    <i class="fas fa-chart-line"></i>
    Indicadores Clave de Rendimiento (KPIs)
</div>

<div class="metric-grid">
    <!-- Total Mensajes -->
    <div class="stat-card primary">
        <div class="stat-icon">
            <i class="fas fa-comments"></i>
        </div>
        <div class="stat-label">
            Total Mensajes
            <span class="info-tooltip" title="Cantidad total de mensajes enviados y recibidos en el sistema">ℹ️</span>
        </div>
        <div class="stat-value">
            {{ $totalMessages > 0 ? number_format($totalMessages) : '0' }}
        </div>
        <div class="stat-change {{ $messageGrowth >= 0 ? 'positive' : 'negative' }}">
            <i class="fas fa-arrow-{{ $messageGrowth >= 0 ? 'up' : 'down' }}"></i>
            {{ $messageGrowth >= 0 ? '+' : '' }}{{ number_format($messageGrowth, 1) }}% vs mes anterior
        </div>
    </div>

    <!-- Tasa de Respuesta -->
    <div class="stat-card success">
        <div class="stat-icon">
            <i class="fas fa-reply"></i>
        </div>
        <div class="stat-label">
            Tasa de Respuesta
            <span class="info-tooltip" title="Porcentaje de mensajes de clientes que recibieron respuesta del sistema">ℹ️</span>
        </div>
        <div class="stat-value">
            {{ $responseRate > 0 ? number_format(min($responseRate, 100), 1) : '0' }}<span style="font-size: 1.25rem;">%</span>
        </div>
        <div class="stat-change {{ $responseRateGrowth >= 0 ? 'positive' : 'negative' }}">
            <i class="fas fa-arrow-{{ $responseRateGrowth >= 0 ? 'up' : 'down' }}"></i>
            {{ $responseRateGrowth >= 0 ? '+' : '' }}{{ number_format($responseRateGrowth, 1) }}% vs mes anterior
        </div>
        <div class="progress-bar-container">
            <div class="progress-bar" style="width: {{ min($responseRate, 100) }}%"></div>
        </div>
    </div>

    <!-- Tiempo Promedio Respuesta -->
    <div class="stat-card info">
        <div class="stat-icon">
            <i class="fas fa-stopwatch"></i>
        </div>
        <div class="stat-label">
            Tiempo Promedio Respuesta
            <span class="info-tooltip" title="Promedio de minutos que tarda el sistema en responder a un mensaje de cliente">ℹ️</span>
        </div>
        <div class="stat-value">
            @php
                $min = $avgResponseTime;
                if($min > 0) {
                    $h = floor($min / 60);
                    $m = round($min % 60);
                    echo $h > 0 ? "$h h $m m" : "$m m";
                } else {
                    echo '0 m';
                }
            @endphp
        </div>
        <div class="stat-change {{ $responseTimeGrowth <= 0 ? 'positive' : 'negative' }}">
            <i class="fas fa-arrow-{{ $responseTimeGrowth <= 0 ? 'down' : 'up' }}"></i>
            {{ $responseTimeGrowth >= 0 ? '+' : '' }}{{ number_format($responseTimeGrowth, 1) }}% vs mes anterior
        </div>
    </div>

    <!-- Clientes Activos -->
    <div class="stat-card warning">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-label">
            Clientes Activos
            <span class="info-tooltip" title="Cantidad de contactos únicos que han enviado o recibido al menos un mensaje en los últimos 30 días">ℹ️</span>
        </div>
        <div class="stat-value">
            {{ $activeClients > 0 ? number_format($activeClients) : '0' }}
        </div>
        <div class="stat-change {{ $activeClientsGrowth >= 0 ? 'positive' : 'negative' }}">
            <i class="fas fa-arrow-{{ $activeClientsGrowth >= 0 ? 'up' : 'down' }}"></i>
            {{ $activeClientsGrowth >= 0 ? '+' : '' }}{{ number_format($activeClientsGrowth, 1) }}% vs mes anterior
        </div>
    </div>

    <!-- Tasa de Interacción -->
    <div class="stat-card purple">
        <div class="stat-icon">
            <i class="fas fa-hand-pointer"></i>
        </div>
        <div class="stat-label">
            Tasa de Interacción
            <span class="info-tooltip" title="Porcentaje de mensajes enviados por el sistema que recibieron respuesta del cliente">ℹ️</span>
        </div>
        <div class="stat-value">
            {{ $interactionRate > 0 ? number_format(min($interactionRate, 100), 1) : '0' }}<span style="font-size: 1.25rem;">%</span>
        </div>
        <div class="stat-change {{ $interactionRateGrowth >= 0 ? 'positive' : 'negative' }}">
            <i class="fas fa-arrow-{{ $interactionRateGrowth >= 0 ? 'up' : 'down' }}"></i>
            {{ $interactionRateGrowth >= 0 ? '+' : '' }}{{ number_format($interactionRateGrowth, 1) }}% vs mes anterior
        </div>
        <div class="progress-bar-container">
            <div class="progress-bar" style="width: {{ min($interactionRate, 100) }}%"></div>
        </div>
    </div>

    <!-- Tasa de Respuesta del Cliente -->
    <div class="stat-card success">
        <div class="stat-icon">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="stat-label">
            Tasa de Respuesta del Cliente
            <span class="info-tooltip" title="Porcentaje de mensajes del sistema que recibieron respuesta del cliente en 24 horas">ℹ️</span>
        </div>
        <div class="stat-value">
            {{ isset($clientResponseRate) && $clientResponseRate > 0 ? number_format(min($clientResponseRate, 100), 1) : '0' }}<span style="font-size: 1.25rem;">%</span>
        </div>
        <div class="progress-bar-container">
            <div class="progress-bar" style="width: {{ min(isset($clientResponseRate) ? $clientResponseRate : 0, 100) }}%"></div>
        </div>
    </div>
</div>

<!-- Métricas de Negocio -->
<div class="section-title">
    <i class="fas fa-briefcase"></i>
    Métricas de Negocio
</div>

<div class="metric-grid">
    <!-- Clientes Nuevos -->
    <div class="stat-card info">
        <div class="stat-icon">
            <i class="fas fa-user-plus"></i>
        </div>
        <div class="stat-label">
            Clientes Nuevos (Este Mes)
        </div>
        <div class="stat-value">
            {{ $newClientsThisMonth > 0 ? number_format($newClientsThisMonth) : '0' }}
        </div>
    </div>

    <!-- Pedidos Este Mes -->
    <div class="stat-card warning">
        <div class="stat-icon">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-label">
            Pedidos (Este Mes)
        </div>
        <div class="stat-value">
            {{ $ordersThisMonth > 0 ? number_format($ordersThisMonth) : '0' }}
        </div>
    </div>

    <!-- Tasa de Conversión -->
    <div class="stat-card success">
        <div class="stat-icon">
            <i class="fas fa-percentage"></i>
        </div>
        <div class="stat-label">
            Tasa de Conversión
            <span class="info-tooltip" title="Porcentaje de mensajes de clientes que resultaron en pedidos">ℹ️</span>
        </div>
        <div class="stat-value">
            {{ isset($conversionRate) && $conversionRate > 0 ? number_format(min($conversionRate, 100), 2) : '0' }}<span style="font-size: 1.25rem;">%</span>
        </div>
        <div class="progress-bar-container">
            <div class="progress-bar" style="width: {{ min(isset($conversionRate) ? $conversionRate : 0, 100) }}%"></div>
        </div>
    </div>

    <!-- Ingresos Totales -->
    <div class="stat-card primary">
        <div class="stat-icon">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-label">
            Ingresos Totales (Este Mes)
        </div>
        <div class="stat-value">
            ${{ isset($totalRevenue) && $totalRevenue > 0 ? number_format($totalRevenue, 2) : '0.00' }}
        </div>
    </div>

    <!-- Valor Promedio Pedido -->
    <div class="stat-card purple">
        <div class="stat-icon">
            <i class="fas fa-receipt"></i>
        </div>
        <div class="stat-label">
            Valor Promedio Pedido
        </div>
        <div class="stat-value">
            ${{ isset($avgOrderValue) && $avgOrderValue > 0 ? number_format($avgOrderValue, 2) : '0.00' }}
        </div>
    </div>

    <!-- Mensajes Enviados vs Recibidos -->
    <div class="stat-card info">
        <div class="stat-icon">
            <i class="fas fa-exchange-alt"></i>
        </div>
        <div class="stat-label">
            Ratio Enviados/Recibidos
            <span class="info-tooltip" title="Relación entre mensajes enviados y recibidos">ℹ️</span>
        </div>
        <div class="stat-value">
            {{ isset($sentReceivedRatio) && $sentReceivedRatio > 0 ? number_format($sentReceivedRatio, 2) : '0' }}:1
        </div>
        <div style="font-size: 0.875rem; color: #6b7280; margin-top: 0.5rem;">
            Enviados: {{ isset($sentMessages) ? number_format($sentMessages) : '0' }} |
            Recibidos: {{ isset($receivedMessages) ? number_format($receivedMessages) : '0' }}
        </div>
    </div>
</div>

<!-- Métricas de Iteraciones -->
<div class="section-title">
    <i class="fas fa-sync-alt"></i>
    Métricas de Iteraciones
</div>

<div class="metric-grid">
    <!-- Iteraciones por Cliente -->
    <div class="stat-card primary">
        <div class="stat-icon">
            <i class="fas fa-user-friends"></i>
        </div>
        <div class="stat-label">
            Iteraciones por Cliente
            <span class="info-tooltip" title="Promedio de mensajes recibidos por cliente activo">ℹ️</span>
        </div>
        <div class="stat-value">
            {{ isset($iterationsPerClient) && $iterationsPerClient > 0 ? number_format($iterationsPerClient, 2) : '0' }}
        </div>
        <div style="font-size: 0.875rem; color: #6b7280; margin-top: 0.5rem;">
            {{ isset($receivedMessages) ? number_format($receivedMessages) : '0' }} mensajes /
            {{ $activeClients > 0 ? number_format($activeClients) : '0' }} clientes
        </div>
    </div>

    <!-- Iteraciones por Humano -->
    <div class="stat-card warning">
        <div class="stat-icon">
            <i class="fas fa-user-tie"></i>
        </div>
        <div class="stat-label">
            Iteraciones por Humano
            <span class="info-tooltip" title="Promedio de mensajes enviados por humano por cliente atendido">ℹ️</span>
        </div>
        <div class="stat-value">
            {{ isset($iterationsPerHuman) && $iterationsPerHuman > 0 ? number_format($iterationsPerHuman, 2) : '0' }}
        </div>
        <div style="font-size: 0.875rem; color: #6b7280; margin-top: 0.5rem;">
            {{ isset($humanMessages) ? number_format($humanMessages) : '0' }} mensajes /
            {{ isset($clientsWithHumanMessages) && $clientsWithHumanMessages > 0 ? number_format($clientsWithHumanMessages) : '0' }} clientes
        </div>
    </div>

    <!-- Distribución de Mensajes por Tipo -->
    <div class="stat-card purple">
        <div class="stat-icon">
            <i class="fas fa-chart-pie"></i>
        </div>
        <div class="stat-label">
            Distribución de Mensajes
            <span class="info-tooltip" title="Distribución de mensajes por tipo de remitente">ℹ️</span>
        </div>
        <div style="margin-top: 1rem;">
            @if(isset($messagesBySenderType))
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span style="font-size: 0.875rem; color: #6b7280;">Clientes:</span>
                    <span style="font-weight: 600;">{{ number_format($messagesBySenderType['client'] ?? 0) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span style="font-size: 0.875rem; color: #6b7280;">Sistema:</span>
                    <span style="font-weight: 600;">{{ number_format($messagesBySenderType['system'] ?? 0) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="font-size: 0.875rem; color: #6b7280;">Humanos:</span>
                    <span style="font-weight: 600;">{{ number_format($messagesBySenderType['humano'] ?? 0) }}</span>
                </div>
            @else
                <span style="font-size: 0.875rem; color: #9ca3af;">Sin datos</span>
            @endif
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="section-title">
    <i class="fas fa-chart-bar"></i>
    Análisis Visual
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Actividad de Mensajes -->
    <div class="chart-card">
        <h3>
            <i class="fas fa-chart-line"></i>
            Actividad de Mensajes (Últimos 30 días)
        </h3>
        <div style="height: 300px; position: relative;">
            <canvas id="messagesChart"></canvas>
        </div>
    </div>

    <!-- Tiempo de Respuesta -->
    <div class="chart-card">
        <h3>
            <i class="fas fa-clock"></i>
            Tiempo de Respuesta Promedio
        </h3>
        <div style="height: 300px; position: relative;">
            <canvas id="responseTimeChart"></canvas>
        </div>
    </div>

    <!-- Distribución de Tipos de Mensajes -->
    <div class="chart-card">
        <h3>
            <i class="fas fa-pie-chart"></i>
            Distribución de Tipos de Mensajes
        </h3>
        <div style="height: 300px; position: relative;">
            <canvas id="messageTypesChart"></canvas>
        </div>
    </div>

    <!-- Mensajes por Día de la Semana -->
    <div class="chart-card">
        <h3>
            <i class="fas fa-calendar-week"></i>
            Actividad por Día de la Semana
        </h3>
        <div style="height: 300px; position: relative;">
            <canvas id="weekdayChart"></canvas>
        </div>
    </div>

    <!-- Mensajes por Hora -->
    <div class="chart-card" style="grid-column: 1 / -1;">
        <h3>
            <i class="fas fa-clock"></i>
            Mensajes por Hora del Día (Este Mes)
        </h3>
        <div style="height: 300px; position: relative;">
            <canvas id="messagesByHourChart"></canvas>
        </div>
    </div>
</div>

<!-- Insights y Recomendaciones -->
@if(isset($peakHour) && $peakHour !== 'N/A')
<div class="insight-card">
    <h4>
        <i class="fas fa-lightbulb"></i>
        Insight de Actividad
    </h4>
    <p>
        <strong>Hora pico:</strong> {{ $peakHour }} |
        <strong>Día más activo:</strong> {{ isset($peakDay) ? $peakDay : 'N/A' }} |
        <strong>Recomendación:</strong> Programar campañas y respuestas automáticas durante estos períodos para maximizar el engagement.
    </p>
</div>
@endif

<!-- Top Rankings -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <!-- Productos más pedidos -->
    <div class="chart-card">
        <h3>
            <i class="fas fa-box"></i>
            Top 5 Productos Más Pedidos (Este Mes)
        </h3>
        <div>
            @forelse($topProducts as $index => $prod)
                <div class="list-item">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span class="badge badge-info" style="min-width: 32px; text-align: center;">{{ $index + 1 }}</span>
                        <span style="font-weight: 500;">{{ $prod->name }}</span>
                    </div>
                    <span class="badge badge-success">{{ $prod->total }} unidades</span>
                </div>
            @empty
                <div class="list-item">
                    <span class="text-gray-500">Sin datos disponibles</span>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Opciones más consultadas -->
    <div class="chart-card">
        <h3>
            <i class="fas fa-list"></i>
            Top 5 Opciones Más Consultadas (Este Mes)
        </h3>
        <div>
            @forelse($topOptions as $index => $opt)
                <div class="list-item">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span class="badge badge-info" style="min-width: 32px; text-align: center;">{{ $index + 1 }}</span>
                        <span style="font-weight: 500;">{{ $opt->type }}</span>
                    </div>
                    <span class="badge badge-warning">{{ $opt->total }} consultas</span>
                </div>
            @empty
                <div class="list-item">
                    <span class="text-gray-500">Sin datos disponibles</span>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Actividad Reciente -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Últimos Pedidos -->
    <div class="chart-card">
        <h3>
            <i class="fas fa-shopping-bag"></i>
            Últimos Pedidos
        </h3>
        <div>
            @forelse($orders as $order)
                <div class="list-item">
                    <div>
                        <div style="font-weight: 600; color: #111827;">{{ $order->contact->name ?? 'Cliente' }}</div>
                        <div style="font-size: 0.875rem; color: #6b7280;">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <span class="badge {{ $order->status === 'completed' ? 'badge-success' : 'badge-warning' }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
            @empty
                <div class="list-item">
                    <span class="text-gray-500">No hay pedidos recientes</span>
                </div>
            @endforelse
        </div>
        <a href="{{ route('admin.orders') }}" style="display: inline-block; margin-top: 1rem; color: var(--whatsapp-green); font-weight: 600; text-decoration: none;">
            Ver todos los pedidos <i class="fas fa-arrow-right"></i>
        </a>
    </div>

    <!-- Últimos Mensajes -->
    <div class="chart-card">
        <h3>
            <i class="fas fa-comment-dots"></i>
            Últimos Mensajes
        </h3>
        <div>
            @forelse($messages as $message)
                <div class="list-item">
                    <div style="flex: 1;">
                        <div style="font-weight: 600; color: #111827;">{{ $message->contact->name ?? 'Cliente' }}</div>
                        @php
                            $content = $message->content;
                            $decoded = null;
                            try {
                                $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
                            } catch (\Throwable $e) {
                                $decoded = null;
                            }
                        @endphp
                        @if(is_array($decoded) && isset($decoded['title']))
                            <div style="font-size: 0.875rem; color: #374151; font-weight: 500; margin-top: 0.25rem;">{{ $decoded['title'] }}</div>
                            @if(isset($decoded['description']))
                                <div style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">{{ \Illuminate\Support\Str::limit($decoded['description'], 60) }}</div>
                            @endif
                        @else
                            <div style="font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem;">{{ \Illuminate\Support\Str::limit($content, 80) }}</div>
                        @endif
                        <div style="font-size: 0.75rem; color: #9ca3af; margin-top: 0.25rem;">{{ $message->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            @empty
                <div class="list-item">
                    <span class="text-gray-500">No hay mensajes recientes</span>
                </div>
            @endforelse
        </div>
        <a href="{{ route('admin.messages') }}" style="display: inline-block; margin-top: 1rem; color: var(--whatsapp-green); font-weight: 600; text-decoration: none;">
            Ver todos los mensajes <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    font: { size: 12 },
                    padding: 15
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                titleFont: { size: 14 },
                bodyFont: { size: 12 }
            }
        }
    };

    // Messages Activity Chart
    const messagesCtx = document.getElementById('messagesChart').getContext('2d');
    new Chart(messagesCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($messagesData->pluck('date')->map(function($date) { return \Carbon\Carbon::parse($date)->format('d/m'); })) !!},
            datasets: [{
                label: 'Mensajes Enviados',
                data: {!! json_encode($messagesData->pluck('sent')) !!},
                borderColor: '#25d366',
                backgroundColor: 'rgba(37, 211, 102, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6
            }, {
                label: 'Mensajes Recibidos',
                data: {!! json_encode($messagesData->pluck('received')) !!},
                borderColor: '#128C7E',
                backgroundColor: 'rgba(18, 140, 126, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            ...commonOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });

    // Response Time Chart
    const responseCtx = document.getElementById('responseTimeChart').getContext('2d');
    new Chart(responseCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($responseTimeData->pluck('date')->map(function($date) { return \Carbon\Carbon::parse($date)->format('d/m'); })) !!},
            datasets: [{
                label: 'Tiempo de Respuesta (min)',
                data: {!! json_encode($responseTimeData->pluck('avg_time')) !!},
                backgroundColor: 'rgba(37, 211, 102, 0.8)',
                borderColor: '#25d366',
                borderWidth: 2
            }]
        },
        options: {
            ...commonOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 1 }
                }
            }
        }
    });

    // Message Types Chart
    const messageTypesData = {!! json_encode(isset($messageTypesDistribution) ? $messageTypesDistribution : []) !!};
    const messageTypesLabels = Object.keys(messageTypesData);
    const messageTypesValues = Object.values(messageTypesData);
    const totalTypes = messageTypesValues.reduce((a, b) => a + b, 0);

    const messageTypesCtx = document.getElementById('messageTypesChart').getContext('2d');
    new Chart(messageTypesCtx, {
        type: 'doughnut',
        data: {
            labels: messageTypesLabels.length > 0 ? messageTypesLabels : ['Sin datos'],
            datasets: [{
                data: messageTypesValues.length > 0 ? messageTypesValues : [1],
                backgroundColor: [
                    '#25d366',
                    '#128C7E',
                    '#34B7F1',
                    '#075E54',
                    '#dcf8c6',
                    '#8696a0'
                ]
            }]
        },
        options: {
            ...commonOptions,
            cutout: '60%',
            plugins: {
                ...commonOptions.plugins,
                legend: {
                    ...commonOptions.plugins.legend,
                    position: 'bottom'
                }
            }
        }
    });

    // Weekday Chart
    @if(isset($messagesByWeekday))
    const weekdayCtx = document.getElementById('weekdayChart').getContext('2d');
    new Chart(weekdayCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(collect($messagesByWeekday)->pluck('day')) !!},
            datasets: [{
                label: 'Mensajes',
                data: {!! json_encode(collect($messagesByWeekday)->pluck('count')) !!},
                backgroundColor: 'rgba(52, 183, 241, 0.8)',
                borderColor: '#34B7F1',
                borderWidth: 2
            }]
        },
        options: {
            ...commonOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });
    @endif

    // Messages By Hour Chart
    const messagesByHourCtx = document.getElementById('messagesByHourChart').getContext('2d');
    new Chart(messagesByHourCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($messagesByHour->pluck('hour')->map(function($h) { return $h . ':00'; })) !!},
            datasets: [{
                label: 'Mensajes',
                data: {!! json_encode($messagesByHour->pluck('total')) !!},
                backgroundColor: 'rgba(18, 140, 126, 0.8)',
                borderColor: '#128C7E',
                borderWidth: 2
            }]
        },
        options: {
            ...commonOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });
});
</script>
@endsection
