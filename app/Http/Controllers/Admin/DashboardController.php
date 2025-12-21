<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WhatsappMessage;
use App\Models\WhatsappContact;
use App\Models\WhatsappConversation;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Período actual (últimos 30 días)
        $now = Carbon::now();
        $thirtyDaysAgo = $now->copy()->subDays(30);
        $sixtyDaysAgo = $now->copy()->subDays(60);

        // Total de mensajes
        $totalMessages = WhatsappMessage::count();
        $lastMonthMessages = WhatsappMessage::where('created_at', '>=', $thirtyDaysAgo)->count();
        $previousMonthMessages = WhatsappMessage::whereBetween('created_at', [$sixtyDaysAgo, $thirtyDaysAgo])->count();
        $messageGrowth = $previousMonthMessages > 0
            ? (($lastMonthMessages - $previousMonthMessages) / $previousMonthMessages) * 100
            : 0;

        // Tasa de respuesta (incluye system y humano)
        $totalResponses = WhatsappMessage::whereIn('sender_type', ['system', 'humano'])
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        $totalInbound = WhatsappMessage::where('sender_type', 'client')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        $responseRate = $totalInbound > 0 ? min(($totalResponses / $totalInbound) * 100, 100) : 0;

        $previousResponses = WhatsappMessage::whereIn('sender_type', ['system', 'humano'])
            ->whereBetween('created_at', [$sixtyDaysAgo, $thirtyDaysAgo])
            ->count();
        $previousInbound = WhatsappMessage::where('sender_type', 'client')
            ->whereBetween('created_at', [$sixtyDaysAgo, $thirtyDaysAgo])
            ->count();
        $previousResponseRate = $previousInbound > 0 ? min(($previousResponses / $previousInbound) * 100, 100) : 0;
        $responseRateGrowth = $previousResponseRate > 0
            ? (($responseRate - $previousResponseRate) / $previousResponseRate) * 100
            : 0;

        // Tiempo promedio de respuesta usando subconsulta (incluye system y humano)
        $avgResponseTime = DB::table('whatsapp_messages as wm1')
            ->join('whatsapp_messages as wm2', function($join) {
                $join->on('wm1.contact_id', '=', 'wm2.contact_id')
                    ->where('wm2.created_at', '<', DB::raw('wm1.created_at'))
                    ->where('wm2.sender_type', '=', 'client');
            })
            ->whereIn('wm1.sender_type', ['system', 'humano'])
            ->where('wm1.created_at', '>=', $thirtyDaysAgo)
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, wm2.created_at, wm1.created_at)) as avg_time'))
            ->value('avg_time') ?? 0;

        $previousAvgResponseTime = DB::table('whatsapp_messages as wm1')
            ->join('whatsapp_messages as wm2', function($join) {
                $join->on('wm1.contact_id', '=', 'wm2.contact_id')
                    ->where('wm2.created_at', '<', DB::raw('wm1.created_at'))
                    ->where('wm2.sender_type', '=', 'client');
            })
            ->whereIn('wm1.sender_type', ['system', 'humano'])
            ->whereBetween('wm1.created_at', [$sixtyDaysAgo, $thirtyDaysAgo])
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, wm2.created_at, wm1.created_at)) as avg_time'))
            ->value('avg_time') ?? 0;

        $responseTimeGrowth = $previousAvgResponseTime > 0
            ? (($avgResponseTime - $previousAvgResponseTime) / $previousAvgResponseTime) * 100
            : 0;

        // Clientes activos (contactos únicos con mensajes en los últimos 30 días)
        $activeClients = DB::table('whatsapp_messages')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->distinct('contact_id')
            ->count('contact_id');
        $previousActiveClients = DB::table('whatsapp_messages')
            ->whereBetween('created_at', [$sixtyDaysAgo, $thirtyDaysAgo])
            ->distinct('contact_id')
            ->count('contact_id');
        $activeClientsGrowth = $previousActiveClients > 0
            ? (($activeClients - $previousActiveClients) / $previousActiveClients) * 100
            : 0;

        // Mensajes con botones - limitado a 100%
        $buttonMessages = WhatsappMessage::where('type', 'button')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        $totalMessagesLastMonth = WhatsappMessage::where('created_at', '>=', $thirtyDaysAgo)->count();
        $buttonMessagesRate = $totalMessagesLastMonth > 0 ? min(($buttonMessages / $totalMessagesLastMonth) * 100, 100) : 0;

        $previousButtonMessages = WhatsappMessage::where('type', 'button')
            ->whereBetween('created_at', [$sixtyDaysAgo, $thirtyDaysAgo])
            ->count();
        $previousTotalMessages = WhatsappMessage::whereBetween('created_at', [$sixtyDaysAgo, $thirtyDaysAgo])->count();
        $previousButtonRate = $previousTotalMessages > 0 ? min(($previousButtonMessages / $previousTotalMessages) * 100, 100) : 0;
        $buttonMessagesGrowth = $previousButtonRate > 0
            ? (($buttonMessagesRate - $previousButtonRate) / $previousButtonRate) * 100
            : 0;

        // Tasa de interacción (incluye system y humano)
        $interactions = WhatsappMessage::where('sender_type', 'client')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        $totalOutbound = WhatsappMessage::whereIn('sender_type', ['system', 'humano'])
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        $interactionRate = $totalOutbound > 0 ? min(($interactions / $totalOutbound) * 100, 100) : 0;

        $previousInteractions = WhatsappMessage::where('sender_type', 'client')
            ->whereBetween('created_at', [$sixtyDaysAgo, $thirtyDaysAgo])
            ->count();
        $previousOutbound = WhatsappMessage::whereIn('sender_type', ['system', 'humano'])
            ->whereBetween('created_at', [$sixtyDaysAgo, $thirtyDaysAgo])
            ->count();
        $previousInteractionRate = $previousOutbound > 0 ? min(($previousInteractions / $previousOutbound) * 100, 100) : 0;
        $interactionRateGrowth = $previousInteractionRate > 0
            ? (($interactionRate - $previousInteractionRate) / $previousInteractionRate) * 100
            : 0;

        // Datos para gráficos
        $messagesData = WhatsappMessage::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(CASE WHEN sender_type IN ("system", "humano") THEN 1 END) as sent'),
            DB::raw('COUNT(CASE WHEN sender_type = "client" THEN 1 END) as received')
        )
        ->where('created_at', '>=', $thirtyDaysAgo)
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        // Tiempo de respuesta por día usando subconsulta
        $responseTimeData = DB::table('whatsapp_messages as wm1')
            ->join('whatsapp_messages as wm2', function($join) {
                $join->on('wm1.contact_id', '=', 'wm2.contact_id')
                    ->where('wm2.created_at', '<', DB::raw('wm1.created_at'))
                    ->where('wm2.sender_type', '=', 'client');
            })
            ->where('wm1.sender_type', 'system')
            ->where('wm1.created_at', '>=', $thirtyDaysAgo)
            ->select(
                DB::raw('DATE(wm1.created_at) as date'),
                DB::raw('AVG(TIMESTAMPDIFF(MINUTE, wm2.created_at, wm1.created_at)) as avg_time')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Temas más frecuentes
        $topTopics = WhatsappMessage::select('type', DB::raw('COUNT(*) as count'))
            ->whereNotNull('type')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->groupBy('type')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->type,
                    'count' => $item->count
                ];
            });

        // Pedidos recientes (carritos completados)
        $orders = \App\Models\WhatsappCart::with(['items', 'contact'])
           // ->where('status', 'completed')
            ->latest()
            ->limit(5)
            ->get();

        // Mensajes recientes
        $messages = WhatsappMessage::with('contact')
            ->latest()
            ->limit(5)
            ->get();

        // Clientes nuevos este mes
        $newClientsThisMonth = DB::table('whatsapp_contacts')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        // Pedidos de este mes (carritos completados)
        $ordersThisMonth = DB::table('whatsapp_carts')
            //->where('status', 'completed')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        // Productos más pedidos este mes (por nombre)
        $topProducts = DB::table('whatsapp_cart_items')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->select('name', DB::raw('SUM(quantity) as total'))
            ->groupBy('name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Mensajes por hora este mes
        $messagesByHour = DB::table('whatsapp_messages')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as total'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Opciones más consultadas este mes (por type)
        $topOptions = DB::table('whatsapp_messages')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->whereNotNull('type')
            ->select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Imágenes más enviadas este mes (por nombre si está en metadata, si no solo cantidad)
        $topImages = DB::table('whatsapp_messages')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->where('type', 'image')
            ->select(DB::raw('COUNT(*) as total'))
            ->first();

        // MÉTRICAS ADICIONALES PARA TOMA DE DECISIONES
        
        // 1. Mensajes enviados vs recibidos (últimos 30 días)
        $sentMessages = WhatsappMessage::whereIn('sender_type', ['system', 'humano'])
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        $receivedMessages = WhatsappMessage::where('sender_type', 'client')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        $sentReceivedRatio = $receivedMessages > 0 ? round(($sentMessages / $receivedMessages) * 100, 1) : 0;

        // 1.1. Iteraciones por Cliente (promedio de mensajes por cliente)
        $iterationsPerClient = $activeClients > 0 ? round($receivedMessages / $activeClients, 2) : 0;

        // 1.2. Iteraciones por Humano (promedio de mensajes humanos por cliente atendido)
        $humanMessages = WhatsappMessage::where('sender_type', 'humano')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        $clientsWithHumanMessages = DB::table('whatsapp_messages')
            ->where('sender_type', 'humano')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->distinct('contact_id')
            ->count('contact_id');
        $iterationsPerHuman = $clientsWithHumanMessages > 0 ? round($humanMessages / $clientsWithHumanMessages, 2) : 0;

        // 2. Tasa de conversión (pedidos / mensajes de clientes) - limitado a 100%
        $conversionRate = $receivedMessages > 0 ? min(round(($ordersThisMonth / $receivedMessages) * 100, 2), 100) : 0;

        // 3. Valor promedio de pedido
        $avgOrderValue = DB::table('whatsapp_carts')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->whereNotNull('total')
            ->avg('total') ?? 0;

        // 4. Ingresos totales del mes
        $totalRevenue = DB::table('whatsapp_carts')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->whereNotNull('total')
            ->sum('total') ?? 0;

        // 5. Tasa de respuesta del cliente (cuántos responden a nuestros mensajes) - limitado a 100%
        $systemMessages = WhatsappMessage::whereIn('sender_type', ['system', 'humano'])
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->get();
        $clientResponses = 0;
        foreach ($systemMessages as $sysMsg) {
            $clientReply = WhatsappMessage::where('contact_id', $sysMsg->contact_id)
                ->where('sender_type', 'client')
                ->where('created_at', '>', $sysMsg->created_at)
                ->where('created_at', '<=', $sysMsg->created_at->copy()->addHours(24))
                ->first();
            if ($clientReply) {
                $clientResponses++;
            }
        }
        $clientResponseRate = $systemMessages->count() > 0 
            ? min(round(($clientResponses / $systemMessages->count()) * 100, 1), 100) 
            : 0;

        // 6. Hora pico de actividad
        $peakHourData = DB::table('whatsapp_messages')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy('hour')
            ->orderByDesc('count')
            ->first();
        $peakHour = $peakHourData ? $peakHourData->hour . ':00' : 'N/A';

        // 7. Día más activo de la semana
        $peakDayData = DB::table('whatsapp_messages')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->select(DB::raw('DAYOFWEEK(created_at) as weekday'), DB::raw('COUNT(*) as count'))
            ->groupBy('weekday')
            ->orderByDesc('count')
            ->first();
        $weekdayNames = ['', 'Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $peakDay = $peakDayData ? $weekdayNames[$peakDayData->weekday] : 'N/A';

        // 8. Distribución de tipos de mensajes (para gráfico)
        $messageTypesDistribution = WhatsappMessage::where('created_at', '>=', $thirtyDaysAgo)
            ->select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type')
            ->toArray();

        // 9. Mensajes por día de la semana (últimos 7 días)
        $messagesByWeekday = [];
        $days = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $dayName = $days[$date->dayOfWeek];
            $count = WhatsappMessage::whereDate('created_at', $date->format('Y-m-d'))->count();
            $messagesByWeekday[] = [
                'day' => $dayName,
                'count' => $count
            ];
        }

        // 10.1. Mensajes por tipo de remitente (últimos 30 días)
        $messagesBySenderType = [
            'client' => WhatsappMessage::where('sender_type', 'client')
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->count(),
            'system' => WhatsappMessage::where('sender_type', 'system')
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->count(),
            'humano' => WhatsappMessage::where('sender_type', 'humano')
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->count()
        ];

        // 10. Total de contactos únicos
        $totalContacts = WhatsappContact::count();
        $contactsWithMessages = DB::table('whatsapp_messages')
            ->distinct('contact_id')
            ->count('contact_id');

        return view('admin.dashboard', compact(
            'totalMessages',
            'messageGrowth',
            'responseRate',
            'responseRateGrowth',
            'avgResponseTime',
            'responseTimeGrowth',
            'activeClients',
            'activeClientsGrowth',
            'buttonMessagesRate',
            'buttonMessagesGrowth',
            'interactionRate',
            'interactionRateGrowth',
            'messagesData',
            'responseTimeData',
            'topTopics',
            'orders',
            'messages',
            'newClientsThisMonth',
            'ordersThisMonth',
            'topProducts',
            'messagesByHour',
            'topOptions',
            'topImages',
            'sentMessages',
            'receivedMessages',
            'sentReceivedRatio',
            'conversionRate',
            'avgOrderValue',
            'totalRevenue',
            'clientResponseRate',
            'peakHour',
            'peakDay',
            'messageTypesDistribution',
            'messagesByWeekday',
            'totalContacts',
            'contactsWithMessages',
            'iterationsPerClient',
            'iterationsPerHuman',
            'humanMessages',
            'clientsWithHumanMessages',
            'messagesBySenderType'
        ));
    }
}
