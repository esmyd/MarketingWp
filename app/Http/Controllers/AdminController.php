<?php

namespace App\Http\Controllers;

use App\Models\WhatsappCart;
use App\Models\WhatsappMessage;
use App\Models\WhatsappContact;
use App\Models\WhatsappConversation;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function dashboard()
    {
        $orders = WhatsappCart::with(['items', 'contact'])->latest()->get();
        $messages = WhatsappMessage::with(['contact', 'conversation'])->latest()->get();

        return view('admin.dashboard', compact('orders', 'messages'));
    }

    public function orders()
    {
        $orders = WhatsappCart::with(['items', 'contact'])
            ->latest()
            ->paginate(10);

        return view('admin.orders', compact('orders'));
    }

    public function messages()
    {
        $messages = WhatsappMessage::with(['contact', 'conversation'])
            ->latest()
            ->paginate(20);

        return view('admin.messages', compact('messages'));
    }

    public function orderDetails($id)
    {
        $order = WhatsappCart::with(['items', 'contact'])->findOrFail($id);
        return response()->json($order);
    }

    public function chats()
    {
        // Obtener contactos que tengan mensajes usando consultas directas
        $contacts = \App\Models\WhatsappContact::whereIn('id', function($query) {
            $query->select('contact_id')->from('whatsapp_messages')->distinct();
        })->get();
        // Contar mensajes por contacto y obtener último mensaje del cliente
        foreach ($contacts as $contact) {
            $contact->messages_count = \App\Models\WhatsappMessage::where('contact_id', $contact->id)->count();
            $lastClientMsg = \App\Models\WhatsappMessage::where('contact_id', $contact->id)
                ->where('sender_type', 'client')
                ->orderByDesc('created_at')
                ->first();
            $contact->last_client_message = $lastClientMsg ? $lastClientMsg->content : null;
        }
        // Si hay al menos un contacto, redirigir al primer chat
        if ($contacts->count() > 0) {
            return redirect()->route('admin.chat', $contacts->first()->id);
        }
        // Si no hay contactos, mostrar la vista original
        return view('admin.chats', compact('contacts'));
    }

    public function chat($contactId)
    {
        $contacts = \App\Models\WhatsappContact::whereIn('id', function($query) {
            $query->select('contact_id')->from('whatsapp_messages')->distinct();
        })->get();
        // Contar mensajes por contacto y obtener último mensaje del cliente
        foreach ($contacts as $contactItem) {
            $contactItem->messages_count = \App\Models\WhatsappMessage::where('contact_id', $contactItem->id)->count();
            $lastClientMsg = \App\Models\WhatsappMessage::where('contact_id', $contactItem->id)
                ->where('sender_type', 'client')
                ->orderByDesc('created_at')
                ->first();
            $contactItem->last_client_message = $lastClientMsg ? $lastClientMsg->content : null;
        }
        $contact = \App\Models\WhatsappContact::findOrFail($contactId);
        $messages = \App\Models\WhatsappMessage::where('contact_id', $contactId)->orderBy('created_at')->get();

        // Calcular estadísticas del contacto actual
        $now = \Carbon\Carbon::now();
        $thirtyDaysAgo = $now->copy()->subDays(30);
        $sixtyDaysAgo = $now->copy()->subDays(60);

        // Total de mensajes del contacto
        $totalMessages = \App\Models\WhatsappMessage::where('contact_id', $contactId)->count();
        $lastMonthMessages = \App\Models\WhatsappMessage::where('contact_id', $contactId)
            ->where('created_at', '>=', $thirtyDaysAgo)->count();
        $previousMonthMessages = \App\Models\WhatsappMessage::where('contact_id', $contactId)
            ->whereBetween('created_at', [$sixtyDaysAgo, $thirtyDaysAgo])->count();
        $messageGrowth = $previousMonthMessages > 0
            ? round((($lastMonthMessages - $previousMonthMessages) / $previousMonthMessages) * 100, 1)
            : 0;

        // Tasa de respuesta (mensajes del sistema / mensajes del cliente)
        $totalResponses = \App\Models\WhatsappMessage::where('contact_id', $contactId)
            ->where('sender_type', 'system')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        $totalInbound = \App\Models\WhatsappMessage::where('contact_id', $contactId)
            ->where('sender_type', 'client')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        $responseRate = $totalInbound > 0 ? round(($totalResponses / $totalInbound) * 100, 1) : 0;

        $previousResponses = \App\Models\WhatsappMessage::where('contact_id', $contactId)
            ->where('sender_type', 'system')
            ->whereBetween('created_at', [$sixtyDaysAgo, $thirtyDaysAgo])
            ->count();
        $previousInbound = \App\Models\WhatsappMessage::where('contact_id', $contactId)
            ->where('sender_type', 'client')
            ->whereBetween('created_at', [$sixtyDaysAgo, $thirtyDaysAgo])
            ->count();
        $previousResponseRate = $previousInbound > 0 ? ($previousResponses / $previousInbound) * 100 : 0;
        $responseRateGrowth = $previousResponseRate > 0
            ? round((($responseRate - $previousResponseRate) / $previousResponseRate) * 100, 1)
            : 0;

        // Tiempo promedio de respuesta (calcular tiempo entre mensaje del cliente y respuesta del sistema)
        $responseTimes = [];
        $clientMessages = \App\Models\WhatsappMessage::where('contact_id', $contactId)
            ->where('sender_type', 'client')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->orderBy('created_at')
            ->get();

        foreach ($clientMessages as $clientMsg) {
            $nextSystemMsg = \App\Models\WhatsappMessage::where('contact_id', $contactId)
                ->where('sender_type', 'system')
                ->where('created_at', '>', $clientMsg->created_at)
                ->orderBy('created_at')
                ->first();

            if ($nextSystemMsg) {
                $responseTimes[] = $clientMsg->created_at->diffInMinutes($nextSystemMsg->created_at);
            }
        }

        $avgResponseTime = count($responseTimes) > 0
            ? round(array_sum($responseTimes) / count($responseTimes), 1) . 'm'
            : '0m';

        // Mensajes con botones/interactivos
        $buttonMessages = \App\Models\WhatsappMessage::where('contact_id', $contactId)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->where(function($q) {
                $q->where('type', 'button')
                  ->orWhere('type', 'interactive')
                  ->orWhere(function($subQ) {
                      $subQ->whereRaw("JSON_VALID(content) = 1")
                           ->where(function($jsonQ) {
                               $jsonQ->whereRaw("JSON_EXTRACT(content, '$.type') = 'button_reply'")
                                     ->orWhereRaw("JSON_EXTRACT(content, '$.type') = 'list_reply'");
                           });
                  });
            })
            ->count();
        $buttonMessagesRate = $lastMonthMessages > 0 ? round(($buttonMessages / $lastMonthMessages) * 100, 1) : 0;

        // Tasa de interacción
        $interactions = \App\Models\WhatsappMessage::where('contact_id', $contactId)
            ->where('sender_type', 'client')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        $totalOutbound = \App\Models\WhatsappMessage::where('contact_id', $contactId)
            ->where('sender_type', 'system')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        $interactionRate = $totalOutbound > 0 ? round(($interactions / $totalOutbound) * 100, 1) : 0;

        // Mensajes por día de la semana (últimos 7 días)
        $messagesByDay = [];
        $days = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $dayName = $days[$date->dayOfWeek];
            $sent = \App\Models\WhatsappMessage::where('contact_id', $contactId)
                ->where('sender_type', 'system')
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->count();
            $received = \App\Models\WhatsappMessage::where('contact_id', $contactId)
                ->where('sender_type', 'client')
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->count();
            $messagesByDay[] = [
                'day' => $dayName,
                'sent' => $sent,
                'received' => $received
            ];
        }

        // Tiempo de respuesta por día
        $responseTimeByDay = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $dayName = $days[$date->dayOfWeek];
            $dayResponseTimes = [];

            $dayClientMessages = \App\Models\WhatsappMessage::where('contact_id', $contactId)
                ->where('sender_type', 'client')
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->orderBy('created_at')
                ->get();

            foreach ($dayClientMessages as $clientMsg) {
                $nextSystemMsg = \App\Models\WhatsappMessage::where('contact_id', $contactId)
                    ->where('sender_type', 'system')
                    ->where('created_at', '>', $clientMsg->created_at)
                    ->whereDate('created_at', $date->format('Y-m-d'))
                    ->orderBy('created_at')
                    ->first();

                if ($nextSystemMsg) {
                    $dayResponseTimes[] = $clientMsg->created_at->diffInMinutes($nextSystemMsg->created_at);
                }
            }

            $avgTime = count($dayResponseTimes) > 0
                ? round(array_sum($dayResponseTimes) / count($dayResponseTimes), 1)
                : 0;

            $responseTimeByDay[] = [
                'day' => $dayName,
                'time' => $avgTime
            ];
        }

        // Distribución de tipos de mensajes
        $messageTypes = \App\Models\WhatsappMessage::where('contact_id', $contactId)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type')
            ->toArray();

        // NUEVOS INDICADORES ÚTILES

        // 1. Mensajes enviados vs recibidos (últimos 30 días)
        $sentMessages = \App\Models\WhatsappMessage::where('contact_id', $contactId)
            ->where('sender_type', 'system')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        $receivedMessages = \App\Models\WhatsappMessage::where('contact_id', $contactId)
            ->where('sender_type', 'client')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        $sentReceivedRatio = $receivedMessages > 0 ? round($sentMessages / $receivedMessages, 2) : 0;

        // 2. Última actividad
        $lastMessage = \App\Models\WhatsappMessage::where('contact_id', $contactId)
            ->orderByDesc('created_at')
            ->first();
        $lastActivity = $lastMessage ? $lastMessage->created_at->diffForHumans() : 'Nunca';
        $lastActivityDate = $lastMessage ? $lastMessage->created_at->format('d/m/Y H:i') : 'N/A';

        // 3. Hora de mayor actividad (últimos 30 días)
        $messagesByHour = \App\Models\WhatsappMessage::where('contact_id', $contactId)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderByDesc('count')
            ->first();
        $peakHour = $messagesByHour ? $messagesByHour->hour . ':00' : 'N/A';

        // 4. Día más activo de la semana (últimos 30 días)
        $messagesByWeekday = \App\Models\WhatsappMessage::where('contact_id', $contactId)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->selectRaw('DAYOFWEEK(created_at) as weekday, COUNT(*) as count')
            ->groupBy('weekday')
            ->orderByDesc('count')
            ->first();
        $weekdayNames = ['', 'Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $mostActiveDay = $messagesByWeekday ? $weekdayNames[$messagesByWeekday->weekday] : 'N/A';

        // 5. Longitud promedio de mensajes del cliente (últimos 30 días)
        $avgMessageLength = \App\Models\WhatsappMessage::where('contact_id', $contactId)
            ->where('sender_type', 'client')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->whereNotNull('content')
            ->get()
            ->map(function($msg) {
                try {
                    $decoded = json_decode($msg->content, true);
                    if (is_array($decoded)) {
                        return isset($decoded['text']) ? strlen($decoded['text']) : strlen($msg->content);
                    }
                } catch (\Exception $e) {
                    // No es JSON, usar contenido directo
                }
                return strlen($msg->content);
            })
            ->filter(function($len) {
                return $len > 0;
            });
        $avgLength = $avgMessageLength->count() > 0
            ? round($avgMessageLength->avg(), 0) . ' caracteres'
            : '0 caracteres';

        // 6. Número de conversaciones/sesiones (grupos de mensajes con menos de 2 horas entre ellos)
        $allMessages = \App\Models\WhatsappMessage::where('contact_id', $contactId)
            ->orderBy('created_at')
            ->get();
        $conversations = 0;
        $lastMessageTime = null;
        foreach ($allMessages as $msg) {
            if ($lastMessageTime === null || $msg->created_at->diffInHours($lastMessageTime) >= 2) {
                $conversations++;
            }
            $lastMessageTime = $msg->created_at;
        }

        // 7. Tiempo promedio entre mensajes del cliente (últimos 30 días)
        $clientMessages = \App\Models\WhatsappMessage::where('contact_id', $contactId)
            ->where('sender_type', 'client')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->orderBy('created_at')
            ->get();
        $timeBetweenMessages = [];
        for ($i = 1; $i < $clientMessages->count(); $i++) {
            $timeBetweenMessages[] = $clientMessages[$i-1]->created_at->diffInMinutes($clientMessages[$i]->created_at);
        }
        $avgTimeBetween = count($timeBetweenMessages) > 0
            ? round(array_sum($timeBetweenMessages) / count($timeBetweenMessages), 1)
            : 0;
        $avgTimeBetweenFormatted = $avgTimeBetween > 0
            ? ($avgTimeBetween >= 60 ? round($avgTimeBetween / 60, 1) . 'h' : $avgTimeBetween . 'm')
            : '0m';

        // 8. Tasa de respuesta del cliente (cuánto responde a nuestros mensajes)
        $systemMessages = \App\Models\WhatsappMessage::where('contact_id', $contactId)
            ->where('sender_type', 'system')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->get();
        $clientResponses = 0;
        foreach ($systemMessages as $sysMsg) {
            $clientReply = \App\Models\WhatsappMessage::where('contact_id', $contactId)
                ->where('sender_type', 'client')
                ->where('created_at', '>', $sysMsg->created_at)
                ->where('created_at', '<=', $sysMsg->created_at->copy()->addHours(24))
                ->first();
            if ($clientReply) {
                $clientResponses++;
            }
        }
        $clientResponseRate = $systemMessages->count() > 0
            ? round(($clientResponses / $systemMessages->count()) * 100, 1)
            : 0;

        // 9. Frecuencia de mensajes (mensajes por día en últimos 30 días)
        $daysActive = \App\Models\WhatsappMessage::where('contact_id', $contactId)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->selectRaw('DATE(created_at) as date')
            ->distinct()
            ->count();
        $frequencyPerDay = $daysActive > 0
            ? round($lastMonthMessages / $daysActive, 1)
            : 0;

        // Si es una petición AJAX, devolver JSON
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'contact' => [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'phone_number' => $contact->phone_number,
                    'bot_enabled' => $contact->bot_enabled ?? true
                ],
                'messages' => $messages->map(function($msg) {
                    return [
                        'id' => $msg->id,
                        'content' => $msg->content,
                        'type' => $msg->type,
                        'sender_type' => $msg->sender_type,
                        'metadata' => $msg->metadata,
                        'created_at' => $msg->created_at->toDateTimeString()
                    ];
                }),
                'stats' => [
                    'totalMessages' => $totalMessages,
                    'messageGrowth' => $messageGrowth,
                    'responseRate' => $responseRate . '%',
                    'responseRateGrowth' => $responseRateGrowth,
                    'avgResponseTime' => $avgResponseTime,
                    'buttonMessagesRate' => $buttonMessagesRate . '%',
                    'interactionRate' => $interactionRate . '%',
                    'messagesByDay' => $messagesByDay,
                    'responseTimeByDay' => $responseTimeByDay,
                    'messageTypes' => $messageTypes,
                    // Nuevos indicadores
                    'sentMessages' => $sentMessages,
                    'receivedMessages' => $receivedMessages,
                    'sentReceivedRatio' => $sentReceivedRatio,
                    'lastActivity' => $lastActivity,
                    'lastActivityDate' => $lastActivityDate,
                    'peakHour' => $peakHour,
                    'mostActiveDay' => $mostActiveDay,
                    'avgMessageLength' => $avgLength,
                    'conversations' => $conversations,
                    'avgTimeBetweenMessages' => $avgTimeBetweenFormatted,
                    'clientResponseRate' => $clientResponseRate . '%',
                    'frequencyPerDay' => $frequencyPerDay
                ]
            ]);
        }

        // Estadísticas para la vista
        $stats = [
            'totalMessages' => $totalMessages,
            'messageGrowth' => $messageGrowth,
            'responseRate' => $responseRate . '%',
            'responseRateGrowth' => $responseRateGrowth,
            'avgResponseTime' => $avgResponseTime,
            'buttonMessagesRate' => $buttonMessagesRate . '%',
            'interactionRate' => $interactionRate . '%',
            'messagesByDay' => $messagesByDay,
            'responseTimeByDay' => $responseTimeByDay,
            'messageTypes' => $messageTypes,
            // Nuevos indicadores
            'sentMessages' => $sentMessages,
            'receivedMessages' => $receivedMessages,
            'sentReceivedRatio' => $sentReceivedRatio,
            'lastActivity' => $lastActivity,
            'lastActivityDate' => $lastActivityDate,
            'peakHour' => $peakHour,
            'mostActiveDay' => $mostActiveDay,
            'avgMessageLength' => $avgLength,
            'conversations' => $conversations,
            'avgTimeBetweenMessages' => $avgTimeBetweenFormatted,
            'clientResponseRate' => $clientResponseRate . '%',
            'frequencyPerDay' => $frequencyPerDay
        ];

        // Calcular estadísticas globales (todos los chats)
        $globalStats = $this->calculateGlobalStats($now, $thirtyDaysAgo, $sixtyDaysAgo);

        return view('admin.chat', compact('contacts', 'contact', 'messages', 'stats', 'globalStats'));
    }

    /**
     * Calcular estadísticas globales de todos los chats
     */
    private function calculateGlobalStats($now, $thirtyDaysAgo, $sixtyDaysAgo)
    {
        // Total de mensajes globales
        $totalMessages = \App\Models\WhatsappMessage::count();
        $lastMonthMessages = \App\Models\WhatsappMessage::where('created_at', '>=', $thirtyDaysAgo)->count();
        $previousMonthMessages = \App\Models\WhatsappMessage::whereBetween('created_at', [$sixtyDaysAgo, $thirtyDaysAgo])->count();
        $messageGrowth = $previousMonthMessages > 0
            ? round((($lastMonthMessages - $previousMonthMessages) / $previousMonthMessages) * 100, 1)
            : 0;

        // Mensajes enviados vs recibidos
        $sentMessages = \App\Models\WhatsappMessage::where('sender_type', 'system')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        $receivedMessages = \App\Models\WhatsappMessage::where('sender_type', 'client')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        $sentReceivedRatio = $receivedMessages > 0 ? round($sentMessages / $receivedMessages, 2) : 0;

        // Última actividad global
        $lastMessage = \App\Models\WhatsappMessage::orderByDesc('created_at')->first();
        $lastActivity = $lastMessage ? $lastMessage->created_at->diffForHumans() : 'Nunca';
        $lastActivityDate = $lastMessage ? $lastMessage->created_at->format('d/m/Y H:i') : 'N/A';

        // Tiempo promedio de respuesta global
        $responseTimes = [];
        $clientMessages = \App\Models\WhatsappMessage::where('sender_type', 'client')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->orderBy('created_at')
            ->get();

        foreach ($clientMessages as $clientMsg) {
            $nextSystemMsg = \App\Models\WhatsappMessage::where('contact_id', $clientMsg->contact_id)
                ->where('sender_type', 'system')
                ->where('created_at', '>', $clientMsg->created_at)
                ->orderBy('created_at')
                ->first();

            if ($nextSystemMsg) {
                $responseTimes[] = $clientMsg->created_at->diffInMinutes($nextSystemMsg->created_at);
            }
        }

        $avgResponseTime = count($responseTimes) > 0
            ? round(array_sum($responseTimes) / count($responseTimes), 1) . 'm'
            : '0m';

        // Tasa de respuesta del cliente global
        $systemMessages = \App\Models\WhatsappMessage::where('sender_type', 'system')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->get();
        $clientResponses = 0;
        foreach ($systemMessages as $sysMsg) {
            $clientReply = \App\Models\WhatsappMessage::where('contact_id', $sysMsg->contact_id)
                ->where('sender_type', 'client')
                ->where('created_at', '>', $sysMsg->created_at)
                ->where('created_at', '<=', $sysMsg->created_at->copy()->addHours(24))
                ->first();
            if ($clientReply) {
                $clientResponses++;
            }
        }
        $clientResponseRate = $systemMessages->count() > 0
            ? round(($clientResponses / $systemMessages->count()) * 100, 1)
            : 0;

        // Hora pico global
        $messagesByHour = \App\Models\WhatsappMessage::where('created_at', '>=', $thirtyDaysAgo)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderByDesc('count')
            ->first();
        $peakHour = $messagesByHour ? $messagesByHour->hour . ':00' : 'N/A';

        // Día más activo
        $messagesByWeekday = \App\Models\WhatsappMessage::where('created_at', '>=', $thirtyDaysAgo)
            ->selectRaw('DAYOFWEEK(created_at) as weekday, COUNT(*) as count')
            ->groupBy('weekday')
            ->orderByDesc('count')
            ->first();
        $weekdayNames = ['', 'Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $mostActiveDay = $messagesByWeekday ? $weekdayNames[$messagesByWeekday->weekday] : 'N/A';

        // Total de contactos activos
        $activeContacts = \App\Models\WhatsappMessage::where('created_at', '>=', $thirtyDaysAgo)
            ->distinct('contact_id')
            ->count('contact_id');

        // Total de conversaciones globales
        $allMessages = \App\Models\WhatsappMessage::orderBy('created_at')->get();
        $conversations = 0;
        $lastMessageTime = null;
        $lastContactId = null;
        foreach ($allMessages as $msg) {
            if ($lastMessageTime === null || $lastContactId !== $msg->contact_id || $msg->created_at->diffInHours($lastMessageTime) >= 2) {
                if ($lastContactId !== $msg->contact_id) {
                    $conversations++;
                } else {
                    $conversations++;
                }
            }
            $lastMessageTime = $msg->created_at;
            $lastContactId = $msg->contact_id;
        }

        // Frecuencia diaria global
        $daysActive = \App\Models\WhatsappMessage::where('created_at', '>=', $thirtyDaysAgo)
            ->selectRaw('DATE(created_at) as date')
            ->distinct()
            ->count();
        $frequencyPerDay = $daysActive > 0
            ? round($lastMonthMessages / $daysActive, 1)
            : 0;

        // Distribución de tipos de mensajes global
        $messageTypes = \App\Models\WhatsappMessage::where('created_at', '>=', $thirtyDaysAgo)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type')
            ->toArray();

        // Mensajes por día (últimos 7 días) global
        $messagesByDay = [];
        $days = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $dayName = $days[$date->dayOfWeek];
            $sent = \App\Models\WhatsappMessage::where('sender_type', 'system')
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->count();
            $received = \App\Models\WhatsappMessage::where('sender_type', 'client')
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->count();
            $messagesByDay[] = [
                'day' => $dayName,
                'sent' => $sent,
                'received' => $received
            ];
        }

        // Mensajes con botones global
        $buttonMessages = \App\Models\WhatsappMessage::where('created_at', '>=', $thirtyDaysAgo)
            ->where(function($q) {
                $q->where('type', 'button')
                  ->orWhere('type', 'interactive')
                  ->orWhere(function($subQ) {
                      $subQ->whereRaw("JSON_VALID(content) = 1")
                           ->where(function($jsonQ) {
                               $jsonQ->whereRaw("JSON_EXTRACT(content, '$.type') = 'button_reply'")
                                     ->orWhereRaw("JSON_EXTRACT(content, '$.type') = 'list_reply'");
                           });
                  });
            })
            ->count();
        $buttonMessagesRate = $lastMonthMessages > 0 ? round(($buttonMessages / $lastMonthMessages) * 100, 1) : 0;

        // Tasa de interacción global
        $interactions = \App\Models\WhatsappMessage::where('sender_type', 'client')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        $totalOutbound = \App\Models\WhatsappMessage::where('sender_type', 'system')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        $interactionRate = $totalOutbound > 0 ? round(($interactions / $totalOutbound) * 100, 1) : 0;

        return [
            'totalMessages' => $totalMessages,
            'messageGrowth' => $messageGrowth,
            'sentMessages' => $sentMessages,
            'receivedMessages' => $receivedMessages,
            'sentReceivedRatio' => $sentReceivedRatio,
            'lastActivity' => $lastActivity,
            'lastActivityDate' => $lastActivityDate,
            'avgResponseTime' => $avgResponseTime,
            'clientResponseRate' => $clientResponseRate . '%',
            'peakHour' => $peakHour,
            'mostActiveDay' => $mostActiveDay,
            'activeContacts' => $activeContacts,
            'conversations' => $conversations,
            'frequencyPerDay' => $frequencyPerDay,
            'messageTypes' => $messageTypes,
            'messagesByDay' => $messagesByDay,
            'buttonMessagesRate' => $buttonMessagesRate . '%',
            'interactionRate' => $interactionRate . '%'
        ];
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $order = \App\Models\WhatsappCart::findOrFail($id);
        $order->status = $request->input('status');
        $order->save();
        return response()->json(['success' => true]);
    }

    public function contactDetails($id)
    {
        $contact = \App\Models\WhatsappContact::findOrFail($id);
        return response()->json($contact);
    }

    public function sendMessage(Request $request)
    {
        // Validar que al menos haya un mensaje, imagen o documento
        $hasMessage = $request->filled('message') && trim($request->input('message')) !== '';
        $hasImage = $request->hasFile('image');
        $hasDocument = $request->hasFile('document');

        if (!$hasMessage && !$hasImage && !$hasDocument) {
            return response()->json([
                'success' => false,
                'message' => 'Debe proporcionar un mensaje, imagen o documento.'
            ], 400);
        }

        $request->validate([
            'contact_id' => 'required|exists:whatsapp_contacts,id',
            'message' => 'nullable|string|max:4096',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            'audio' => 'nullable|mimes:mp3,ogg,wav,m4a,aac,webm|max:16384', // 16MB max para audio (incluye webm para grabaciones)
            'document' => 'nullable|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar|max:10240' // 10MB max para documentos
        ]);

        try {
            $contact = WhatsappContact::findOrFail($request->contact_id);
            $whatsappService = new WhatsappService();

            $hasImage = $request->hasFile('image');
            $hasAudio = $request->hasFile('audio');
            $hasDocument = $request->hasFile('document');
            $hasMessage = $request->filled('message') && trim($request->input('message')) !== '';
            $success = false;
            $message = null;

            if ($hasImage) {
                // Guardar la imagen temporalmente
                $imagePath = $request->file('image')->store('temp', 'public');
                $fullPath = storage_path('app/public/' . $imagePath);

                // Enviar imagen con o sin caption (marcar como enviado por humano)
                $success = $whatsappService->sendImageMessage(
                    $contact,
                    $fullPath,
                    $request->input('message'),
                    true // humanSent = true
                );

                // Eliminar archivo temporal
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }

                if ($success) {
                    $message = WhatsappMessage::where('contact_id', $contact->id)
                        ->whereIn('sender_type', ['system', 'humano'])
                        ->where('type', 'image')
                        ->latest()
                        ->first();
                }
            } elseif ($hasAudio) {
                // Guardar el audio temporalmente
                $audioPath = $request->file('audio')->store('temp', 'public');
                $fullPath = storage_path('app/public/' . $audioPath);
                $originalFilename = $request->file('audio')->getClientOriginalName();
                $extension = strtolower($request->file('audio')->getClientOriginalExtension());

                // Verificar formato compatible antes de enviar
                $allowedExtensions = ['mp3', 'ogg', 'wav', 'm4a', 'aac'];
                if (!in_array($extension, $allowedExtensions) && $extension !== 'webm') {
                    // Eliminar archivo temporal
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                    return response()->json([
                        'success' => false,
                        'message' => 'Formato de audio no compatible. Por favor, usa MP3, OGG, WAV, M4A o AAC.'
                    ], 400);
                }

                // Si es webm, advertir que puede fallar
                if ($extension === 'webm') {
                    Log::warning('Intento de enviar audio WebM', [
                        'contact_id' => $contact->id,
                        'filename' => $originalFilename
                    ]);
                }

                // Enviar audio (marcar como enviado por humano)
                $success = $whatsappService->sendAudioMessage(
                    $contact,
                    $fullPath,
                    $request->input('message'),
                    true // humanSent = true
                );

                // Eliminar archivo temporal
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }

                if ($success) {
                    $message = WhatsappMessage::where('contact_id', $contact->id)
                        ->whereIn('sender_type', ['system', 'humano'])
                        ->where('type', 'audio')
                        ->latest()
                        ->first();
                } else {
                    // Si falló y es webm, dar mensaje específico
                    if ($extension === 'webm') {
                        return response()->json([
                            'success' => false,
                            'message' => 'WhatsApp no acepta archivos WebM. Por favor, graba en formato OGG o sube un archivo MP3, OGG, WAV, M4A o AAC.'
                        ], 400);
                    }
                }
            } elseif ($hasDocument) {
                // Guardar el documento temporalmente
                $documentPath = $request->file('document')->store('temp', 'public');
                $fullPath = storage_path('app/public/' . $documentPath);
                $originalFilename = $request->file('document')->getClientOriginalName();

                // Enviar documento con o sin caption (marcar como enviado por humano)
                $success = $whatsappService->sendDocumentMessage(
                    $contact,
                    $fullPath,
                    $originalFilename,
                    $request->input('message'),
                    true // humanSent = true
                );

                // Eliminar archivo temporal
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }

                if ($success) {
                    $message = WhatsappMessage::where('contact_id', $contact->id)
                        ->whereIn('sender_type', ['system', 'humano'])
                        ->where('type', 'document')
                        ->latest()
                        ->first();
                }
            } elseif ($hasMessage) {
                // Enviar solo texto
                Log::info('Enviando mensaje de texto', [
                    'contact_id' => $contact->id,
                    'phone' => $contact->phone_number,
                    'message_length' => strlen($request->message)
                ]);

                // Enviar mensaje de texto (marcar como enviado por humano)
                $success = $whatsappService->sendTextMessage($contact, $request->message, true);

                if ($success) {
                    $message = WhatsappMessage::where('contact_id', $contact->id)
                        ->whereIn('sender_type', ['system', 'humano'])
                        ->where('type', 'text')
                        ->latest()
                        ->first();

                    Log::info('Mensaje enviado exitosamente', [
                        'message_id' => $message ? $message->id : null
                    ]);
                } else {
                    Log::error('Error al enviar mensaje de texto', [
                        'contact_id' => $contact->id
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe proporcionar un mensaje, imagen, audio o documento.'
                ], 400);
            }

            if ($success && $message) {
                $responseData = [
                    'id' => $message->id,
                    'content' => $message->content,
                    'type' => $message->type,
                    'created_at' => $message->created_at->format('Y-m-d H:i:s')
                ];

                // Agregar información adicional según el tipo
                if ($message->type === 'image') {
                    $metadata = $message->metadata ?? [];
                    $responseData['image_url'] = null;
                } elseif ($message->type === 'audio') {
                    $metadata = $message->metadata ?? [];
                    $responseData['filename'] = $metadata['filename'] ?? 'audio';
                } elseif ($message->type === 'document') {
                    $metadata = $message->metadata ?? [];
                    $responseData['filename'] = $metadata['filename'] ?? 'documento';
                }

                return response()->json([
                    'success' => true,
                    'message' => $responseData
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el mensaje. Por favor, intenta nuevamente.'
            ], 500);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . implode(', ', $e->errors())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error sending message from admin chat', [
                'error' => $e->getMessage(),
                'contact_id' => $request->contact_id ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el mensaje: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle el estado del bot para un contacto
     */
    public function toggleBot(Request $request, $contactId)
    {
        try {
            $request->validate([
                'enabled' => 'required|boolean'
            ]);

            $contact = WhatsappContact::findOrFail($contactId);
            $contact->bot_enabled = $request->enabled;
            $contact->save();

            Log::info('Estado del bot actualizado', [
                'contact_id' => $contactId,
                'bot_enabled' => $request->enabled
            ]);

            return response()->json([
                'success' => true,
                'message' => $request->enabled ? 'Bot activado' : 'Bot desactivado',
                'bot_enabled' => $contact->bot_enabled
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar estado del bot', [
                'error' => $e->getMessage(),
                'contact_id' => $contactId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado del bot: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getNewMessages($contactId, Request $request)
    {
        try {
            $lastMessageId = $request->input('last_message_id', 0);
            $lastTimestamp = $request->input('last_timestamp');

            $query = WhatsappMessage::where('contact_id', $contactId);

            // Si hay un timestamp, filtrar por mensajes más recientes
            if ($lastTimestamp) {
                $query->where('created_at', '>', $lastTimestamp);
            } elseif ($lastMessageId > 0) {
                // Si solo hay un ID, obtener mensajes después de ese ID
                $query->where('id', '>', $lastMessageId);
            } else {
                // Si no hay parámetros, obtener los últimos 10 mensajes
                $query->latest()->limit(10);
            }

            $newMessages = $query->orderBy('created_at')->get();

            return response()->json([
                'success' => true,
                'messages' => $newMessages->map(function($msg) {
                    $messageData = [
                        'id' => $msg->id,
                        'content' => $msg->content,
                        'type' => $msg->type,
                        'sender_type' => $msg->sender_type,
                        'metadata' => $msg->metadata,
                        'created_at' => $msg->created_at->toDateTimeString(),
                        'created_at_formatted' => $msg->created_at->format('H:i')
                    ];

                    // Agregar información adicional según el tipo
                    if ($msg->type === 'image') {
                        $messageData['has_image'] = true;
                    } elseif ($msg->type === 'document') {
                        $metadata = $msg->metadata ?? [];
                        $messageData['filename'] = $metadata['filename'] ?? 'documento';
                        $messageData['file_size'] = $metadata['file_size'] ?? null;
                    }

                    return $messageData;
                }),
                'count' => $newMessages->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo nuevos mensajes', [
                'contact_id' => $contactId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener nuevos mensajes'
            ], 500);
        }
    }

    public function getImage($messageId)
    {
        try {
            $message = WhatsappMessage::findOrFail($messageId);

            if ($message->type !== 'image') {
                // Retornar placeholder en lugar de error JSON
                $placeholderSvg = '<svg width="300" height="200" xmlns="http://www.w3.org/2000/svg"><rect width="300" height="200" fill="#202c33"/><text x="150" y="100" text-anchor="middle" fill="#8696a0" font-family="Arial" font-size="14">No es una imagen</text></svg>';
                return response($placeholderSvg, 200)
                    ->header('Content-Type', 'image/svg+xml')
                    ->header('Cache-Control', 'no-cache');
            }

            $metadata = $message->metadata ?? [];
            $mediaId = $metadata['media_id'] ?? null;

            if (!$mediaId) {
                // Retornar placeholder en lugar de error JSON
                $placeholderSvg = '<svg width="300" height="200" xmlns="http://www.w3.org/2000/svg"><rect width="300" height="200" fill="#202c33"/><text x="150" y="100" text-anchor="middle" fill="#8696a0" font-family="Arial" font-size="14">Imagen no disponible</text></svg>';
                return response($placeholderSvg, 200)
                    ->header('Content-Type', 'image/svg+xml')
                    ->header('Cache-Control', 'no-cache');
            }

            // Obtener la URL de la imagen desde WhatsApp Media API
            $response = \Illuminate\Support\Facades\Http::withToken(config('whatsapp.token'))
                ->timeout(5) // Timeout corto para no bloquear
                ->get("https://graph.facebook.com/" . config('whatsapp.api_version', 'v22.0') . "/{$mediaId}");

            if (!$response->successful()) {
                // Para cualquier error, retornar placeholder silenciosamente
                $placeholderSvg = '<svg width="300" height="200" xmlns="http://www.w3.org/2000/svg"><rect width="300" height="200" fill="#202c33"/><text x="150" y="100" text-anchor="middle" fill="#8696a0" font-family="Arial" font-size="14">Imagen no disponible</text></svg>';
                return response($placeholderSvg, 200)
                    ->header('Content-Type', 'image/svg+xml')
                    ->header('Cache-Control', 'no-cache');
            }

            $mediaData = $response->json();
            $imageUrl = $mediaData['url'] ?? null;

            if (!$imageUrl) {
                // Retornar placeholder en lugar de error
                $placeholderSvg = '<svg width="300" height="200" xmlns="http://www.w3.org/2000/svg"><rect width="300" height="200" fill="#202c33"/><text x="150" y="100" text-anchor="middle" fill="#8696a0" font-family="Arial" font-size="14">Imagen no disponible</text></svg>';
                return response($placeholderSvg, 200)
                    ->header('Content-Type', 'image/svg+xml')
                    ->header('Cache-Control', 'no-cache');
            }

            // Descargar la imagen desde WhatsApp
            $imageResponse = \Illuminate\Support\Facades\Http::withToken(config('whatsapp.token'))
                ->timeout(5) // Timeout corto
                ->get($imageUrl);

            if (!$imageResponse->successful()) {
                // Retornar placeholder en lugar de error
                $placeholderSvg = '<svg width="300" height="200" xmlns="http://www.w3.org/2000/svg"><rect width="300" height="200" fill="#202c33"/><text x="150" y="100" text-anchor="middle" fill="#8696a0" font-family="Arial" font-size="14">Imagen no disponible</text></svg>';
                return response($placeholderSvg, 200)
                    ->header('Content-Type', 'image/svg+xml')
                    ->header('Cache-Control', 'no-cache');
            }

            // Obtener el tipo de contenido
            $contentType = $imageResponse->header('Content-Type') ?? 'image/jpeg';

            // Retornar la imagen con los headers correctos
            return response($imageResponse->body(), 200)
                ->header('Content-Type', $contentType)
                ->header('Cache-Control', 'public, max-age=3600');

        } catch (\Exception $e) {
            // No loguear errores de imágenes expiradas para evitar spam en logs
            // Solo retornar placeholder silenciosamente
            $placeholderSvg = '<svg width="300" height="200" xmlns="http://www.w3.org/2000/svg"><rect width="300" height="200" fill="#202c33"/><text x="150" y="100" text-anchor="middle" fill="#8696a0" font-family="Arial" font-size="14">Imagen no disponible</text></svg>';
            return response($placeholderSvg, 200)
                ->header('Content-Type', 'image/svg+xml')
                ->header('Cache-Control', 'no-cache');
        }
    }
}
