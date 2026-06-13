<?php

namespace App\Services;

use App\Models\WhatsappMenuItem;
use App\Models\WhatsappPrice;
use Illuminate\Database\Eloquent\Builder;

class DemoClienteService
{
    public function __construct(
        private PlanLimitsService $planLimits
    ) {}

    public function activeKey(): ?string
    {
        $raw = $this->planLimits->platformLimitsRaw()['active_demo_cliente'] ?? null;

        if (!is_string($raw)) {
            return null;
        }

        $key = trim($raw);

        return $key !== '' ? $key : null;
    }

    public function saveActiveKey(?string $key): void
    {
        $this->planLimits->savePlatformLimits([
            'active_demo_cliente' => $key !== null && trim($key) !== '' ? trim($key) : null,
        ]);
    }

    /**
     * @return array<string, string> slug => label
     */
    public function options(): array
    {
        $labels = config('demo_clientes.labels', []);
        $fromDb = WhatsappMenuItem::query()
            ->whereNotNull('demo_cliente')
            ->where('demo_cliente', '!=', '')
            ->distinct()
            ->pluck('demo_cliente')
            ->merge(
                WhatsappPrice::query()
                    ->whereNotNull('demo_cliente')
                    ->where('demo_cliente', '!=', '')
                    ->distinct()
                    ->pluck('demo_cliente')
            )
            ->unique()
            ->sort()
            ->values();

        $options = [];
        foreach ($fromDb as $slug) {
            $options[$slug] = $labels[$slug] ?? $slug;
        }

        foreach ($labels as $slug => $label) {
            $options[$slug] = $label;
        }

        ksort($options);

        return $options;
    }

    public function applyCategoryScope(Builder $query): Builder
    {
        $active = $this->activeKey();

        if (!$active) {
            return $query;
        }

        return $query->where('demo_cliente', $active);
    }

    public function applyProductScope(Builder $query): Builder
    {
        $active = $this->activeKey();

        if (!$active) {
            return $query;
        }

        return $query->where('demo_cliente', $active);
    }
}
