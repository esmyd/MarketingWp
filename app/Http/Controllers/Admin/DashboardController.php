<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappCart;
use App\Models\WhatsappContact;
use App\Models\WhatsappMessage;
use App\Services\ConsumptionReportService;
use App\Services\PlanLimitsService;
use App\Services\PlatformBillingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request, ConsumptionReportService $consumption, PlanLimitsService $planLimits, PlatformBillingService $platformBilling)
    {
        [$from, $to, $periodPreset] = $this->resolveReportPeriod($request);

        $periodDays = max(1, $from->diffInDays($to) + 1);
        $prevTo = $from->copy()->subDay()->endOfDay();
        $prevFrom = $prevTo->copy()->subDays($periodDays - 1)->startOfDay();

        $metrics = $this->buildMetrics($from, $to, $prevFrom, $prevTo);
        $consumptionReport = $consumption->build($from, $to);
        $planLimits = $planLimits->snapshot();
        $platformBillingSnapshot = $platformBilling->dashboardSnapshot($consumptionReport['total_max'] ?? null);

        $orders = WhatsappCart::reportable()
            ->with(['contact'])
            ->whereBetween('created_at', [$from, $to])
            ->latest()
            ->limit(5)
            ->get();

        $totalOrdersAllTime = WhatsappCart::reportable()->count();

        return view('admin.dashboard', compact(
            'metrics',
            'consumptionReport',
            'from',
            'to',
            'periodPreset',
            'totalOrdersAllTime',
            'orders',
            'planLimits',
            'platformBillingSnapshot',
        ));
    }

    /**
     * Rango de fechas del reporte. Por defecto: todo el historial con datos (no solo 30 días).
     */
    private function resolveReportPeriod(Request $request): array
    {
        $preset = $request->input('period', 'month');
        $to = Carbon::now()->endOfDay();

        if ($request->filled('to')) {
            $to = Carbon::parse($request->input('to'))->endOfDay();
            $preset = 'custom';
        }

        if ($request->filled('from')) {
            $from = Carbon::parse($request->input('from'))->startOfDay();
            $preset = 'custom';

            if ($from->gt($to)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }

            return [$from, $to, $preset];
        }

        $from = match ($preset) {
            '7d' => $to->copy()->subDays(6)->startOfDay(),
            '30d' => $to->copy()->subDays(29)->startOfDay(),
            '90d' => $to->copy()->subDays(89)->startOfDay(),
            'month' => Carbon::now()->startOfMonth()->startOfDay(),
            'all' => $this->earliestReportDate() ?? $to->copy()->subDays(29)->startOfDay(),
            default => Carbon::now()->startOfMonth()->startOfDay(),
        };

        return [$from, $to, $preset];
    }

    private function earliestReportDate(): ?Carbon
    {
        $dates = collect([
            WhatsappCart::reportable()->min('created_at'),
            WhatsappMessage::min('created_at'),
        ])->filter();

        if ($dates->isEmpty()) {
            return null;
        }

        return Carbon::parse($dates->min())->startOfDay();
    }

    private function buildMetrics(Carbon $from, Carbon $to, Carbon $prevFrom, Carbon $prevTo): array
    {
        $periodMessages = WhatsappMessage::whereBetween('created_at', [$from, $to])->count();
        $prevPeriodMessages = WhatsappMessage::whereBetween('created_at', [$prevFrom, $prevTo])->count();
        $messageGrowth = $this->percentChange($prevPeriodMessages, $periodMessages);

        $received = WhatsappMessage::where('sender_type', 'client')
            ->whereBetween('created_at', [$from, $to])
            ->count();
        $sent = WhatsappMessage::whereIn('sender_type', ['system', 'humano'])
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $prevReceived = WhatsappMessage::where('sender_type', 'client')
            ->whereBetween('created_at', [$prevFrom, $prevTo])
            ->count();
        $prevSent = WhatsappMessage::whereIn('sender_type', ['system', 'humano'])
            ->whereBetween('created_at', [$prevFrom, $prevTo])
            ->count();

        $responseRate = $this->clientResponseRate($from, $to);
        $prevResponseRate = $this->clientResponseRate($prevFrom, $prevTo);
        $responseRateGrowth = $this->percentChange($prevResponseRate, $responseRate);

        $avgResponseTime = $this->averageResponseTimeMinutes($from, $to);
        $prevAvgResponseTime = $this->averageResponseTimeMinutes($prevFrom, $prevTo);
        $responseTimeGrowth = $prevAvgResponseTime > 0
            ? round((($avgResponseTime - $prevAvgResponseTime) / $prevAvgResponseTime) * 100, 1)
            : 0;

        $activeClients = (int) WhatsappMessage::whereBetween('created_at', [$from, $to])
            ->distinct()
            ->count('contact_id');

        $prevActiveClients = (int) WhatsappMessage::whereBetween('created_at', [$prevFrom, $prevTo])
            ->distinct()
            ->count('contact_id');
        $activeClientsGrowth = $this->percentChange($prevActiveClients, $activeClients);

        $ordersCount = WhatsappCart::reportable()
            ->whereBetween('created_at', [$from, $to])
            ->count();
        $prevOrdersCount = WhatsappCart::reportable()
            ->whereBetween('created_at', [$prevFrom, $prevTo])
            ->count();
        $ordersGrowth = $this->percentChange($prevOrdersCount, $ordersCount);

        $totalRevenue = (float) WhatsappCart::reportable()
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('total')
            ->where('status', '!=', WhatsappCart::STATUS_CANCELLED)
            ->sum('total');

        $prevRevenue = (float) WhatsappCart::reportable()
            ->whereBetween('created_at', [$prevFrom, $prevTo])
            ->whereNotNull('total')
            ->where('status', '!=', WhatsappCart::STATUS_CANCELLED)
            ->sum('total');
        $revenueGrowth = $this->percentChange($prevRevenue, $totalRevenue);

        $avgOrderValue = (float) WhatsappCart::reportable()
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('total')
            ->avg('total') ?? 0;

        $conversionRate = $received > 0
            ? min(round(($ordersCount / $received) * 100, 1), 100)
            : 0;

        $engagementRate = $sent > 0
            ? min(round(($received / $sent) * 100, 1), 100)
            : 0;

        $humanMessages = WhatsappMessage::where('sender_type', 'humano')
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $botMessages = WhatsappMessage::where('sender_type', 'system')
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $newContacts = WhatsappContact::whereBetween('created_at', [$from, $to])->count();
        $totalContacts = WhatsappContact::count();
        $totalHistoricalMessages = WhatsappMessage::count();

        $peakHourData = WhatsappMessage::whereBetween('created_at', [$from, $to])
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy('hour')
            ->orderByDesc('count')
            ->first();

        $peakHour = $peakHourData
            ? str_pad($peakHourData->hour, 2, '0', STR_PAD_LEFT) . ':00'
            : null;

        $messagesBySenderType = [
            'client' => $received,
            'system' => $botMessages,
            'humano' => $humanMessages,
        ];

        $collectedRevenue = (float) WhatsappCart::reportable()
            ->whereBetween('created_at', [$from, $to])
            ->whereIn('status', [
                WhatsappCart::STATUS_CONFIRMED,
                WhatsappCart::STATUS_COMPLETED,
                WhatsappCart::STATUS_PAID,
            ])
            ->sum('total');

        $pendingRevenue = max(0, $totalRevenue - $collectedRevenue);
        $collectionRate = $totalRevenue > 0
            ? round(($collectedRevenue / $totalRevenue) * 100, 1)
            : 0;

        return [
            'period_messages' => $periodMessages,
            'message_growth' => $messageGrowth,
            'received' => $received,
            'sent' => $sent,
            'response_rate' => $responseRate,
            'response_rate_growth' => $responseRateGrowth,
            'avg_response_time' => $avgResponseTime,
            'avg_response_time_formatted' => $this->formatMinutes($avgResponseTime),
            'response_time_growth' => $responseTimeGrowth,
            'active_clients' => $activeClients,
            'active_clients_growth' => $activeClientsGrowth,
            'orders_count' => $ordersCount,
            'orders_growth' => $ordersGrowth,
            'total_revenue' => $totalRevenue,
            'revenue_growth' => $revenueGrowth,
            'collected_revenue' => $collectedRevenue,
            'pending_revenue' => $pendingRevenue,
            'collection_rate' => $collectionRate,
            'avg_order_value' => $avgOrderValue,
            'conversion_rate' => $conversionRate,
            'engagement_rate' => $engagementRate,
            'human_messages' => $humanMessages,
            'bot_messages' => $botMessages,
            'new_contacts' => $newContacts,
            'total_contacts' => $totalContacts,
            'total_historical_messages' => $totalHistoricalMessages,
            'peak_hour' => $peakHour,
            'messages_by_sender' => $messagesBySenderType,
            'prev_period_messages' => $prevPeriodMessages,
        ];
    }

    /**
     * % de mensajes del cliente que recibieron respuesta (bot o humano) en 24 h.
     */
    private function clientResponseRate(Carbon $from, Carbon $to): float
    {
        $clientMessages = WhatsappMessage::where('sender_type', 'client')
            ->whereBetween('created_at', [$from, $to])
            ->get(['id', 'contact_id', 'created_at']);

        if ($clientMessages->isEmpty()) {
            return 0;
        }

        $answered = 0;
        foreach ($clientMessages as $msg) {
            $hasReply = WhatsappMessage::where('contact_id', $msg->contact_id)
                ->whereIn('sender_type', ['system', 'humano'])
                ->where('created_at', '>', $msg->created_at)
                ->where('created_at', '<=', $msg->created_at->copy()->addDay())
                ->exists();

            if ($hasReply) {
                $answered++;
            }
        }

        return round(($answered / $clientMessages->count()) * 100, 1);
    }

    /**
     * Minutos promedio entre el último mensaje del cliente y la respuesta (bot/humano).
     */
    private function averageResponseTimeMinutes(Carbon $from, Carbon $to): float
    {
        $outbound = WhatsappMessage::whereIn('sender_type', ['system', 'humano'])
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('contact_id')
            ->orderBy('created_at')
            ->get(['contact_id', 'created_at']);

        if ($outbound->isEmpty()) {
            return 0;
        }

        $diffs = [];
        foreach ($outbound as $reply) {
            $prevClientAt = WhatsappMessage::where('contact_id', $reply->contact_id)
                ->where('sender_type', 'client')
                ->where('created_at', '<', $reply->created_at)
                ->orderByDesc('created_at')
                ->value('created_at');

            if ($prevClientAt) {
                $minutes = Carbon::parse($prevClientAt)->diffInMinutes($reply->created_at);
                if ($minutes >= 0 && $minutes <= 10080) { // máx. 7 días, evita outliers
                    $diffs[] = $minutes;
                }
            }
        }

        return count($diffs) > 0 ? round(array_sum($diffs) / count($diffs), 1) : 0;
    }

    private function formatMinutes(?float $minutes): string
    {
        if (!$minutes || $minutes <= 0) {
            return '—';
        }

        if ($minutes < 60) {
            return round($minutes) . ' min';
        }

        $hours = floor($minutes / 60);
        $mins = round($minutes % 60);

        return $mins > 0 ? "{$hours} h {$mins} m" : "{$hours} h";
    }

    private function percentChange(float $previous, float $current): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
