<?php

namespace App\Services;

use App\Models\User;
use App\Models\WhatsappContact;
use App\Models\WhatsappMessage;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class UserActivityService
{
    public function dailyStatsForUser(User $user, ?Carbon $date = null): array
    {
        return $this->dailyStatsForUsers(collect([$user]), $date)->get($user->id, [
            'messages_sent' => 0,
            'clients_served' => 0,
            'agent_requests_closed' => 0,
        ]);
    }

    /**
     * @return Collection<int, array{messages_sent:int, clients_served:int, agent_requests_closed:int}>
     */
    public function dailyStatsForUsers(Collection $users, ?Carbon $date = null): Collection
    {
        if ($users->isEmpty()) {
            return collect();
        }

        $day = ($date ?? now())->copy()->startOfDay();
        $from = $day->copy();
        $to = $day->copy()->endOfDay();
        $userIds = $users->pluck('id')->map(fn ($id) => (int) $id)->all();

        $messageRows = WhatsappMessage::query()
            ->select('admin_user_id', 'contact_id')
            ->where('sender_type', 'humano')
            ->whereIn('admin_user_id', $userIds)
            ->whereBetween('created_at', [$from, $to])
            ->get()
            ->groupBy('admin_user_id');

        $agentClosed = WhatsappContact::query()
            ->select('id', 'metadata')
            ->where(function ($query) use ($userIds) {
                foreach ($userIds as $userId) {
                    $query->orWhereRaw(
                        "JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.agent_handled_by')) = ?",
                        [(string) $userId]
                    );
                }
            })
            ->get()
            ->groupBy(fn ($contact) => (int) ($contact->metadata['agent_handled_by'] ?? 0))
            ->map(function ($group) use ($from, $to) {
                return $group->filter(function ($contact) use ($from, $to) {
                    $handledAt = $contact->metadata['agent_handled_at'] ?? null;
                    if (!$handledAt) {
                        return false;
                    }

                    try {
                        $at = Carbon::parse($handledAt);
                    } catch (\Throwable) {
                        return false;
                    }

                    return $at->between($from, $to);
                });
            });

        return $users->mapWithKeys(function (User $user) use ($messageRows, $agentClosed) {
            $rows = $messageRows->get($user->id, collect());
            $closedContacts = $agentClosed->get($user->id, collect());

            $contactIds = $rows->pluck('contact_id')->filter()->unique()->values();
            $closedIds = $closedContacts->pluck('id')->filter()->unique()->values();
            $clientsServed = $contactIds->merge($closedIds)->unique()->count();

            return [$user->id => [
                'messages_sent' => $rows->count(),
                'clients_served' => $clientsServed,
                'agent_requests_closed' => $closedIds->count(),
            ]];
        });
    }
}
