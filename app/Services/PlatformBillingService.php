<?php

namespace App\Services;

use App\Models\PlatformPaymentReceipt;
use App\Models\PricingSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PlatformBillingService
{
    public function billingSettings(): array
    {
        $raw = app(PlanLimitsService::class)->platformLimitsRaw();
        $defaults = config('platform_billing.defaults', []);

        return array_merge($defaults, is_array($raw['billing'] ?? null) ? $raw['billing'] : []);
    }

    public function suspensionSettings(): array
    {
        $raw = app(PlanLimitsService::class)->platformLimitsRaw();
        $defaults = [
            'suspend_bot' => false,
            'suspend_chat' => false,
            'suspend_orders' => false,
            'auto_suspend_on_overdue' => true,
        ];

        return array_merge($defaults, is_array($raw['suspensions'] ?? null) ? $raw['suspensions'] : []);
    }

    public function saveBillingAndSuspensions(array $billing, array $suspensions): void
    {
        $limits = app(PlanLimitsService::class)->platformLimitsRaw();
        $limits['billing'] = array_merge($this->billingSettings(), $billing);
        $limits['suspensions'] = array_merge($this->suspensionSettings(), $suspensions);

        PricingSetting::current()->update(['platform_limits' => $limits]);
    }

    public function dashboardSnapshot(?float $metaEstimate = null): array
    {
        $billing = $this->billingSettings();
        $suspensions = $this->suspensionSettings();
        $payment = $this->paymentStatusForCurrentMonth();
        $autoOverdue = !empty($suspensions['auto_suspend_on_overdue'])
            && ($payment['plan_overdue'] || $payment['meta_overdue']);

        return [
            'plan_amount' => (float) ($billing['plan_amount'] ?? 0),
            'plan_due_date' => $payment['plan_due'],
            'plan_due_label' => $payment['plan_due']->format('d/m/Y'),
            'plan_paid' => $payment['plan_paid'],
            'plan_overdue' => $payment['plan_overdue'],
            'meta_due_date' => $payment['meta_due'],
            'meta_due_label' => $payment['meta_due']->format('d/m/Y'),
            'meta_paid' => $payment['meta_paid'],
            'meta_overdue' => $payment['meta_overdue'],
            'meta_estimate' => $metaEstimate,
            'auto_overdue' => $autoOverdue,
            'manual_suspend_bot' => !empty($suspensions['suspend_bot']),
            'manual_suspend_chat' => !empty($suspensions['suspend_chat']),
            'manual_suspend_orders' => !empty($suspensions['suspend_orders']),
            'auto_suspend_enabled' => !empty($suspensions['auto_suspend_on_overdue']),
            'bot_suspended' => $this->isBotSuspended(),
            'chat_suspended' => $this->isChatSuspended(),
            'orders_suspended' => $this->isOrdersSuspended(),
            'any_suspended' => $this->isBotSuspended() || $this->isChatSuspended() || $this->isOrdersSuspended(),
        ];
    }

    public function isBotSuspended(?User $user = null): bool
    {
        $s = $this->suspensionSettings();

        if (! empty($s['suspend_bot'])) {
            return true;
        }

        return $this->autoOverdueActive() && ! $this->userBypassesAutoSuspension($user);
    }

    public function isChatSuspended(?User $user = null): bool
    {
        $s = $this->suspensionSettings();

        if (! empty($s['suspend_chat'])) {
            return true;
        }

        return $this->autoOverdueActive() && ! $this->userBypassesAutoSuspension($user);
    }

    public function isOrdersSuspended(?User $user = null): bool
    {
        $s = $this->suspensionSettings();

        if (! empty($s['suspend_orders'])) {
            return true;
        }

        return $this->autoOverdueActive() && ! $this->userBypassesAutoSuspension($user);
    }

    public function botMayRespondToContact(object $contact): bool
    {
        if ($this->isBotSuspended(null)) {
            return false;
        }

        return (bool) ($contact->bot_enabled ?? true);
    }

    public function receiptsForWallet(int $limit = 50): Collection
    {
        return PlatformPaymentReceipt::query()
            ->with(['user:id,name', 'reviewer:id,name'])
            ->latest('paid_at')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function pendingReceiptsCount(): int
    {
        return PlatformPaymentReceipt::query()->where('status', PlatformPaymentReceipt::STATUS_PENDING)->count();
    }

    public function reviewReceipt(PlatformPaymentReceipt $receipt, string $status, ?User $reviewer, ?string $notes = null): PlatformPaymentReceipt
    {
        $receipt->update([
            'status' => $status,
            'review_notes' => $notes,
            'reviewed_by' => $reviewer?->id,
            'reviewed_at' => now(),
        ]);

        return $receipt->fresh(['user', 'reviewer']);
    }

    private function autoOverdueActive(): bool
    {
        if (empty($this->suspensionSettings()['auto_suspend_on_overdue'])) {
            return false;
        }

        $payment = $this->paymentStatusForCurrentMonth();

        return $payment['plan_overdue'] || $payment['meta_overdue'];
    }

    /** @return array{plan_due: Carbon, meta_due: Carbon, plan_paid: bool, meta_paid: bool, plan_overdue: bool, meta_overdue: bool} */
    private function paymentStatusForCurrentMonth(): array
    {
        $billing = $this->billingSettings();
        $now = now();
        $planDue = $this->dueDateForCurrentMonth((int) ($billing['plan_due_day'] ?? 5));
        $metaDue = $this->dueDateForCurrentMonth((int) ($billing['meta_due_day'] ?? 10));
        $planPaid = $this->hasApprovedPaymentForPeriod(PlatformPaymentReceipt::FOR_PLAN, $now)
            || $this->hasApprovedPaymentForPeriod(PlatformPaymentReceipt::FOR_BOTH, $now);
        $metaPaid = $this->hasApprovedPaymentForPeriod(PlatformPaymentReceipt::FOR_META, $now)
            || $this->hasApprovedPaymentForPeriod(PlatformPaymentReceipt::FOR_BOTH, $now);

        return [
            'plan_due' => $planDue,
            'meta_due' => $metaDue,
            'plan_paid' => $planPaid,
            'meta_paid' => $metaPaid,
            'plan_overdue' => ! $planPaid && $now->gt($planDue->copy()->endOfDay()),
            'meta_overdue' => ! $metaPaid && $now->gt($metaDue->copy()->endOfDay()),
        ];
    }

    private function userBypassesAutoSuspension(?User $user): bool
    {
        $user ??= auth()->user();

        return $user?->isSuperAdmin() ?? false;
    }

    private function dueDateForCurrentMonth(int $day): Carbon
    {
        $day = max(1, min(28, $day));
        $now = now();

        return Carbon::create($now->year, $now->month, $day)->startOfDay();
    }

    private function hasApprovedPaymentForPeriod(string $paymentFor, Carbon $date): bool
    {
        return PlatformPaymentReceipt::query()
            ->where('status', PlatformPaymentReceipt::STATUS_APPROVED)
            ->where('payment_for', $paymentFor)
            ->whereYear('paid_at', $date->year)
            ->whereMonth('paid_at', $date->month)
            ->exists();
    }
}
