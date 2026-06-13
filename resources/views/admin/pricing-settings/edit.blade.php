@extends('admin.layouts.app')

@section('header', 'Tarifas Meta / Consumo')

@section('content')
<div class="bg-white shadow-sm rounded-lg overflow-hidden max-w-4xl">
    <div class="p-6 border-b border-gray-100">
        <h2 class="text-lg font-semibold text-gray-900">Parámetros internos de costo WhatsApp</h2>
        <p class="text-sm text-gray-500 mt-1">
            Solo visible para super administradores. Activa solo los tipos de conversación que usa este cliente.
        </p>
    </div>

    <div class="p-6">
        @if(session('success'))
            <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('admin.pricing-settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-6 p-4 rounded-xl border border-emerald-200 bg-emerald-50">
                <h3 class="font-semibold text-gray-900 mb-2">Tipos de conversación activos</h3>
                <p class="text-sm text-gray-600 mb-3">Los desactivados no aparecen en el dashboard del cliente ni en la página de planes.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @php
                        $categoryLabels = [
                            'service' => '💬 Atención al cliente (cuando escriben)',
                            'utility' => '📋 Avisos automáticos del bot',
                            'marketing' => '📢 Promociones / campañas',
                            'authentication' => '🔐 Códigos de verificación (OTP)',
                        ];
                    @endphp
                    @foreach($categoryKeys as $key)
                        <label class="flex items-start gap-2 text-sm cursor-pointer">
                            <input type="checkbox" name="enabled_categories[]" value="{{ $key }}"
                                @checked(in_array($key, old('enabled_categories', $enabledCategories), true))
                                class="mt-1 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            <span>{{ $categoryLabels[$key] ?? ucfirst($key) }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Factor de ajuste interno</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="meta_markup" step="0.01" min="1" max="3"
                            value="{{ old('meta_markup', $settings->meta_markup) }}"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" required>
                        <span class="text-sm text-gray-500 whitespace-nowrap">× (ej. 1.30)</span>
                    </div>
                    @error('meta_markup')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Región</label>
                    <input type="text" name="region" value="{{ old('region', $settings->region) }}"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Moneda</label>
                    <input type="text" name="currency" maxlength="3" value="{{ old('currency', $settings->currency) }}"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm uppercase" required>
                </div>
            </div>

            <div class="space-y-5">
                @foreach($categoryKeys as $key)
                    @php
                        $meta = $categories[$key] ?? [];
                        $rate = $settings->rates[$key] ?? ['min' => 0, 'max' => 0];
                        $appliedMin = round(($rate['min'] ?? 0) * $settings->meta_markup, 4);
                        $appliedMax = round(($rate['max'] ?? 0) * $settings->meta_markup, 4);
                        $isEnabled = in_array($key, old('enabled_categories', $enabledCategories), true);
                    @endphp
                    <div class="border rounded-xl p-4 {{ $isEnabled ? 'border-gray-200 bg-gray-50' : 'border-dashed border-gray-300 bg-gray-100 opacity-80' }}">
                        <div class="flex items-start gap-3 mb-3">
                            <span class="text-2xl">{{ $meta['icon'] ?? '💬' }}</span>
                            <div>
                                <h3 class="font-semibold text-gray-900">
                                    {{ $meta['label'] ?? ucfirst($key) }}
                                    @unless($isEnabled)
                                        <span class="text-xs font-normal text-gray-500">(inactivo para el cliente)</span>
                                    @endunless
                                </h3>
                                <p class="text-sm text-gray-500">{{ $meta['description'] ?? '' }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase text-gray-500 mb-1">Tarifa base mín. (USD)</label>
                                <input type="number" name="rates[{{ $key }}][min]" step="0.0001" min="0"
                                    value="{{ old("rates.{$key}.min", $rate['min']) }}"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm bg-white" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase text-gray-500 mb-1">Tarifa base máx. (USD)</label>
                                <input type="number" name="rates[{{ $key }}][max]" step="0.0001" min="0"
                                    value="{{ old("rates.{$key}.max", $rate['max']) }}"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm bg-white" required>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            Aplicado al dashboard: <strong>${{ number_format($appliedMin, 4) }}</strong> — <strong>${{ number_format($appliedMax, 4) }}</strong> por conversación
                        </p>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex items-center justify-between">
                <p class="text-xs text-gray-500 max-w-md">
                    Puedes dejar tarifas de promociones y OTP guardadas aunque estén desactivadas, por si las activas después.
                </p>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg">
                    <i class="fas fa-save"></i> Guardar configuración
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
