@extends('admin.layouts.app')

@section('header', 'Configuración del Chatbot')

@section('content')
<div class="bg-white shadow-sm rounded-lg overflow-hidden">
    <div class="p-6">
        <form action="{{ route('admin.chatbot.config.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900">Configuración General</h3>

                    <div>
                        <label for="bot_name" class="block text-sm font-medium text-gray-700">Nombre del Bot</label>
                        <input type="text" id="bot_name" name="bot_name" value="{{ $config->bot_name ?? '' }}" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="welcome_message" class="block text-sm font-medium text-gray-700">Mensaje de Bienvenida</label>
                        <textarea id="welcome_message" name="welcome_message" rows="3" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ $config->welcome_message ?? '' }}</textarea>
                    </div>

                    <div>
                        <label for="fallback_message" class="block text-sm font-medium text-gray-700">Mensaje de Fallback</label>
                        <textarea id="fallback_message" name="fallback_message" rows="3" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ $config->fallback_message ?? '' }}</textarea>
                    </div>

                    <div>
                        <label for="response_delay" class="block text-sm font-medium text-gray-700">Retraso de Respuesta (ms)</label>
                        <input type="number" id="response_delay" name="response_delay" value="{{ $config->response_delay ?? 1000 }}" min="0" max="5000" step="100"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900">Apariencia</h3>

                    <div>
                        <label for="primary_color" class="block text-sm font-medium text-gray-700">Color Primario</label>
                        <input type="color" id="primary_color" name="primary_color" value="{{ $config->primary_color ?? '#3B82F6' }}"
                            class="mt-1 block w-full h-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="secondary_color" class="block text-sm font-medium text-gray-700">Color Secundario</label>
                        <input type="color" id="secondary_color" name="secondary_color" value="{{ $config->secondary_color ?? '#1E40AF' }}"
                            class="mt-1 block w-full h-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="bot_avatar" class="block text-sm font-medium text-gray-700">Avatar del Bot</label>
                        <input type="text" id="bot_avatar" name="bot_avatar" value="{{ $config->bot_avatar ?? '' }}" placeholder="URL de la imagen"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="font_family" class="block text-sm font-medium text-gray-700">Fuente</label>
                        <select id="font_family" name="font_family"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="Arial" {{ ($config->font_family ?? '') === 'Arial' ? 'selected' : '' }}>Arial</option>
                            <option value="Helvetica" {{ ($config->font_family ?? '') === 'Helvetica' ? 'selected' : '' }}>Helvetica</option>
                            <option value="Roboto" {{ ($config->font_family ?? '') === 'Roboto' ? 'selected' : '' }}>Roboto</option>
                            <option value="Open Sans" {{ ($config->font_family ?? '') === 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mt-6 bg-gray-50 p-6 rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-4">🔔 Configuración de Monitoreo</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Recibe notificaciones cada vez que alguien escriba al bot. Las notificaciones se enviarán por WhatsApp y/o Email.
                </p>

                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="monitoring_enabled" name="monitoring_enabled" value="1" 
                            {{ ($config->monitoring_enabled ?? false) ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="monitoring_enabled" class="ml-2 block text-sm font-medium text-gray-700">
                            Habilitar monitoreo
                        </label>
                    </div>

                    <div>
                        <label for="monitoring_phone_number" class="block text-sm font-medium text-gray-700">
                            Número de WhatsApp para monitoreo
                        </label>
                        <input type="text" id="monitoring_phone_number" name="monitoring_phone_number" 
                            value="{{ $config->monitoring_phone_number ?? '' }}" 
                            placeholder="Ej: 521234567890 (con código de país)"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <p class="mt-1 text-xs text-gray-500">
                            Número donde recibirás las notificaciones por WhatsApp. Debe incluir el código de país sin el signo +.
                        </p>
                    </div>

                    <div>
                        <label for="monitoring_email" class="block text-sm font-medium text-gray-700">
                            Email para monitoreo
                        </label>
                        <input type="email" id="monitoring_email" name="monitoring_email" 
                            value="{{ $config->monitoring_email ?? '' }}" 
                            placeholder="ejemplo@correo.com"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <p class="mt-1 text-xs text-gray-500">
                            Email donde recibirás las notificaciones por correo electrónico.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-save mr-2"></i>Guardar Configuración
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Inicializar textareas con editor enriquecido si es necesario
    // Ejemplo: tinymce.init({ selector: 'textarea' });
});
</script>
@endpush
