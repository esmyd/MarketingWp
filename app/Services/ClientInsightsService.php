<?php

namespace App\Services;

use App\Models\WhatsappCart;
use App\Models\WhatsappContact;
use App\Models\WhatsappMessage;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientInsightsService
{
    public const SEGMENTS = [
        '' => 'Todos',
        'frequent_buyer' => 'Comprador frecuente',
        'vip' => 'Cliente VIP',
        'new' => 'Cliente nuevo',
        'inactive' => 'Inactivo (+30 días)',
        'needs_agent' => 'Requiere agente',
        'bot_off' => 'Bot desactivado',
        'has_orders' => 'Con pedidos',
        'no_orders' => 'Sin pedidos',
        'pending_reply' => 'Esperando respuesta',
    ];

    public const SORT_OPTIONS = [
        'recent' => 'Actividad más reciente',
        'orders_desc' => 'Más pedidos',
        'messages_desc' => 'Más mensajes',
        'spent_desc' => 'Mayor gasto',
        'name_asc' => 'Nombre A–Z',
    ];

    public function paginate(Request $request, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->baseQuery($request);
        $this->applyFilters($query, $request);
        $this->applySorting($query, $request->input('sort', 'recent'));

        return $query->paginate($perPage)->withQueryString();
    }

    public function summaryStats(Request $request): array
    {
        $base = $this->baseQuery($request);
        $this->applyFilters($base, $request, skipSegment: true);

        $activeSince = now()->subDays(7);

        return [
            'total' => (clone $base)->count(),
            'active_7d' => (clone $base)
                ->where($this->lastActivitySubquery(), '>=', $activeSince)
                ->count(),
            'frequent_buyers' => (clone $base)
                ->has('carts', '>=', 3, 'and', fn (Builder $q) => $q->reportable())
                ->count(),
            'needs_agent' => (clone $base)
                ->whereRaw("JSON_EXTRACT(metadata, '$.needs_agent') = true")
                ->count(),
        ];
    }

    public function contactDetail(WhatsappContact $contact): array
    {
        $contact = $this->loadContactMetrics($contact);

        $orders = WhatsappCart::reportable()
            ->with('items')
            ->where('contact_id', $contact->id)
            ->latest()
            ->limit(15)
            ->get();

        $recentMessages = WhatsappMessage::where('contact_id', $contact->id)
            ->with('adminUser:id,name')
            ->latest()
            ->limit(30)
            ->get()
            ->sortBy('created_at')
            ->values();

        $messagesByMonth = WhatsappMessage::where('contact_id', $contact->id)
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month')
            ->selectRaw('SUM(CASE WHEN sender_type = "client" THEN 1 ELSE 0 END) as inbound')
            ->selectRaw('SUM(CASE WHEN sender_type IN ("system", "humano") THEN 1 ELSE 0 END) as outbound')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'contact' => $contact,
            'indicators' => $this->indicators($contact),
            'orders' => $orders,
            'recent_messages' => $recentMessages,
            'messages_by_month' => $messagesByMonth,
            'response_rate' => $this->responseRateFromMetrics($contact),
        ];
    }

    /** @return array<int, array{key: string, label: string, tone: string, icon: string}> */
    public function indicators(object $contact): array
    {
        $badges = [];
        $orders = (int) ($contact->orders_count ?? 0);
        $spent = (float) ($contact->total_spent ?? 0);
        $recentOrders = (int) ($contact->recent_orders_count ?? 0);
        $lastActivity = $this->resolveLastActivity($contact);
        $createdAt = $contact->created_at ? Carbon::parse($contact->created_at) : null;

        if ($orders >= 5 || $spent >= 500) {
            $badges[] = ['key' => 'vip', 'label' => 'Cliente VIP', 'tone' => 'purple', 'icon' => 'fa-crown'];
        } elseif ($orders >= 3 || $recentOrders >= 2) {
            $badges[] = ['key' => 'frequent', 'label' => 'Comprador frecuente', 'tone' => 'green', 'icon' => 'fa-repeat'];
        }

        if ($createdAt && $createdAt->gte(now()->subDays(7)) && $orders <= 1) {
            $badges[] = ['key' => 'new', 'label' => 'Cliente nuevo', 'tone' => 'blue', 'icon' => 'fa-star'];
        }

        if (!empty($contact->needs_agent_flag)) {
            $badges[] = ['key' => 'agent', 'label' => 'Requiere agente', 'tone' => 'red', 'icon' => 'fa-headset'];
        }

        if ($contact->bot_enabled === false) {
            $badges[] = ['key' => 'bot_off', 'label' => 'Bot pausado', 'tone' => 'amber', 'icon' => 'fa-robot'];
        }

        if ($lastActivity && $lastActivity->lt(now()->subDays(30))) {
            $badges[] = ['key' => 'inactive', 'label' => 'Inactivo', 'tone' => 'gray', 'icon' => 'fa-moon'];
        }

        if ($this->isPendingReply($contact)) {
            $badges[] = ['key' => 'pending', 'label' => 'Esperando respuesta', 'tone' => 'orange', 'icon' => 'fa-clock'];
        }

        if ($orders === 0 && ((int) ($contact->client_messages_count ?? 0)) >= 5) {
            $badges[] = ['key' => 'no_purchase', 'label' => 'Sin compras', 'tone' => 'slate', 'icon' => 'fa-comment-dollar'];
        }

        if ($spent >= 100 && $orders >= 1) {
            $badges[] = ['key' => 'buyer', 'label' => 'Ha comprado', 'tone' => 'teal', 'icon' => 'fa-bag-shopping'];
        }

        return $badges;
    }

    private function baseQuery(Request $request): Builder
    {
        $ninetyDaysAgo = now()->subDays(90);

        return WhatsappContact::query()
            ->where(function (Builder $q) {
                $q->whereHas('messages')
                    ->orWhereHas('carts', fn (Builder $c) => $c->reportable());
            })
            ->where(function (Builder $q) {
                $q->whereNull('metadata')
                    ->orWhereRaw("JSON_EXTRACT(metadata, '$.role') IS NULL");
            })
            ->withCount([
                'messages as client_messages_count' => fn (Builder $q) => $q->where('sender_type', 'client'),
                'messages as replied_messages_count' => fn (Builder $q) => $q->whereIn('sender_type', ['system', 'humano']),
                'carts as orders_count' => fn (Builder $q) => $q->reportable(),
                'carts as recent_orders_count' => fn (Builder $q) => $q->reportable()->where('created_at', '>=', $ninetyDaysAgo),
            ])
            ->withSum([
                'carts as total_spent' => fn (Builder $q) => $q->reportable()
                    ->where('status', '!=', WhatsappCart::STATUS_CANCELLED),
            ], 'total')
            ->withMax([
                'messages as last_client_message_at' => fn (Builder $q) => $q->where('sender_type', 'client'),
            ], 'created_at')
            ->withMax([
                'messages as last_reply_message_at' => fn (Builder $q) => $q->whereIn('sender_type', ['system', 'humano']),
            ], 'created_at')
            ->withMax('messages as last_activity_at', 'created_at')
            ->addSelect(DB::raw(
                "CASE WHEN JSON_EXTRACT(whatsapp_contacts.metadata, '$.needs_agent') = true THEN 1 ELSE 0 END as needs_agent_flag"
            ));
    }

    private function applyFilters(Builder $query, Request $request, bool $skipSegment = false): void
    {
        if ($search = trim((string) $request->input('q', ''))) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('activity_from')) {
            $from = Carbon::parse($request->input('activity_from'))->startOfDay();
            $query->where($this->lastActivitySubquery(), '>=', $from);
        }

        if ($request->filled('activity_to')) {
            $to = Carbon::parse($request->input('activity_to'))->endOfDay();
            $query->where($this->lastActivitySubquery(), '<=', $to);
        }

        if ($request->filled('min_orders')) {
            $min = (int) $request->input('min_orders');
            $query->has('carts', '>=', $min, 'and', fn (Builder $q) => $q->reportable());
        }

        if (!$skipSegment && ($segment = $request->input('segment', ''))) {
            match ($segment) {
                'frequent_buyer' => $query->has('carts', '>=', 3, 'and', fn (Builder $q) => $q->reportable()),
                'vip' => $query->where(function (Builder $q) {
                    $q->has('carts', '>=', 5, 'and', fn (Builder $c) => $c->reportable())
                        ->orWhereRaw('(
                            SELECT COALESCE(SUM(total), 0) FROM whatsapp_carts
                            WHERE whatsapp_carts.contact_id = whatsapp_contacts.id
                            AND status NOT IN ("active", "abandoned", "cancelled")
                        ) >= 500');
                }),
                'new' => $query->where('whatsapp_contacts.created_at', '>=', now()->subDays(7)),
                'inactive' => $query->where(function (Builder $q) {
                    $q->where($this->lastActivitySubquery(), '<', now()->subDays(30))
                        ->orWhere(function (Builder $inner) {
                            $inner->whereDoesntHave('messages')
                                ->whereDoesntHave('carts', fn (Builder $c) => $c->reportable())
                                ->where('whatsapp_contacts.created_at', '<', now()->subDays(30));
                        });
                }),
                'needs_agent' => $query->whereRaw("JSON_EXTRACT(metadata, '$.needs_agent') = true"),
                'bot_off' => $query->where('bot_enabled', false),
                'has_orders' => $query->has('carts', '>=', 1, 'and', fn (Builder $q) => $q->reportable()),
                'no_orders' => $query->doesntHave('carts', 'and', fn (Builder $q) => $q->reportable()),
                'pending_reply' => $query->whereRaw($this->pendingReplySql()),
                default => null,
            };
        }
    }

    private function applySorting(Builder $query, string $sort): void
    {
        // Ordenar con subconsultas: los alias de withMax/withCount no existen en el COUNT de paginación.
        match ($sort) {
            'orders_desc' => $query
                ->orderBy($this->ordersCountSubquery(), 'desc')
                ->orderBy($this->lastActivitySubquery(), 'desc'),
            'messages_desc' => $query
                ->orderBy($this->clientMessagesCountSubquery(), 'desc')
                ->orderBy($this->lastActivitySubquery(), 'desc'),
            'spent_desc' => $query
                ->orderBy($this->totalSpentSubquery(), 'desc')
                ->orderBy($this->lastActivitySubquery(), 'desc'),
            'name_asc' => $query->orderByRaw('COALESCE(whatsapp_contacts.name, whatsapp_contacts.phone_number) ASC'),
            default => $query->orderBy($this->lastActivitySubquery(), 'desc'),
        };
    }

    private function enrichContact(WhatsappContact $contact): WhatsappContact
    {
        $enriched = $this->baseQuery(new Request())
            ->where('whatsapp_contacts.id', $contact->id)
            ->first();

        if (!$enriched) {
            return $this->loadContactMetrics($contact);
        }

        $enriched->pending_reply = $this->isPendingReply($enriched);

        return $enriched;
    }

    /** Métricas directas — fuente de verdad para el detalle del cliente. */
    private function loadContactMetrics(WhatsappContact $contact): WhatsappContact
    {
        $contactId = $contact->id;
        $ninetyDaysAgo = now()->subDays(90);

        $contact->client_messages_count = WhatsappMessage::where('contact_id', $contactId)
            ->where('sender_type', 'client')
            ->count();
        $contact->replied_messages_count = WhatsappMessage::where('contact_id', $contactId)
            ->whereIn('sender_type', ['system', 'humano'])
            ->count();
        $contact->orders_count = WhatsappCart::reportable()
            ->where('contact_id', $contactId)
            ->count();
        $contact->recent_orders_count = WhatsappCart::reportable()
            ->where('contact_id', $contactId)
            ->where('created_at', '>=', $ninetyDaysAgo)
            ->count();
        $contact->total_spent = (float) WhatsappCart::reportable()
            ->where('contact_id', $contactId)
            ->where('status', '!=', WhatsappCart::STATUS_CANCELLED)
            ->sum('total');
        $contact->last_client_message_at = WhatsappMessage::where('contact_id', $contactId)
            ->where('sender_type', 'client')
            ->max('created_at');
        $contact->last_reply_message_at = WhatsappMessage::where('contact_id', $contactId)
            ->whereIn('sender_type', ['system', 'humano'])
            ->max('created_at');
        $contact->last_activity_at = WhatsappMessage::where('contact_id', $contactId)
            ->max('created_at');
        $contact->needs_agent_flag = $contact->needsAgent() ? 1 : 0;
        $contact->pending_reply = $this->isPendingReply($contact);

        return $contact;
    }

    /** Subconsulta segura para filtrar/ordenar por última actividad (no es columna física). */
    private function lastActivitySubquery(): Builder
    {
        return WhatsappMessage::query()
            ->selectRaw('MAX(created_at)')
            ->whereColumn('whatsapp_messages.contact_id', 'whatsapp_contacts.id');
    }

    private function ordersCountSubquery(): Builder
    {
        return WhatsappCart::query()
            ->selectRaw('COUNT(*)')
            ->whereColumn('whatsapp_carts.contact_id', 'whatsapp_contacts.id')
            ->whereNotIn('status', ['active', 'abandoned']);
    }

    private function clientMessagesCountSubquery(): Builder
    {
        return WhatsappMessage::query()
            ->selectRaw('COUNT(*)')
            ->whereColumn('whatsapp_messages.contact_id', 'whatsapp_contacts.id')
            ->where('sender_type', 'client');
    }

    private function totalSpentSubquery(): Builder
    {
        return WhatsappCart::query()
            ->selectRaw('COALESCE(SUM(total), 0)')
            ->whereColumn('whatsapp_carts.contact_id', 'whatsapp_contacts.id')
            ->whereNotIn('status', ['active', 'abandoned'])
            ->where('status', '!=', WhatsappCart::STATUS_CANCELLED);
    }

    private function pendingReplySql(): string
    {
        return '(
            SELECT MAX(created_at) FROM whatsapp_messages wm1
            WHERE wm1.contact_id = whatsapp_contacts.id AND wm1.sender_type = "client"
        ) IS NOT NULL AND (
            SELECT MAX(created_at) FROM whatsapp_messages wm2
            WHERE wm2.contact_id = whatsapp_contacts.id AND wm2.sender_type IN ("system", "humano")
        ) IS NULL OR (
            SELECT MAX(created_at) FROM whatsapp_messages wm3
            WHERE wm3.contact_id = whatsapp_contacts.id AND wm3.sender_type = "client"
        ) > (
            SELECT MAX(created_at) FROM whatsapp_messages wm4
            WHERE wm4.contact_id = whatsapp_contacts.id AND wm4.sender_type IN ("system", "humano")
        )';
    }

    private function resolveLastActivity(object $contact): ?Carbon
    {
        if (!$contact->last_activity_at) {
            return null;
        }

        return Carbon::parse($contact->last_activity_at);
    }

    private function isPendingReply(object $contact): bool
    {
        if (!$contact->last_client_message_at) {
            return false;
        }

        $clientAt = Carbon::parse($contact->last_client_message_at);

        if (!$contact->last_reply_message_at) {
            return $clientAt->gte(now()->subHours(48));
        }

        return $clientAt->gt(Carbon::parse($contact->last_reply_message_at));
    }

    private function responseRateFromMetrics(object $contact): float
    {
        $inbound = (int) ($contact->client_messages_count ?? 0);
        if ($inbound === 0) {
            return 0;
        }

        $outbound = (int) ($contact->replied_messages_count ?? 0);

        return min(round(($outbound / $inbound) * 100, 1), 100);
    }

    private function responseRate(int $contactId): float
    {
        $inbound = WhatsappMessage::where('contact_id', $contactId)
            ->where('sender_type', 'client')
            ->count();

        if ($inbound === 0) {
            return 0;
        }

        $outbound = WhatsappMessage::where('contact_id', $contactId)
            ->whereIn('sender_type', ['system', 'humano'])
            ->count();

        return min(round(($outbound / $inbound) * 100, 1), 100);
    }
}
