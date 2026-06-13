<?php

namespace App\Services;

use App\Models\PricingSetting;
use App\Models\WhatsappChatbotConfig;
use App\Models\WhatsappMenuItem;
use App\Models\WhatsappPrice;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PlanLimitsService
{
    public function config(): ?WhatsappChatbotConfig
    {
        return WhatsappChatbotConfig::first();
    }

    public function planKey(): string
    {
        $key = $this->platformLimitsRaw()['subscription_plan'] ?? 'starter';
        $plans = config('pricing.plans', []);

        return array_key_exists($key, $plans) ? $key : 'starter';
    }

    public function planDefaults(?string $planKey = null): array
    {
        $key = $planKey ?? $this->planKey();

        return config("pricing.plans.{$key}.limits", config('pricing.plans.starter.limits', []));
    }

    public function allPlans(): array
    {
        return config('pricing.plans', []);
    }

    public function effectiveLimits(): array
    {
        $meta = $this->platformLimitsRaw();
        $defaults = $this->planDefaults();
        $planKey = $this->planKey();

        return [
            'plan_key' => $planKey,
            'plan_name' => config("pricing.plans.{$planKey}.name", 'Starter'),
            'plan_label' => config("pricing.plans.{$planKey}.label", 'Plan Esencial'),
            'max_products' => $this->resolveIntLimit($meta['max_products_limit'] ?? null, (int) ($defaults['max_products'] ?? 80)),
            'max_categories' => $this->resolveIntLimit($meta['max_categories_limit'] ?? null, (int) ($defaults['max_categories'] ?? 20)),
            'storage_gb' => $this->resolveFloatLimit($meta['storage_gb_limit'] ?? null, (float) ($defaults['storage_gb'] ?? 10)),
        ];
    }

    public function platformLimitsRaw(): array
    {
        $settings = PricingSetting::current();
        $stored = $settings->platform_limits;

        if (is_array($stored) && $stored !== []) {
            return $stored;
        }

        $legacy = $this->config()?->metadata ?? [];
        if (
            isset($legacy['subscription_plan'])
            || isset($legacy['max_products_limit'])
            || isset($legacy['max_categories_limit'])
            || isset($legacy['storage_gb_limit'])
        ) {
            return [
                'subscription_plan' => $legacy['subscription_plan'] ?? 'starter',
                'max_products_limit' => $legacy['max_products_limit'] ?? null,
                'max_categories_limit' => $legacy['max_categories_limit'] ?? null,
                'storage_gb_limit' => $legacy['storage_gb_limit'] ?? null,
            ];
        }

        return [];
    }

    public function savePlatformLimits(array $data): void
    {
        PricingSetting::current()->update([
            'platform_limits' => [
                'subscription_plan' => $data['subscription_plan'] ?? 'starter',
                'max_products_limit' => array_key_exists('max_products_limit', $data)
                    ? max(0, (int) $data['max_products_limit'])
                    : null,
                'max_categories_limit' => array_key_exists('max_categories_limit', $data)
                    ? max(0, (int) $data['max_categories_limit'])
                    : null,
                'storage_gb_limit' => array_key_exists('storage_gb_limit', $data)
                    ? max(0, (float) $data['storage_gb_limit'])
                    : null,
            ],
        ]);
    }

    public function usage(): array
    {
        $bytes = $this->calculateStorageBytes();

        return [
            'products' => WhatsappPrice::query()->count(),
            'categories' => WhatsappMenuItem::catalogCategories()->count(),
            'storage_bytes' => $bytes,
            'storage_gb' => $this->bytesToGb($bytes),
        ];
    }

    public function snapshot(): array
    {
        $limits = $this->effectiveLimits();
        $usage = $this->usage();

        $maxProducts = max(1, $limits['max_products']);
        $maxCategories = max(1, $limits['max_categories']);
        $maxStorageGb = max(0.01, $limits['storage_gb']);

        return array_merge($limits, [
            'usage' => $usage,
            'products_remaining' => max(0, $limits['max_products'] - $usage['products']),
            'categories_remaining' => max(0, $limits['max_categories'] - $usage['categories']),
            'storage_remaining_gb' => max(0, round($limits['storage_gb'] - $usage['storage_gb'], 2)),
            'products_at_limit' => $usage['products'] >= $limits['max_products'],
            'categories_at_limit' => $usage['categories'] >= $limits['max_categories'],
            'storage_at_limit' => $usage['storage_gb'] >= $limits['storage_gb'],
            'products_percent' => min(100, (int) round($usage['products'] / $maxProducts * 100)),
            'categories_percent' => min(100, (int) round($usage['categories'] / $maxCategories * 100)),
            'storage_percent' => min(100, (int) round($usage['storage_gb'] / $maxStorageGb * 100)),
        ]);
    }

    public function canCreateProduct(): bool
    {
        $snapshot = $this->snapshot();

        return $snapshot['usage']['products'] < $snapshot['max_products'];
    }

    public function canCreateCategory(): bool
    {
        $snapshot = $this->snapshot();

        return $snapshot['usage']['categories'] < $snapshot['max_categories'];
    }

    public function productLimitMessage(): string
    {
        $snapshot = $this->snapshot();

        return sprintf(
            'Has alcanzado el límite de %d productos (%d/%d). Contacta al administrador de la plataforma para ampliar tu plan.',
            $snapshot['max_products'],
            $snapshot['usage']['products'],
            $snapshot['max_products']
        );
    }

    public function categoryLimitMessage(): string
    {
        $snapshot = $this->snapshot();

        return sprintf(
            'Has alcanzado el límite de %d categorías (%d/%d). Contacta al administrador de la plataforma para ampliar tu plan.',
            $snapshot['max_categories'],
            $snapshot['usage']['categories'],
            $snapshot['max_categories']
        );
    }

    public function formatStorage(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0) . ' KB';
        }

        return $bytes . ' B';
    }

    private function resolveIntLimit(mixed $override, int $fallback): int
    {
        if ($override !== null && $override !== '') {
            return max(0, (int) $override);
        }

        return max(0, $fallback);
    }

    private function resolveFloatLimit(mixed $override, float $fallback): float
    {
        if ($override !== null && $override !== '') {
            return max(0, (float) $override);
        }

        return max(0, $fallback);
    }

    private function bytesToGb(int $bytes): float
    {
        return round($bytes / (1024 ** 3), 2);
    }

    private function calculateStorageBytes(): int
    {
        try {
            $disk = Storage::disk('public');
            $total = 0;

            foreach ($disk->allFiles() as $file) {
                $total += $disk->size($file);
            }

            return $total;
        } catch (\Throwable) {
            $path = storage_path('app/public');

            return is_dir($path) ? $this->directorySize($path) : 0;
        }
    }

    private function directorySize(string $path): int
    {
        $size = 0;

        foreach (File::allFiles($path) as $file) {
            $size += $file->getSize();
        }

        return $size;
    }
}
