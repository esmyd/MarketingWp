@extends('admin.layouts.app')

@section('header', 'Clientes')

@section('content')
@php
    $statusLabels = [
        'pending' => 'Pendiente',
        'confirmed' => 'Confirmado',
        'completed' => 'Completado',
        'cancelled' => 'Cancelado',
        'payment_pending' => 'Pago pend.',
        'paid' => 'Pagado',
    ];
    $fmt = fn ($dt) => $dt ? \Carbon\Carbon::parse($dt)->format('d/m/Y H:i') : '—';
@endphp

<style>
    .clients-page { --wa-dark: #128c7e; --wa-teal: #075e54; }

    .clients-hero {
        background: linear-gradient(135deg, var(--wa-dark) 0%, var(--wa-teal) 100%);
        color: #fff;
        border-radius: 14px;
        padding: 1.5rem 1.75rem;
        margin-bottom: 1.25rem;
    }

    .clients-hero h2 { font-size: 1.35rem; font-weight: 600; margin: 0 0 .25rem; }
    .clients-hero p { margin: 0; opacity: .9; font-size: .9rem; }

    .clients-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: .75rem;
        margin-bottom: 1.25rem;
    }

    .clients-stat {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 1rem;
    }

    .clients-stat .lbl { font-size: .72rem; color: #6c757d; text-transform: uppercase; letter-spacing: .04em; }
    .clients-stat .val { font-size: 1.35rem; font-weight: 700; color: #212529; margin-top: .2rem; }

    .clients-filters {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 1rem 1.15rem;
        margin-bottom: 1rem;
    }

    .clients-filters .row { row-gap: .75rem; }

    .clients-table-wrap {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        overflow: hidden;
    }

    .clients-table { margin: 0; font-size: .84rem; }
    .clients-table thead th {
        background: #f8f9fa;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #6c757d;
        border-bottom: 1px solid #e9ecef;
        white-space: nowrap;
    }

    .clients-table tbody td { vertical-align: middle; }
    .clients-table .client-name { font-weight: 600; color: #212529; }
    .clients-table .client-phone { font-size: .78rem; color: #6c757d; }

    .client-badges { display: flex; flex-wrap: wrap; gap: .25rem; margin-top: .35rem; }
    .client-badge {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        font-size: .65rem;
        font-weight: 600;
        padding: .15rem .45rem;
        border-radius: 999px;
        white-space: nowrap;
    }

    .client-badge.green { background: #dcfce7; color: #166534; }
    .client-badge.purple { background: #f3e8ff; color: #6b21a8; }
    .client-badge.blue { background: #dbeafe; color: #1e40af; }
    .client-badge.red { background: #fee2e2; color: #991b1b; }
    .client-badge.amber { background: #fef3c7; color: #92400e; }
    .client-badge.gray { background: #f3f4f6; color: #4b5563; }
    .client-badge.orange { background: #ffedd5; color: #c2410c; }
    .client-badge.slate { background: #e2e8f0; color: #475569; }
    .client-badge.teal { background: #ccfbf1; color: #115e59; }

    .metric-pill {
        display: inline-block;
        min-width: 2rem;
        text-align: center;
        font-weight: 700;
        color: #111827;
    }

    .dt-cell { font-size: .78rem; color: #374151; line-height: 1.35; }
    .dt-cell .lbl { color: #9ca3af; font-size: .68rem; text-transform: uppercase; }

    .clients-actions { display: flex; flex-wrap: wrap; gap: .35rem; }
    .clients-actions .btn { font-size: .75rem; padding: .3rem .55rem; }
</style>

<div class="clients-page">
    <div class="clients-hero">
        <h2><i class="fas fa-users me-2"></i>Clientes</h2>
        <p>Trazabilidad de conversaciones y compras · ordenado por actividad más reciente</p>
    </div>

    <div class="clients-stats">
        <div class="clients-stat">
            <div class="lbl">Total clientes</div>
            <div class="val">{{ number_format($summary['total']) }}</div>
        </div>
        <div class="clients-stat">
            <div class="lbl">Activos (7 días)</div>
            <div class="val">{{ number_format($summary['active_7d']) }}</div>
        </div>
        <div class="clients-stat">
            <div class="lbl">Compradores frecuentes</div>
            <div class="val">{{ number_format($summary['frequent_buyers']) }}</div>
        </div>
        <div class="clients-stat">
            <div class="lbl">Requieren agente</div>
            <div class="val">{{ number_format($summary['needs_agent']) }}</div>
        </div>
    </div>

    <form class="clients-filters" method="get" action="{{ route('admin.clients.index') }}">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Buscar</label>
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Nombre o teléfono" value="{{ $filters['q'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Segmento</label>
                <select name="segment" class="form-select form-select-sm">
                    @foreach($segments as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['segment'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Ordenar</label>
                <select name="sort" class="form-select form-select-sm">
                    @foreach($sortOptions as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['sort'] ?? 'recent') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Actividad desde</label>
                <input type="date" name="activity_from" class="form-control form-control-sm" value="{{ $filters['activity_from'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Actividad hasta</label>
                <input type="date" name="activity_to" class="form-control form-control-sm" value="{{ $filters['activity_to'] ?? '' }}">
            </div>
            <div class="col-md-1">
                <label class="form-label small mb-1">Min. pedidos</label>
                <input type="number" name="min_orders" min="0" class="form-control form-control-sm" value="{{ $filters['min_orders'] ?? '' }}">
            </div>
            <div class="col-md-12 col-lg-auto d-flex gap-2">
                <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-filter me-1"></i>Filtrar</button>
                <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
            </div>
        </div>
    </form>

    <div class="clients-table-wrap">
        <div class="table-responsive">
            <table class="table clients-table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th class="text-center">Pedidos</th>
                        <th class="text-center">Msj. cliente</th>
                        <th class="text-center">Respuestas</th>
                        <th>Último msj. cliente</th>
                        <th>Última respuesta</th>
                        <th>Gasto</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        @php $badges = $insights->indicators($client); @endphp
                        <tr>
                            <td>
                                <div class="client-name">{{ $client->name ?: 'Sin nombre' }}</div>
                                <div class="client-phone">{{ $client->phone_number }}</div>
                                @if($badges)
                                    <div class="client-badges">
                                        @foreach(array_slice($badges, 0, 3) as $badge)
                                            <span class="client-badge {{ $badge['tone'] }}">
                                                <i class="fas {{ $badge['icon'] }}"></i>{{ $badge['label'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="text-center"><span class="metric-pill">{{ $client->orders_count ?? 0 }}</span></td>
                            <td class="text-center"><span class="metric-pill">{{ $client->client_messages_count ?? 0 }}</span></td>
                            <td class="text-center"><span class="metric-pill">{{ $client->replied_messages_count ?? 0 }}</span></td>
                            <td class="dt-cell">{{ $fmt($client->last_client_message_at) }}</td>
                            <td class="dt-cell">{{ $fmt($client->last_reply_message_at) }}</td>
                            <td>${{ number_format((float) ($client->total_spent ?? 0), 2) }}</td>
                            <td>
                                <div class="clients-actions">
                                    @perm('chats.open')
                                    <a href="{{ route('admin.chat', $client->id) }}" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-comments"></i> Chat
                                    </a>
                                    @endperm
                                    <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i> Detalle
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                No hay clientes que coincidan con los filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($clients->hasPages())
            <div class="p-3 border-top">{{ $clients->links() }}</div>
        @endif
    </div>
</div>
@endsection
