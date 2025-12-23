@extends('admin.layouts.app')

@section('header', 'Campañas de Marketing')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
    <h2 class="mb-0">Campañas de Marketing</h2>
    <a href="{{ route('admin.marketing.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> <span class="d-none d-sm-inline">Nueva Campaña</span>
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg overflow-hidden">
    <div class="p-3 p-md-4 p-lg-6">
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="d-none d-md-table-header-group">
                    <tr>
                        <th>Nombre</th>
                        <th class="d-none d-lg-table-cell">Tipo</th>
                        <th class="d-none d-md-table-cell">Destinatarios</th>
                        <th>Estado</th>
                        <th class="d-none d-lg-table-cell">Progreso</th>
                        <th class="d-none d-xl-table-cell">Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($campaigns as $campaign)
                        <tr>
                            <td>
                                <strong>{{ $campaign->name }}</strong>
                                @if($campaign->description)
                                    <br><small class="text-muted d-none d-md-inline">{{ \Illuminate\Support\Str::limit($campaign->description, 50) }}</small>
                                @endif
                                <div class="d-md-none mt-2">
                                    <span class="badge bg-info me-1">
                                        @if($campaign->message_type === 'text')
                                            <i class="fas fa-comment"></i>
                                        @elseif($campaign->message_type === 'template')
                                            <i class="fas fa-file-alt"></i>
                                        @elseif($campaign->message_type === 'image')
                                            <i class="fas fa-image"></i>
                                        @else
                                            <i class="fas fa-mouse-pointer"></i>
                                        @endif
                                    </span>
                                    <span class="badge bg-secondary">{{ $campaign->total_recipients }} destinatarios</span>
                                </div>
                            </td>
                            <td class="d-none d-lg-table-cell">
                                <span class="badge bg-info">
                                    @if($campaign->message_type === 'text')
                                        <i class="fas fa-comment"></i> Texto
                                    @elseif($campaign->message_type === 'template')
                                        <i class="fas fa-file-alt"></i> Plantilla
                                    @elseif($campaign->message_type === 'image')
                                        <i class="fas fa-image"></i> Imagen
                                    @else
                                        <i class="fas fa-mouse-pointer"></i> Interactivo
                                    @endif
                                </span>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <span class="badge bg-secondary">{{ $campaign->total_recipients }}</span>
                            </td>
                            <td>
                                @if($campaign->status === 'draft')
                                    <span class="badge bg-secondary">Borrador</span>
                                @elseif($campaign->status === 'scheduled')
                                    <span class="badge bg-warning">Programada</span>
                                @elseif($campaign->status === 'sending')
                                    <span class="badge bg-info">Enviando...</span>
                                @elseif($campaign->status === 'completed')
                                    <span class="badge bg-success">Completada</span>
                                @elseif($campaign->status === 'paused')
                                    <span class="badge bg-warning">Pausada</span>
                                @else
                                    <span class="badge bg-danger">Cancelada</span>
                                @endif
                            </td>
                            <td class="d-none d-lg-table-cell">
                                @if($campaign->status === 'completed' || $campaign->status === 'sending')
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar" role="progressbar"
                                             style="width: {{ $campaign->total_recipients > 0 ? ($campaign->sent_count / $campaign->total_recipients * 100) : 0 }}%">
                                            {{ $campaign->sent_count }}/{{ $campaign->total_recipients }}
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        Enviados: {{ $campaign->sent_count }} |
                                        Fallidos: {{ $campaign->failed_count }}
                                    </small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="d-none d-xl-table-cell">
                                <small>
                                    {{ $campaign->created_at->format('d/m/Y') }}<br>
                                    @if($campaign->sent_at)
                                        {{ $campaign->sent_at->format('d/m/Y') }}
                                    @endif
                                </small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admin.marketing.show', $campaign) }}"
                                       class="btn btn-info" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                        <span class="d-none d-lg-inline ms-1">Ver</span>
                                    </a>
                                    @if($campaign->status === 'draft' || $campaign->status === 'scheduled')
                                        <a href="{{ route('admin.marketing.edit', $campaign) }}"
                                           class="btn btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                            <span class="d-none d-lg-inline ms-1">Editar</span>
                                        </a>
                                    @endif
                                    @if($campaign->status === 'draft' || $campaign->status === 'scheduled')
                                        <form action="{{ route('admin.marketing.send', $campaign) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('¿Estás seguro de enviar esta campaña?')">
                                            @csrf
                                            <button type="submit" class="btn btn-success" title="Enviar">
                                                <i class="fas fa-paper-plane"></i>
                                                <span class="d-none d-lg-inline ms-1">Enviar</span>
                                            </button>
                                        </form>
                                    @endif
                                    @if($campaign->status === 'draft')
                                        <form action="{{ route('admin.marketing.destroy', $campaign) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('¿Estás seguro de eliminar esta campaña?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No hay campañas creadas. <a href="{{ route('admin.marketing.create') }}">Crear primera campaña</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $campaigns->links() }}
        </div>
    </div>
</div>
@endsection

