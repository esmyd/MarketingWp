<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappCampaign;
use App\Models\WhatsappContact;
use App\Models\WhatsappTemplate;
use App\Models\WhatsappBusinessProfile;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MarketingCampaignController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsappService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $campaigns = WhatsappCampaign::with('template')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.marketing.index', compact('campaigns'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Buscar plantillas aprobadas o activas (funciona en producción y local)
        // Prioriza: 'approved', 'active', 'Active - Quality pendin', etc.
        $templates = WhatsappTemplate::where(function($query) {
            $query->whereRaw('LOWER(status) LIKE ?', ['approved%'])
                  ->orWhereRaw('LOWER(status) LIKE ?', ['active%'])
                  ->orWhere('status', 'APPROVED')
                  ->orWhere('status', 'approved')
                  ->orWhere('status', 'ACTIVE')
                  ->orWhere('status', 'active');
        })->get();

        // Si no hay resultados, obtener todas excepto rechazadas/pending (fallback para local)
        if ($templates->isEmpty()) {
            $templates = WhatsappTemplate::whereNotIn('status', ['rejected', 'REJECTED', 'pending', 'PENDING'])
                                         ->whereNotNull('template_id')
                                         ->get();
        }

        $contacts = WhatsappContact::where('status', 'active')->orderBy('name')->get();
        $businessProfile = WhatsappBusinessProfile::first();

        return view('admin.marketing.create', compact('templates', 'contacts', 'businessProfile'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'message_type' => 'required|in:text,template,image,interactive',
            'message_content' => 'required_if:message_type,text|nullable|string',
            'template_id' => 'required_if:message_type,template|nullable|exists:whatsapp_templates,id',
            'template_variables' => 'nullable|array',
            'recipient_type' => 'required|in:all,filtered,selected',
            'recipient_filters' => 'nullable|array',
            'selected_contacts' => 'nullable|array',
            'selected_contacts.*' => 'exists:whatsapp_contacts,id',
            'manual_numbers' => 'nullable|array',
            'manual_numbers.*' => 'string',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $businessProfile = WhatsappBusinessProfile::first();
        if (!$businessProfile) {
            return redirect()->back()->with('error', 'No se encontró un perfil de negocio configurado');
        }

        // Manejar números manuales - crear contactos temporales si es necesario
        $selectedContacts = $validated['selected_contacts'] ?? [];
        if (!empty($validated['manual_numbers'])) {
            foreach ($validated['manual_numbers'] as $phoneNumber) {
                $phoneNumber = trim($phoneNumber);
                if ($phoneNumber) {
                    // Buscar si el contacto ya existe
                    $existingContact = WhatsappContact::where('phone_number', $phoneNumber)
                        ->where('business_profile_id', $businessProfile->id)
                        ->first();

                    if ($existingContact) {
                        // Agregar a la lista si no está ya incluido
                        if (!in_array($existingContact->id, $selectedContacts)) {
                            $selectedContacts[] = $existingContact->id;
                        }
                    } else {
                        // Crear contacto temporal
                        $newContact = WhatsappContact::create([
                            'business_profile_id' => $businessProfile->id,
                            'name' => 'Contacto ' . substr($phoneNumber, -4),
                            'phone_number' => $phoneNumber,
                            'status' => 'active',
                            'metadata' => ['temporary' => true, 'created_from_campaign' => true]
                        ]);
                        $selectedContacts[] = $newContact->id;
                    }
                }
            }
        }

        // Actualizar validated con los contactos seleccionados (incluidos los nuevos)
        $validated['selected_contacts'] = array_unique($selectedContacts);

        // Calcular total de destinatarios
        $totalRecipients = $this->calculateRecipients($validated);

        $campaign = WhatsappCampaign::create([
            'business_profile_id' => $businessProfile->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'message_type' => $validated['message_type'],
            'message_content' => $validated['message_content'] ?? null,
            'template_id' => $validated['template_id'] ?? null,
            'template_variables' => $validated['template_variables'] ?? null,
            'recipient_type' => $validated['recipient_type'],
            'recipient_filters' => $validated['recipient_filters'] ?? null,
            'selected_contacts' => $validated['selected_contacts'] ?? null,
            'status' => $validated['scheduled_at'] ? 'scheduled' : 'draft',
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'total_recipients' => $totalRecipients,
        ]);

        return redirect()->route('admin.marketing.index')
            ->with('success', 'Campaña creada correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $campaign = WhatsappCampaign::with('template', 'businessProfile')->findOrFail($id);
        return view('admin.marketing.show', compact('campaign'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $campaign = WhatsappCampaign::findOrFail($id);

        if ($campaign->status === 'sending' || $campaign->status === 'completed') {
            return redirect()->route('admin.marketing.show', $campaign)
                ->with('error', 'No se puede editar una campaña que ya está en proceso o completada');
        }

        // Buscar plantillas aprobadas o activas (case-insensitive)
        // Incluye: 'approved', 'active', 'Active - Quality pendin', etc.
        $templates = WhatsappTemplate::where(function($query) {
            $query->whereRaw('LOWER(status) LIKE ?', ['approved%'])
                  ->orWhereRaw('LOWER(status) LIKE ?', ['active%'])
                  ->orWhere('status', 'APPROVED')
                  ->orWhere('status', 'approved')
                  ->orWhere('status', 'ACTIVE')
                  ->orWhere('status', 'active');
        })->get();

        // Si no hay resultados, obtener todas excepto rechazadas/pending (fallback para local)
        if ($templates->isEmpty()) {
            $templates = WhatsappTemplate::whereNotIn('status', ['rejected', 'REJECTED', 'pending', 'PENDING'])
                                         ->whereNotNull('template_id')
                                         ->get();
        }

        $contacts = WhatsappContact::where('status', 'active')->orderBy('name')->get();
        $businessProfile = WhatsappBusinessProfile::first();

        return view('admin.marketing.edit', compact('campaign', 'templates', 'contacts', 'businessProfile'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $campaign = WhatsappCampaign::findOrFail($id);

        if ($campaign->status === 'sending' || $campaign->status === 'completed') {
            return redirect()->back()->with('error', 'No se puede editar una campaña que ya está en proceso o completada');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'message_type' => 'required|in:text,template,image,interactive',
            'message_content' => 'required_if:message_type,text|nullable|string',
            'template_id' => 'required_if:message_type,template|nullable|exists:whatsapp_templates,id',
            'template_variables' => 'nullable|array',
            'recipient_type' => 'required|in:all,filtered,selected',
            'recipient_filters' => 'nullable|array',
            'selected_contacts' => 'nullable|array',
            'selected_contacts.*' => 'exists:whatsapp_contacts,id',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        // Recalcular total de destinatarios
        $totalRecipients = $this->calculateRecipients($validated);

        $campaign->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'message_type' => $validated['message_type'],
            'message_content' => $validated['message_content'] ?? null,
            'template_id' => $validated['template_id'] ?? null,
            'template_variables' => $validated['template_variables'] ?? null,
            'recipient_type' => $validated['recipient_type'],
            'recipient_filters' => $validated['recipient_filters'] ?? null,
            'selected_contacts' => $validated['selected_contacts'] ?? null,
            'status' => $validated['scheduled_at'] ? 'scheduled' : 'draft',
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'total_recipients' => $totalRecipients,
        ]);

        return redirect()->route('admin.marketing.index')
            ->with('success', 'Campaña actualizada correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $campaign = WhatsappCampaign::findOrFail($id);

        if ($campaign->status === 'sending') {
            return redirect()->back()->with('error', 'No se puede eliminar una campaña que está en proceso de envío');
        }

        $campaign->delete();

        return redirect()->route('admin.marketing.index')
            ->with('success', 'Campaña eliminada correctamente');
    }

    /**
     * Envía la campaña
     */
    public function send($id)
    {
        $campaign = WhatsappCampaign::findOrFail($id);

        if ($campaign->status === 'sending' || $campaign->status === 'completed') {
            return redirect()->back()->with('error', 'Esta campaña ya fue enviada o está en proceso');
        }

        try {
            $campaign->update(['status' => 'sending']);

            // Obtener destinatarios
            $recipients = $this->getRecipients($campaign);

            if (empty($recipients)) {
                $campaign->update(['status' => 'draft']);
                return redirect()->back()->with('error', 'No se encontraron destinatarios para la campaña');
            }

            // Enviar mensajes
            $sent = 0;
            $failed = 0;
            $errorDetails = [];

            foreach ($recipients as $contact) {
                try {
                    $success = false;
                    $errorMessage = null;

                    if ($campaign->message_type === 'text') {
                        // Personalizar el mensaje con datos del contacto
                        $personalizedMessage = $this->personalizeMessage($campaign->message_content, $contact);
                        $result = $this->whatsappService->sendTextMessage(
                            $contact,
                            $personalizedMessage,
                            false
                        );

                        if (is_array($result)) {
                            $success = $result['success'] ?? false;
                            $errorMessage = $result['error'] ?? null;
                        } else {
                            $success = $result;
                        }
                    } elseif ($campaign->message_type === 'template' && $campaign->template) {
                        $variables = $campaign->template_variables ?? [];
                        // Personalizar variables con datos del contacto
                        $personalizedVars = $this->personalizeVariables($variables, $contact);
                        $result = $this->whatsappService->sendTemplateMessage(
                            $contact,
                            $campaign->template,
                            $personalizedVars
                        );

                        if (is_array($result)) {
                            $success = $result['success'] ?? false;
                            $errorMessage = $result['error'] ?? null;
                        } else {
                            $success = $result;
                        }
                    }

                    if ($success) {
                        $sent++;
                    } else {
                        $failed++;
                        if ($errorMessage) {
                            $errorDetails[] = [
                                'contact_id' => $contact->id,
                                'contact_name' => $contact->name,
                                'phone_number' => $contact->phone_number,
                                'error' => $errorMessage,
                                'timestamp' => now()->toIso8601String()
                            ];
                        }
                    }

                    // Pequeña pausa para evitar rate limiting
                    usleep(500000); // 0.5 segundos
                } catch (\Exception $e) {
                    Log::error('Error enviando mensaje de campaña', [
                        'campaign_id' => $campaign->id,
                        'contact_id' => $contact->id,
                        'error' => $e->getMessage()
                    ]);
                    $failed++;
                    $errorDetails[] = [
                        'contact_id' => $contact->id,
                        'contact_name' => $contact->name ?? 'Desconocido',
                        'phone_number' => $contact->phone_number ?? 'N/A',
                        'error' => $e->getMessage(),
                        'timestamp' => now()->toIso8601String()
                    ];
                }

                // Actualizar contadores en tiempo real
                $campaign->update([
                    'sent_count' => $sent,
                    'failed_count' => $failed,
                    'error_details' => !empty($errorDetails) ? $errorDetails : null
                ]);
            }

            $campaign->update([
                'status' => 'completed',
                'sent_at' => now(),
                'error_details' => !empty($errorDetails) ? $errorDetails : null
            ]);

            return redirect()->route('admin.marketing.show', $campaign)
                ->with('success', "Campaña enviada. Enviados: {$sent}, Fallidos: {$failed}");

        } catch (\Exception $e) {
            Log::error('Error enviando campaña', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $campaign->update(['status' => 'draft']);

            return redirect()->back()->with('error', 'Error al enviar la campaña: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene los destinatarios según la configuración de la campaña
     */
    private function getRecipients(WhatsappCampaign $campaign)
    {
        $query = WhatsappContact::where('status', 'active');

        switch ($campaign->recipient_type) {
            case 'all':
                // Todos los contactos activos
                break;

            case 'filtered':
                // Aplicar filtros
                $filters = $campaign->recipient_filters ?? [];
                if (isset($filters['bot_enabled'])) {
                    $query->where('bot_enabled', $filters['bot_enabled']);
                }
                // Agregar más filtros según necesidad
                break;

            case 'selected':
                // Contactos seleccionados manualmente
                $selectedIds = $campaign->selected_contacts ?? [];
                if (!empty($selectedIds)) {
                    $query->whereIn('id', $selectedIds);
                } else {
                    return collect();
                }
                break;
        }

        return $query->get();
    }

    /**
     * Calcula el total de destinatarios
     */
    private function calculateRecipients(array $data): int
    {
        $query = WhatsappContact::where('status', 'active');

        switch ($data['recipient_type']) {
            case 'all':
                return $query->count();

            case 'filtered':
                $filters = $data['recipient_filters'] ?? [];
                if (isset($filters['bot_enabled'])) {
                    $query->where('bot_enabled', $filters['bot_enabled']);
                }
                return $query->count();

            case 'selected':
                $selectedIds = $data['selected_contacts'] ?? [];
                return count($selectedIds);

            default:
                return 0;
        }
    }

    /**
     * Personaliza un mensaje de texto con datos del contacto
     */
    private function personalizeMessage(string $message, WhatsappContact $contact): string
    {
        $personalized = $message;

        // Reemplazar placeholders comunes
        $personalized = str_replace('{{name}}', $contact->name ?? 'Cliente', $personalized);
        $personalized = str_replace('{{phone}}', $contact->phone_number ?? '', $personalized);
        $personalized = str_replace('{{nombre}}', $contact->name ?? 'Cliente', $personalized);
        $personalized = str_replace('{{telefono}}', $contact->phone_number ?? '', $personalized);
        $personalized = str_replace('{{nombre_contacto}}', $contact->name ?? 'Cliente', $personalized);

        return $personalized;
    }

    /**
     * Personaliza las variables de la plantilla con datos del contacto
     */
    private function personalizeVariables(array $variables, WhatsappContact $contact): array
    {
        $personalized = [];
        foreach ($variables as $var) {
            if (is_string($var)) {
                // Reemplazar placeholders comunes
                $var = str_replace('{{name}}', $contact->name ?? 'Cliente', $var);
                $var = str_replace('{{phone}}', $contact->phone_number ?? '', $var);
                $var = str_replace('{{nombre}}', $contact->name ?? 'Cliente', $var);
                $var = str_replace('{{telefono}}', $contact->phone_number ?? '', $var);
            }
            $personalized[] = $var;
        }
        return $personalized;
    }

    /**
     * Reprograma una campaña completada o fallida
     */
    public function reschedule(Request $request, $id)
    {
        $campaign = WhatsappCampaign::findOrFail($id);

        $validated = $request->validate([
            'scheduled_at' => 'required|date|after:now',
        ]);

        // Resetear estadísticas y estado
        $campaign->update([
            'status' => 'scheduled',
            'scheduled_at' => $validated['scheduled_at'],
            'sent_at' => null,
            'sent_count' => 0,
            'failed_count' => 0,
            'delivered_count' => 0,
            'read_count' => 0,
            'error_details' => null
        ]);

        return redirect()->route('admin.marketing.show', $campaign)
            ->with('success', 'Campaña reprogramada correctamente');
    }

    /**
     * Obtiene contactos para AJAX (búsqueda)
     */
    public function getContacts(Request $request)
    {
        $search = $request->get('search', '');

        $contacts = WhatsappContact::where('status', 'active')
            ->where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            })
            ->limit(50)
            ->get(['id', 'name', 'phone_number']);

        return response()->json($contacts);
    }
}
