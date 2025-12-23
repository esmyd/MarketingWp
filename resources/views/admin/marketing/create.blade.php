@extends('admin.layouts.app')

@section('header', 'Nueva Campaña de Marketing')

@section('content')
<div class="bg-white shadow-sm rounded-lg overflow-hidden">
    <div class="p-3 p-md-4 p-lg-6">
        <form action="{{ route('admin.marketing.store') }}" method="POST" id="campaignForm">
            @csrf

            <!-- Información Básica -->
            <div class="mb-4">
                <h4 class="mb-3">Información Básica</h4>

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
            <div class="mb-4">
                <h4 class="mb-3">Tipo de Mensaje</h4>

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
            <div class="mb-4">
                <h4 class="mb-3">Destinatarios</h4>

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
                    <label class="form-label">Seleccionar Contactos</label>
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
                    <small class="form-text text-muted">Total seleccionados: <span id="selected_count">0</span></small>
                </div>
            </div>

            <!-- Programación -->
            <div class="mb-4">
                <h4 class="mb-3">Programación</h4>

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
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.marketing.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Campaña
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

