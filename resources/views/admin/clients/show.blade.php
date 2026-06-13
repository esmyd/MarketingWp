@extends('admin.layouts.app')

@section('header', 'Detalle del cliente')

@section('content')
@php
    use App\Helpers\WhatsappMessageFormatter;

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
    .client-detail { max-width: 1100px; margin: 0 auto; }
    .client-back { margin-bottom: 1rem; }
    .client-header {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 14px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1rem;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: space-between;
        align-items: flex-start;
    }
    .client-header h2 { margin: 0 0 .25rem; font-size: 1.35rem; font-weight: 700; }
    .client-header .phone { color: #6c757d; font-size: .9rem; }
    .client-badges { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .65rem; }
    .client-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .72rem; font-weight: 600; padding: .25rem .55rem; border-radius: 999px;
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

    .detail-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: .75rem;
        margin-bottom: 1rem;
    }
    .detail-stat {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 1rem;
    }
    .detail-stat .lbl { font-size: .72rem; color: #6c757d; text-transform: uppercase; }
    .detail-stat .val { font-size: 1.25rem; font-weight: 700; margin-top: .2rem; color: #111827; }
    .detail-stat .sub { font-size: .75rem; color: #9ca3af; margin-top: .15rem; }

    .detail-panel {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 1.1rem 1.25rem;
        margin-bottom: 1rem;
    }
    .detail-panel h3 { font-size: .95rem; font-weight: 600; margin: 0 0 1rem; }

    .timeline { max-height: 420px; overflow-y: auto; }
    .timeline-item {
        display: flex;
        gap: .75rem;
        padding: .65rem 0;
        border-bottom: 1px solid #f1f3f5;
        font-size: .84rem;
    }
    .timeline-item:last-child { border-bottom: none; }
    .timeline-icon {
        width: 28px; height: 28px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: .7rem; flex-shrink: 0;
    }
    .timeline-icon.client { background: #dbeafe; color: #1d4ed8; }
    .timeline-icon.system { background: #dcfce7; color: #15803d; }
    .timeline-icon.humano { background: #fef3c7; color: #b45309; }
    .timeline-meta { font-size: .72rem; color: #9ca3af; margin-top: .15rem; }

    .orders-mini-table { font-size: .84rem; }
    .orders-mini-table th { font-size: .72rem; text-transform: uppercase; color: #6c757d; }
</style>

<div class="client-detail">
    <div class="client-back">
        <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Volver a clientes
        </a>
    </div>

    <div class="client-header">
        <div>
            <h2>{{ $contact->name ?: 'Sin nombre' }}</h2>
            <div class="phone"><i class="fas fa-phone me-1"></i>{{ $contact->phone_number }}</div>
            <div class="phone mt-1">
                Cliente desde {{ $contact->created_at?->format('d/m/Y') ?? '—' }}
                · Bot {{ $contact->bot_enabled ? 'activo' : 'pausado' }}
            </div>
            @if($indicators)
                <div class="client-badges">
                    @foreach($indicators as $badge)
                        <span class="client-badge {{ $badge['tone'] }}">
                            <i class="fas {{ $badge['icon'] }}"></i>{{ $badge['label'] }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="d-flex flex-wrap gap-2">
            @perm('chats.open')
            <a href="{{ route('admin.chat', $contact->id) }}" class="btn btn-success">
                <i class="fas fa-comments me-1"></i> Ir al chat
            </a>
            @endperm
        </div>
    </div>

    <div class="detail-stats">
        <div class="detail-stat">
            <div class="lbl">Pedidos</div>
            <div class="val">{{ $contact->orders_count ?? 0 }}</div>
            <div class="sub">{{ $contact->recent_orders_count ?? 0 }} en últimos 90 días</div>
        </div>
        <div class="detail-stat">
            <div class="lbl">Mensajes del cliente</div>
            <div class="val">{{ $contact->client_messages_count ?? 0 }}</div>
        </div>
        <div class="detail-stat">
            <div class="lbl">Respuestas (bot + humano)</div>
            <div class="val">{{ $contact->replied_messages_count ?? 0 }}</div>
            <div class="sub">Ratio {{ number_format($response_rate, 1) }}%</div>
        </div>
        <div class="detail-stat">
            <div class="lbl">Total comprado</div>
            <div class="val">${{ number_format((float) ($contact->total_spent ?? 0), 2) }}</div>
            @if(($contact->orders_count ?? 0) === 0)
                <div class="sub">Sin pedidos cerrados</div>
            @endif
        </div>
        <div class="detail-stat">
            <div class="lbl">Último mensaje cliente</div>
            <div class="val" style="font-size:1rem;">{{ $fmt($contact->last_client_message_at) }}</div>
        </div>
        <div class="detail-stat">
            <div class="lbl">Última respuesta</div>
            <div class="val" style="font-size:1rem;">{{ $fmt($contact->last_reply_message_at) }}</div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="detail-panel">
                <h3><i class="fas fa-shopping-cart me-1 text-success"></i> Pedidos recientes</h3>
                @if($orders->isEmpty())
                    <p class="text-muted mb-0">Este cliente aún no tiene pedidos registrados.</p>
                @else
                    <div class="table-responsive">
                        <table class="table orders-mini-table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                        <td>{{ $order->id }}</td>
                                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $statusLabels[$order->status] ?? $order->status }}</td>
                                        <td class="text-end">${{ number_format((float) $order->total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-6">
            <div class="detail-panel">
                <h3><i class="fas fa-chart-bar me-1 text-primary"></i> Actividad (6 meses)</h3>
                @if($messages_by_month->isEmpty())
                    <p class="text-muted mb-0">Sin actividad registrada.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0" style="font-size:.84rem;">
                            <thead>
                                <tr>
                                    <th>Mes</th>
                                    <th class="text-center">Cliente</th>
                                    <th class="text-center">Respuestas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($messages_by_month as $row)
                                    <tr>
                                        <td>{{ $row->month }}</td>
                                        <td class="text-center">{{ $row->inbound }}</td>
                                        <td class="text-center">{{ $row->outbound }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="detail-panel">
        <h3><i class="fas fa-history me-1"></i> Trazabilidad — últimos mensajes</h3>
        <div class="timeline">
            @forelse($recent_messages as $message)
                @php
                    $iconClass = match($message->sender_type) {
                        'client' => 'client',
                        'humano' => 'humano',
                        default => 'system',
                    };
                    $senderLabel = match($message->sender_type) {
                        'client' => 'Cliente',
                        'humano' => 'Humano',
                        default => 'Bot',
                    };
                    $preview = WhatsappMessageFormatter::displayText(
                        $message->content,
                        $message->type,
                        $message->metadata ?? []
                    );
                @endphp
                <div class="timeline-item">
                    <div class="timeline-icon {{ $iconClass }}">
                        <i class="fas fa-{{ $message->sender_type === 'client' ? 'user' : ($message->sender_type === 'humano' ? 'headset' : 'robot') }}"></i>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <strong>{{ $senderLabel }}</strong>
                        <div>{{ \Illuminate\Support\Str::limit($preview, 120) }}</div>
                        <div class="timeline-meta">{{ $message->created_at->format('d/m/Y H:i:s') }}</div>
                    </div>
                </div>
            @empty
                <p class="text-muted mb-0">No hay mensajes registrados.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
