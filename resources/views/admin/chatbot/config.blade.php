@extends('admin.layouts.app')

@section('header', 'Configuración del Chatbot')

@section('content')
<div class="bg-white shadow-sm rounded-lg overflow-hidden">
    <div class="p-6">
        <form action="{{ route('admin.chatbot.config.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900">Configuración General</h3>

                    <div>
                        <label for="bot_name" class="block text-sm font-medium text-gray-700">Nombre del Bot</label>
                        <input type="text" id="bot_name" name="bot_name" value="{{ old('bot_name', $config->bot_name ?? '') }}" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <p class="mt-1 text-xs text-gray-500">Usa <code>@{{nombre_bot}}</code> en mensajes del flujo de marketing para mostrar este nombre.</p>
                    </div>

                    <div>
                        <label for="welcome_message" class="block text-sm font-medium text-gray-700">Mensaje de Bienvenida</label>
                        <textarea id="welcome_message" name="welcome_message" rows="3" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('welcome_message', $config->welcome_message ?? '') }}</textarea>
                    </div>

                    <div>
                        <label for="fallback_message" class="block text-sm font-medium text-gray-700">Mensaje de Fallback</label>
                        <textarea id="fallback_message" name="fallback_message" rows="3" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('fallback_message', $config->fallback_message ?? '') }}</textarea>
                    </div>

                    <div>
                        <label for="response_delay" class="block text-sm font-medium text-gray-700">Retraso de Respuesta (ms)</label>
                        <input type="number" id="response_delay" name="response_delay" value="{{ $config->response_delay ?? 1000 }}" min="0" max="5000" step="100"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900">Apariencia</h3>
                    <p class="text-sm text-gray-500">El color primario pinta las burbujas del bot en el chat. El secundario se usa en encabezados y acentos del flujo de marketing.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="primary_color" class="block text-sm font-medium text-gray-700">Color primario (burbujas bot)</label>
                            <div class="mt-1 flex items-center gap-2">
                                <input type="color" id="primary_color" name="primary_color" value="{{ old('primary_color', $config->primary_color ?? '#005c4b') }}"
                                    class="h-10 w-14 border border-gray-300 rounded-md cursor-pointer p-1">
                                <input type="text" id="primary_color_hex" maxlength="7" pattern="#?[0-9a-fA-F]{6}"
                                    value="{{ old('primary_color', $config->primary_color ?? '#005c4b') }}"
                                    class="flex-1 border border-gray-300 rounded-md shadow-sm py-2 px-3 text-sm font-mono uppercase">
                            </div>
                        </div>
                        <div>
                            <label for="secondary_color" class="block text-sm font-medium text-gray-700">Color secundario (acentos)</label>
                            <div class="mt-1 flex items-center gap-2">
                                <input type="color" id="secondary_color" name="secondary_color" value="{{ old('secondary_color', $config->secondary_color ?? '#075e54') }}"
                                    class="h-10 w-14 border border-gray-300 rounded-md cursor-pointer p-1">
                                <input type="text" id="secondary_color_hex" maxlength="7" pattern="#?[0-9a-fA-F]{6}"
                                    value="{{ old('secondary_color', $config->secondary_color ?? '#075e54') }}"
                                    class="flex-1 border border-gray-300 rounded-md shadow-sm py-2 px-3 text-sm font-mono uppercase">
                            </div>
                        </div>
                    </div>

                    <div id="color-preview" class="rounded-lg border border-gray-200 p-4 bg-gray-50">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Vista previa</p>
                        <div class="flex flex-wrap items-end gap-4">
                            <div>
                                <div id="preview-bubble" class="inline-block px-3 py-2 rounded-lg text-white text-sm shadow-sm" style="background: {{ $config->primary_color ?? '#005c4b' }}; border-top-right-radius: 0;">
                                    Mensaje del bot
                                </div>
                            </div>
                            <div id="preview-header" class="px-3 py-2 rounded-md text-white text-sm font-medium" style="background: linear-gradient(135deg, {{ $config->secondary_color ?? '#075e54' }}, {{ $config->primary_color ?? '#005c4b' }});">
                                Encabezado / flujo
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="bot_avatar" class="block text-sm font-medium text-gray-700">Avatar del Bot</label>
                        <div class="mt-2 flex items-start gap-4">
                            <div id="bot-avatar-preview-wrap" class="flex-shrink-0 w-16 h-16 rounded-full overflow-hidden border border-gray-200 bg-gray-100 flex items-center justify-center {{ ($config->bot_avatar_url ?? null) ? '' : 'hidden' }}">
                                <img id="bot-avatar-preview" src="{{ $config->bot_avatar_url ?? '' }}" alt="Avatar del bot" class="w-full h-full object-cover">
                            </div>
                            <div id="bot-avatar-placeholder" class="flex-shrink-0 w-16 h-16 rounded-full border border-dashed border-gray-300 bg-gray-50 flex items-center justify-center text-gray-400 {{ ($config->bot_avatar_url ?? null) ? 'hidden' : '' }}">
                                <i class="fas fa-robot text-xl"></i>
                            </div>
                            <div class="flex-1 space-y-2">
                                <input type="file" id="bot_avatar_image" name="bot_avatar_image" accept="image/jpeg,image/png,image/jpg,image/webp"
                                    class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <input type="url" id="bot_avatar" name="bot_avatar" value="{{ old('bot_avatar', $config->bot_avatar ?? '') }}" placeholder="O pega la URL de la imagen"
                                    class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @if($config->bot_avatar_url ?? null)
                                <label class="inline-flex items-center text-sm text-gray-600">
                                    <input type="checkbox" name="remove_bot_avatar" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                                    Eliminar avatar actual
                                </label>
                                @endif
                                <p class="text-xs text-gray-500">Se muestra en el chat del panel y en la vista previa del flujo.</p>
                            </div>
                        </div>
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
    const fileInput = document.getElementById('bot_avatar_image');
    const preview = document.getElementById('bot-avatar-preview');
    const previewWrap = document.getElementById('bot-avatar-preview-wrap');
    const placeholder = document.getElementById('bot-avatar-placeholder');

    function normalizeHex(value, fallback) {
        let v = (value || '').trim();
        if (!v.startsWith('#')) v = '#' + v;
        if (/^#[0-9a-fA-F]{6}$/.test(v)) return v.toLowerCase();
        if (/^#[0-9a-fA-F]{3}$/.test(v)) {
            const c = v.slice(1);
            return ('#' + c[0] + c[0] + c[1] + c[1] + c[2] + c[2]).toLowerCase();
        }
        return fallback;
    }

    function bindColorPair(pickerId, hexId) {
        const picker = document.getElementById(pickerId);
        const hex = document.getElementById(hexId);
        if (!picker || !hex) return;

        const syncFromPicker = () => {
            hex.value = picker.value;
            updateColorPreview();
        };
        const syncFromHex = () => {
            const normalized = normalizeHex(hex.value, picker.value);
            hex.value = normalized;
            picker.value = normalized;
            updateColorPreview();
        };

        picker.addEventListener('input', syncFromPicker);
        hex.addEventListener('change', syncFromHex);
        hex.addEventListener('blur', syncFromHex);
    }

    function updateColorPreview() {
        const primary = document.getElementById('primary_color')?.value || '#005c4b';
        const secondary = document.getElementById('secondary_color')?.value || '#075e54';
        const bubble = document.getElementById('preview-bubble');
        const header = document.getElementById('preview-header');
        if (bubble) bubble.style.background = primary;
        if (header) header.style.background = `linear-gradient(135deg, ${secondary}, ${primary})`;
    }

    bindColorPair('primary_color', 'primary_color_hex');
    bindColorPair('secondary_color', 'secondary_color_hex');

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            const file = this.files?.[0];
            if (!file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = function (event) {
                preview.src = event.target.result;
                previewWrap.classList.remove('hidden');
                placeholder.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        });
    }
});
</script>
@endpush
