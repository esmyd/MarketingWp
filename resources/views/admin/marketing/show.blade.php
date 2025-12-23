@extends('admin.layouts.app')

@section('header', 'Detalles de Campaña')

@section('content')
<div class="row">
    <div class="col-12 col-md-8">
        <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-4">
            <div class="p-3 p-md-4 p-lg-6">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
                    <h3 class="mb-0">{{ $campaign->name }}</h3>
                    <div class="d-flex flex-wrap gap-2">
                        @if($campaign->status === 'draft' || $campaign->status === 'scheduled')
                            <a href="{{ route('admin.marketing.edit', $campaign) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        @endif
                        @if($campaign->status === 'draft' || $campaign->status === 'scheduled')
                            <form action="{{ route('admin.marketing.send', $campaign) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('¿Estás seguro de enviar esta campaña?')">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-paper-plane"></i> Enviar Campaña
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('admin.marketing.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                @if($campaign->description)
                    <p class="text-muted mb-4">{{ $campaign->description }}</p>
                @endif

                <div class="row mb-4">
                    <div class="col-12 col-md-6 mb-3 mb-md-0">
                        <strong>Tipo de Mensaje:</strong>
                        <span class="badge bg-info ms-2">
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
                    </div>
                    <div class="col-12 col-md-6">
                        <strong>Estado:</strong>
                        @if($campaign->status === 'draft')
                            <span class="badge bg-secondary ms-2">Borrador</span>
                        @elseif($campaign->status === 'scheduled')
                            <span class="badge bg-warning ms-2">Programada</span>
                        @elseif($campaign->status === 'sending')
                            <span class="badge bg-info ms-2">Enviando...</span>
                        @elseif($campaign->status === 'completed')
                            <span class="badge bg-success ms-2">Completada</span>
                        @elseif($campaign->status === 'paused')
                            <span class="badge bg-warning ms-2">Pausada</span>
                        @else
                            <span class="badge bg-danger ms-2">Cancelada</span>
                        @endif
                    </div>
                </div>

                @if($campaign->message_type === 'text')
                    <div class="mb-4">
                        <strong>Contenido del Mensaje:</strong>
                        <div class="border rounded p-3 mt-2 bg-light">
                            {{ $campaign->message_content }}
                        </div>
                    </div>
                @elseif($campaign->message_type === 'template' && $campaign->template)
                    <div class="mb-4">
                        <strong>Plantilla:</strong>
                        <div class="border rounded p-3 mt-2 bg-light">
                            <strong>{{ $campaign->template->name }}</strong> ({{ $campaign->template->category }})
                            @if($campaign->template_variables)
                                <br><small class="text-muted">Variables: {{ json_encode($campaign->template_variables) }}</small>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="mb-4">
                    <strong>Tipo de Destinatarios:</strong>
                    <span class="ms-2">
                        @if($campaign->recipient_type === 'all')
                            Todos los contactos activos
                        @elseif($campaign->recipient_type === 'filtered')
                            Filtrados
                        @else
                            Seleccionados manualmente
                        @endif
                    </span>
                </div>

                <div class="row mb-4">
                    <div class="col-12 col-md-6 mb-3 mb-md-0">
                        <strong>Total de Destinatarios:</strong>
                        <span class="badge bg-secondary ms-2">{{ $campaign->total_recipients }}</span>
                    </div>
                    @if($campaign->scheduled_at)
                        <div class="col-12 col-md-6">
                            <strong>Programada para:</strong>
                            <span class="ms-2">{{ $campaign->scheduled_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4 mt-4 mt-md-0">
        <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-4">
            <div class="p-3 p-md-4 p-lg-6">
                <h5 class="mb-4">Estadísticas</h5>

                @if($campaign->status === 'completed' || $campaign->status === 'sending')
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Progreso de Envío</span>
                            <span>{{ $campaign->total_recipients > 0 ? round(($campaign->sent_count / $campaign->total_recipients) * 100, 1) : 0 }}%</span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar" role="progressbar"
                                 style="width: {{ $campaign->total_recipients > 0 ? ($campaign->sent_count / $campaign->total_recipients * 100) : 0 }}%">
                                {{ $campaign->sent_count }}/{{ $campaign->total_recipients }}
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Enviados:</strong>
                        <span class="badge bg-success ms-2">{{ $campaign->sent_count }}</span>
                    </div>

                    <div class="mb-3">
                        <strong>Fallidos:</strong>
                        <span class="badge bg-danger ms-2">{{ $campaign->failed_count }}</span>
                    </div>

                    <div class="mb-3">
                        <strong>Entregados:</strong>
                        <span class="badge bg-info ms-2">{{ $campaign->delivered_count }}</span>
                    </div>

                    <div class="mb-3">
                        <strong>Leídos:</strong>
                        <span class="badge bg-primary ms-2">{{ $campaign->read_count }}</span>
                    </div>

                    @if($campaign->sent_count > 0)
                        <div class="mt-4 pt-3 border-top">
                            <small class="text-muted">
                                Tasa de entrega: {{ $campaign->delivery_rate }}%<br>
                                Tasa de lectura: {{ $campaign->read_rate }}%
                            </small>
                        </div>
                    @endif
                @else
                    <p class="text-muted">Las estadísticas estarán disponibles después del envío.</p>
                @endif
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="p-3 p-md-4 p-lg-6">
                <h5 class="mb-4">Información</h5>
                <div class="small">
                    @if($campaign->created_at)
                        <div class="mb-2">
                            <strong>Creada:</strong><br>
                            {{ $campaign->created_at->format('d/m/Y H:i') }}
                        </div>
                    @endif
                    @if($campaign->sent_at)
                        <div class="mb-2">
                            <strong>Enviada:</strong><br>
                            {{ $campaign->sent_at->format('d/m/Y H:i') }}
                        </div>
                    @endif
                    @if($campaign->updated_at)
                        <div>
                            <strong>Última actualización:</strong><br>
                            {{ $campaign->updated_at->format('d/m/Y H:i') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

