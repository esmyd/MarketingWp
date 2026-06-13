<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingSetting;
use App\Services\PricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PricingSettingsController extends Controller
{
    public function edit(PricingService $pricing): View
    {
        $settings = $pricing->settings();
        $categories = config('pricing.meta_rates.per_conversation', []);
        $categoryKeys = PricingSetting::ALL_CATEGORIES;
        $enabledCategories = $settings->enabledCategories();

        return view('admin.pricing-settings.edit', compact('settings', 'categories', 'categoryKeys', 'enabledCategories'));
    }

    public function update(Request $request): RedirectResponse
    {
        $enabled = array_values(array_intersect(
            PricingSetting::ALL_CATEGORIES,
            $request->input('enabled_categories', [])
        ));

        if ($enabled === []) {
            return back()
                ->withInput()
                ->with('error', 'Debe habilitar al menos un tipo de conversación.');
        }

        $rules = [
            'meta_markup' => ['required', 'numeric', 'min:1', 'max:3'],
            'region' => ['required', 'string', 'max:120'],
            'currency' => ['required', 'string', 'size:3'],
            'rates' => ['required', 'array'],
            'enabled_categories' => ['nullable', 'array'],
            'enabled_categories.*' => ['in:' . implode(',', PricingSetting::ALL_CATEGORIES)],
        ];

        foreach (PricingSetting::ALL_CATEGORIES as $key) {
            $rules["rates.{$key}.min"] = ['required', 'numeric', 'min:0'];
            $rules["rates.{$key}.max"] = ['required', 'numeric', 'min:0', "gte:rates.{$key}.min"];
        }

        $validated = $request->validate($rules);

        $settings = PricingSetting::current();
        $settings->update([
            'meta_markup' => $validated['meta_markup'],
            'region' => $validated['region'],
            'currency' => strtoupper($validated['currency']),
            'rates' => PricingSetting::normalizeRates($validated['rates']),
            'enabled_categories' => $enabled,
        ]);

        return redirect()
            ->route('admin.pricing-settings.edit')
            ->with('success', 'Tarifas y tipos de conversación actualizados correctamente.');
    }
}
