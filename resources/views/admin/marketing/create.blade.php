@extends('admin.layouts.app')

@section('header', 'Nueva Campaña de Marketing')

@section('content')
<!-- Validaciones de Configuración -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="fas fa-check-circle me-2"></i>Estado de Configuración
                </h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3 rounded {{ $businessProfile && $businessProfile->phone_number_id ? 'bg-success-subtle' : 'bg-danger-subtle' }}">
                            <i class="fas {{ $businessProfile && $businessProfile->phone_number_id ? 'fa-check-circle text-success' : 'fa-times-circle text-danger' }} fa-2x me-3"></i>
                            <div>
                                <strong>Perfil de Negocio Meta</strong><br>
                                <small class="text-muted">
                                    {{ $businessProfile && $businessProfile->phone_number_id ? 'Configurado correctamente' : 'No configurado - Revisar configuración' }}
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3 rounded {{ $businessProfile && $businessProfile->access_token ? 'bg-success-subtle' : 'bg-danger-subtle' }}">
                            <i class="fas {{ $businessProfile && $businessProfile->access_token ? 'fa-check-circle text-success' : 'fa-times-circle text-danger' }} fa-2x me-3"></i>
                            <div>
                                <strong>Token de Acceso</strong><br>
                                <small class="text-muted">
                                    {{ $businessProfile && $businessProfile->access_token ? 'Token válido' : 'Token no configurado' }}
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        @php
                            $allTemplates = \App\Models\WhatsappTemplate::all();
                            $approvedTemplates = $templates;
                            $totalTemplates = $allTemplates->count();
                        @endphp
                        <div class="d-flex align-items-center p-3 rounded {{ $approvedTemplates->count() > 0 ? 'bg-success-subtle' : 'bg-warning-subtle' }}">
                            <i class="fas {{ $approvedTemplates->count() > 0 ? 'fa-check-circle text-success' : 'fa-exclamation-triangle text-warning' }} fa-2x me-3"></i>
                            <div>
                                <strong>Plantillas Aprobadas</strong><br>
                                <small class="text-muted">
                                    {{ $approvedTemplates->count() }} plantilla(s) disponible(s)
                                    @if($approvedTemplates->count() === 0)
                                        <br><span class="text-danger">Necesitas al menos una plantilla aprobada</span>
                                        @if($totalTemplates > 0)
                                            <br><span class="text-info small">Total en BD: {{ $totalTemplates }} (Estados: {{ $allTemplates->pluck('status')->unique()->implode(', ') }})</span>
                                        @endif
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3 rounded {{ $contacts->count() > 0 ? 'bg-success-subtle' : 'bg-warning-subtle' }}">
                            <i class="fas {{ $contacts->count() > 0 ? 'fa-check-circle text-success' : 'fa-exclamation-triangle text-warning' }} fa-2x me-3"></i>
                            <div>
                                <strong>Contactos Activos</strong><br>
                                <small class="text-muted">
                                    {{ $contacts->count() }} contacto(s) disponible(s)
                                    @if($contacts->count() === 0)
                                        <br><span class="text-danger">No hay contactos para enviar</span>
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="bg-white shadow-sm rounded-lg overflow-hidden">
    <div class="p-3 p-md-4 p-lg-6">
        <form action="{{ route('admin.marketing.store') }}" method="POST" id="campaignForm">
            @csrf

            <!-- Información Básica -->
            <div class="mb-4 pb-3 border-bottom">
                <h4 class="mb-3">
                    <i class="fas fa-info-circle me-2 text-primary"></i>Información Básica
                </h4>

                <div class="mb-3">
                    <label for="name" class="form-label">Nombre de la Campaña <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Descripción</label>
                    <textarea class="form-control" id="description" name="description" rows="2">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Tipo de Mensaje -->
            <div class="mb-4 pb-3 border-bottom">
                <h4 class="mb-3">
                    <i class="fas fa-envelope me-2 text-primary"></i>Tipo de Mensaje
                </h4>

                <div class="mb-3">
                    <label for="message_type" class="form-label">Tipo <span class="text-danger">*</span></label>
                    <select class="form-control" id="message_type" name="message_type" required onchange="toggleMessageFields()">
                        <option value="text" {{ old('message_type') === 'text' ? 'selected' : '' }}>Mensaje de Texto</option>
                        <option value="template" {{ old('message_type') === 'template' ? 'selected' : '' }}>Plantilla Aprobada</option>
                    </select>
                    @error('message_type')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Campo para mensaje de texto -->
                <div class="mb-3" id="text_message_field">
                    <label for="message_content" class="form-label">Contenido del Mensaje <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="message_content" name="message_content" rows="5"
                              placeholder="Escribe el mensaje que se enviará a los destinatarios...">{{ old('message_content') }}</textarea>
                    <small class="form-text text-muted">Puedes usar @{{name}} para personalizar con el nombre del contacto</small>
                    @error('message_content')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Campo para plantilla -->
                <div class="mb-3" id="template_field" style="display: none;">
                    <label for="template_id" class="form-label">Plantilla <span class="text-danger">*</span></label>
                    <select class="form-control" id="template_id" name="template_id">
                        <option value="">Selecciona una plantilla</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                {{ $template->name }} ({{ $template->category }})
                            </option>
                        @endforeach
                    </select>
                    @error('template_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror

                    <div id="template_variables_field" class="mt-3" style="display: none;">
                        <label class="form-label">Variables de la Plantilla</label>
                        <div id="variables_container"></div>
                    </div>
                </div>
            </div>

            <!-- Destinatarios -->
            <div class="mb-4 pb-3 border-bottom">
                <h4 class="mb-3">
                    <i class="fas fa-users me-2 text-primary"></i>Destinatarios
                </h4>

                <div class="mb-3">
                    <label for="recipient_type" class="form-label">Tipo de Destinatarios <span class="text-danger">*</span></label>
                    <select class="form-control" id="recipient_type" name="recipient_type" required onchange="toggleRecipientFields()">
                        <option value="all" {{ old('recipient_type') === 'all' ? 'selected' : '' }}>Todos los contactos activos</option>
                        <option value="filtered" {{ old('recipient_type') === 'filtered' ? 'selected' : '' }}>Filtrados</option>
                        <option value="selected" {{ old('recipient_type') === 'selected' ? 'selected' : '' }}>Seleccionar manualmente</option>
                    </select>
                    @error('recipient_type')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Filtros -->
                <div class="mb-3" id="filters_field" style="display: none;">
                    <label class="form-label">Filtros</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="filter_bot_enabled" name="recipient_filters[bot_enabled]" value="1"
                               {{ old('recipient_filters.bot_enabled') ? 'checked' : '' }}>
                        <label class="form-check-label" for="filter_bot_enabled">
                            Solo contactos con bot habilitado
                        </label>
                    </div>
                </div>

                <!-- Selección manual -->
                <div class="mb-3" id="selected_contacts_field" style="display: none;">
                    <label class="form-label">Seleccionar Contactos o Agregar Números</label>
                    
                    <!-- Opción para agregar números manualmente -->
                    <div class="card mb-3 border-primary">
                        <div class="card-header bg-primary-subtle">
                            <i class="fas fa-plus-circle me-2"></i>Agregar Números Manualmente
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <textarea class="form-control" id="manual_numbers" rows="3" 
                                          placeholder="Ingresa números de teléfono, uno por línea. Ejemplo:&#10;+1234567890&#10;+0987654321&#10;521234567890"></textarea>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Ingresa números en formato internacional (con código de país), uno por línea.
                                </small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addManualNumbers()">
                                <i class="fas fa-plus me-1"></i>Agregar Números
                            </button>
                        </div>
                    </div>

                    <!-- Lista de contactos -->
                    <div class="mb-2">
                        <input type="text" class="form-control" id="contact_search" placeholder="Buscar contacto..."
                               onkeyup="searchContacts(this.value)">
                    </div>
                    <div id="contacts_list" class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                        @foreach($contacts as $contact)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="selected_contacts[]"
                                       value="{{ $contact->id }}" id="contact_{{ $contact->id }}"
                                       {{ in_array($contact->id, old('selected_contacts', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="contact_{{ $contact->id }}">
                                    {{ $contact->name }} ({{ $contact->phone_number }})
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-2">
                        <small class="form-text text-muted">
                            <i class="fas fa-users me-1"></i>
                            Total seleccionados: <span id="selected_count" class="badge bg-primary rounded-pill">0</span>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Programación -->
            <div class="mb-4 pb-3 border-bottom">
                <h4 class="mb-3">
                    <i class="fas fa-calendar-alt me-2 text-primary"></i>Programación
                </h4>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="schedule_campaign"
                               onchange="toggleSchedule()">
                        <label class="form-check-label" for="schedule_campaign">
                            Programar envío
                        </label>
                    </div>
                </div>

                <div class="mb-3" id="schedule_field" style="display: none;">
                    <label for="scheduled_at" class="form-label">Fecha y Hora de Envío</label>
                    <input type="datetime-local" class="form-control" id="scheduled_at" name="scheduled_at"
                           value="{{ old('scheduled_at') }}">
                    @error('scheduled_at')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Botones -->
            <div class="d-flex justify-content-between align-items-center mt-4 pt-3">
                <a href="{{ route('admin.marketing.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-times me-2"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary rounded-pill px-5">
                    <i class="fas fa-save me-2"></i> Guardar Campaña
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleMessageFields() {
    const messageType = document.getElementById('message_type').value;
    const textField = document.getElementById('text_message_field');
    const templateField = document.getElementById('template_field');
    const variablesField = document.getElementById('template_variables_field');

    if (messageType === 'text') {
        textField.style.display = 'block';
        textField.querySelector('#message_content').required = true;
        templateField.style.display = 'none';
        templateField.querySelector('#template_id').required = false;
        variablesField.style.display = 'none';
    } else if (messageType === 'template') {
        textField.style.display = 'none';
        textField.querySelector('#message_content').required = false;
        templateField.style.display = 'block';
        templateField.querySelector('#template_id').required = true;
    }
}

function toggleRecipientFields() {
    const recipientType = document.getElementById('recipient_type').value;
    const filtersField = document.getElementById('filters_field');
    const selectedField = document.getElementById('selected_contacts_field');

    if (recipientType === 'filtered') {
        filtersField.style.display = 'block';
        selectedField.style.display = 'none';
    } else if (recipientType === 'selected') {
        filtersField.style.display = 'none';
        selectedField.style.display = 'block';
        updateSelectedCount();
    } else {
        filtersField.style.display = 'none';
        selectedField.style.display = 'none';
    }
}

function toggleSchedule() {
    const scheduleCheckbox = document.getElementById('schedule_campaign');
    const scheduleField = document.getElementById('schedule_field');

    if (scheduleCheckbox.checked) {
        scheduleField.style.display = 'block';
        scheduleField.querySelector('#scheduled_at').required = true;
    } else {
        scheduleField.style.display = 'none';
        scheduleField.querySelector('#scheduled_at').required = false;
    }
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('#selected_contacts_field input[type="checkbox"]:checked');
    document.getElementById('selected_count').textContent = checkboxes.length;
}

function searchContacts(query) {
    const contacts = document.querySelectorAll('#contacts_list .form-check');
    contacts.forEach(contact => {
        const text = contact.textContent.toLowerCase();
        if (text.includes(query.toLowerCase())) {
            contact.style.display = 'block';
        } else {
            contact.style.display = 'none';
        }
    });
}

function addManualNumbers() {
    const numbersText = document.getElementById('manual_numbers').value.trim();
    if (!numbersText) {
        alert('Por favor ingresa al menos un número');
        return;
    }

    const numbers = numbersText.split('\n').filter(n => n.trim() !== '');
    const contactsList = document.getElementById('contacts_list');
    
    numbers.forEach(phoneNumber => {
        phoneNumber = phoneNumber.trim();
        if (phoneNumber) {
            // Verificar si el número ya existe
            const existingCheckbox = document.querySelector(`input[value="${phoneNumber}"]`);
            if (existingCheckbox) {
                existingCheckbox.checked = true;
                updateSelectedCount();
                return;
            }

            // Crear nuevo contacto temporal (será manejado en el backend)
            const newContactDiv = document.createElement('div');
            newContactDiv.className = 'form-check';
            newContactDiv.innerHTML = `
                <input class="form-check-input" type="checkbox" name="manual_numbers[]"
                       value="${phoneNumber}" id="manual_${phoneNumber}" checked onchange="updateSelectedCount()">
                <label class="form-check-label" for="manual_${phoneNumber}">
                    <i class="fas fa-phone me-1"></i>${phoneNumber} <span class="badge bg-info ms-2">Nuevo</span>
                </label>
            `;
            contactsList.appendChild(newContactDiv);
        }
    });

    document.getElementById('manual_numbers').value = '';
    updateSelectedCount();
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    toggleMessageFields();
    toggleRecipientFields();

    // Actualizar contador al cambiar checkboxes
    const checkboxes = document.querySelectorAll('#selected_contacts_field input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
    updateSelectedCount();
});
</script>
@endsection

