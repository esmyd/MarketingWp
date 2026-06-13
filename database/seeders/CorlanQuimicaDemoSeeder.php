<?php

namespace Database\Seeders;

use App\Enums\MarketingStepKey;
use App\Models\MarketingFlow;
use App\Models\MarketingFlowStep;
use App\Models\PricingSetting;
use App\Models\WhatsappBusinessProfile;
use App\Models\WhatsappChatbotConfig;
use App\Models\WhatsappChatbotResponse;
use App\Models\WhatsappMenu;
use App\Models\WhatsappMenuItem;
use App\Models\WhatsappPrice;
use App\Services\DemoClienteService;
use App\Services\OrderPdfSettingsService;
use App\Services\PlanLimitsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CorlanQuimicaDemoSeeder extends Seeder
{
    private const DEMO_KEY = 'CorlanQuimica';

    public function run(): void
    {
        $this->tagLegacyCatalogs();
        $this->seedCorlanCatalog();
        $this->configureBusiness();
        $this->configureChatbot();
        $this->configureMarketingFlow();
        $this->configureResponses();
        $this->configurePdf();
        $this->activateDemo();

        $this->command?->info('Demo CorlanQuimica lista. Catálogo activo: ' . self::DEMO_KEY);
    }

    private function tagLegacyCatalogs(): void
    {
        $softwareActions = [
            'software', 'desarrollo_web', 'ecommerce', 'chatbots', 'automatizacion', 'apps_moviles',
        ];

        $herbalifeActions = [
            'precios_nutricion', 'precios_bienestar', 'precios_cuidado', 'precios_energia',
            'nutricion', 'bienestar', 'cuidado_personal', 'energia',
        ];

        WhatsappMenuItem::query()
            ->whereIn('action_id', $softwareActions)
            ->update(['demo_cliente' => 'software', 'is_active' => false]);

        WhatsappMenuItem::query()
            ->whereIn('action_id', $herbalifeActions)
            ->update(['demo_cliente' => 'herbalife', 'is_active' => false]);

        WhatsappPrice::query()
            ->whereHas('menuCategory', fn ($q) => $q->whereIn('action_id', $softwareActions))
            ->update(['demo_cliente' => 'software', 'is_active' => false]);

        WhatsappPrice::query()
            ->whereHas('menuCategory', fn ($q) => $q->whereIn('action_id', $herbalifeActions))
            ->update(['demo_cliente' => 'herbalife', 'is_active' => false]);

        WhatsappPrice::query()
            ->where(function ($q) {
                $q->where('sku', 'like', 'SW-%')
                    ->orWhere('sku', 'like', 'WEB-%')
                    ->orWhere('sku', 'like', 'EC-%')
                    ->orWhere('sku', 'like', 'BOT-%')
                    ->orWhere('sku', 'like', 'AUTO-%')
                    ->orWhere('sku', 'like', 'APP-%');
            })
            ->update(['demo_cliente' => 'software', 'is_active' => false]);

        WhatsappPrice::query()
            ->where(function ($q) {
                $q->where('name', 'like', '%Herbalife%')
                    ->orWhere('sku', 'like', '100%')
                    ->orWhere('sku', 'like', '200%')
                    ->orWhere('sku', 'like', '300%');
            })
            ->whereNull('demo_cliente')
            ->update(['demo_cliente' => 'herbalife', 'is_active' => false]);
    }

    private function seedCorlanCatalog(): void
    {
        $profileId = WhatsappBusinessProfile::first()?->id ?? 1;

        $mainMenu = WhatsappMenu::updateOrCreate(
            ['action_id' => 'prices_menu'],
            [
                'business_profile_id' => $profileId,
                'title' => 'Catálogo Corlan Química',
                'description' => 'Productos de limpieza, químicos e insumos industriales',
                'type' => 'list',
                'content' => "*Catálogo Corlan Química S.A.*\n\nSoluciones integrales en limpieza, higiene industrial, químicos, insumos de cafetería, oficina, sector bananero y equipos de protección personal.\n\nSeleccione una categoría o indique el código SKU del producto.",
                'button_text' => 'Ver categorías',
                'icon' => '🧪',
                'order' => 1,
                'is_active' => true,
            ]
        );

        $catalog = require __DIR__ . '/data/corlanquimica_catalog.php';
        $skuCounter = 1;

        foreach ($catalog as $index => $category) {
            $menuItem = WhatsappMenuItem::updateOrCreate(
                [
                    'menu_id' => $mainMenu->id,
                    'action_id' => $category['action_id'],
                ],
                [
                    'title' => $category['title'],
                    'description' => $category['description'],
                    'icon' => $category['icon'],
                    'order' => $index + 1,
                    'is_active' => true,
                    'demo_cliente' => self::DEMO_KEY,
                ]
            );

            foreach ($category['products'] as $product) {
                $sku = 'CQ' . str_pad((string) $skuCounter, 3, '0', STR_PAD_LEFT);
                $skuCounter++;

                WhatsappPrice::updateOrCreate(
                    ['sku' => $sku],
                    [
                        'menu_item_id' => $menuItem->id,
                        'category' => $category['title'],
                        'name' => Str::limit($product['name'], 24, ''),
                        'description' => $product['desc'] . ' — Presentación: ' . $product['qty'],
                        'price' => (float) $product['price'],
                        'promo_price' => null,
                        'is_promo' => false,
                        'currency' => 'USD',
                        'is_active' => true,
                        'demo_cliente' => self::DEMO_KEY,
                        'stock' => 500,
                        'allow_quantity_selection' => true,
                        'min_quantity' => 1,
                        'max_quantity' => 999,
                        'characteristics' => [
                            'Presentación: ' . $product['unit'],
                            'Medida: ' . $product['qty'],
                        ],
                        'metadata' => [
                            'quantity' => $product['qty'],
                            'format' => $product['unit'],
                            'demo' => true,
                        ],
                    ]
                );
            }
        }
    }

    private function configureBusiness(): void
    {
        $profile = WhatsappBusinessProfile::first();
        if (!$profile) {
            return;
        }

        $profile->update([
            'business_name' => 'Corlan Química S.A.',
            'display_name' => 'Corlan Química',
            'metadata' => array_merge(is_array($profile->metadata) ? $profile->metadata : [], [
                'legal_name' => 'Corlan Química S.A.',
                'trade_name' => 'Corlan Química',
                'ruc' => '',
                'address' => 'Ciudadela Guayacanes, Edificio 64, Oficina 24, Guayaquil',
                'city' => 'Guayaquil, Ecuador',
                'email' => 'ventas@corlanquimica.com',
                'website' => 'https://corlanquimica.com',
                'phone_office' => '04-507-2574',
                'whatsapp' => '+593 98 105 4219',
                'business_hours' => 'Lunes a Viernes, 8:00 am – 5:00 pm',
            ]),
        ]);

        WhatsappMenu::where('action_id', 'menu_productos')->update([
            'title' => 'Catálogo de productos',
            'content' => 'Consulte nuestras líneas de limpieza industrial, químicos, insumos corporativos y EPP.',
            'button_text' => 'Ver catálogo',
        ]);

        WhatsappMenu::where('action_id', 'main_menu')->update([
            'content' => 'Bienvenido a Corlan Química S.A. ¿En qué podemos asistirle hoy?',
        ]);

        WhatsappMenu::where('action_id', 'menu_pedido')->update([
            'title' => 'Mis pedidos',
            'content' => 'Consulte el estado de sus órdenes y despachos con Corlan Química.',
            'button_text' => 'Ver pedidos',
        ]);

        WhatsappMenu::where('action_id', 'info_menu')->update([
            'content' => "*Información comercial*\nCorlan Química S.A.\n\nSeleccione el tema que desea consultar:",
        ]);
    }

    private function configureChatbot(): void
    {
        $config = WhatsappChatbotConfig::first();
        if (!$config) {
            return;
        }

        $config->update([
            'welcome_message' => 'Estimado cliente, le damos la bienvenida a *Corlan Química S.A.* Somos su aliado en insumos de limpieza, higiene industrial y soluciones químicas en Guayaquil y Ecuador. ¿En qué podemos ayudarle?',
            'default_response' => 'Disculpe, no hemos comprendido su mensaje. Escriba *hola* para acceder al menú o indique el código SKU del producto que desea consultar.',
        ]);

        $metadata = $config->metadata ?? [];
        $metadata['bot_name'] = 'Asistente Corlan Química';
        $metadata['primary_color'] = '#059669';
        $metadata['secondary_color'] = '#047857';
        $config->update(['metadata' => $metadata]);
    }

    private function configureResponses(): void
    {
        $updates = [
            'horarios' => "*Horarios de atención*\n*Corlan Química S.A.*\n\n📅 *Lunes a Viernes:* 8:00 am – 5:00 pm\n🚫 *Sábados, domingos y feriados:* cerrado\n\n📍 *Oficinas:* Ciudadela Guayacanes, Edificio 64, Oficina 24 — Guayaquil\n📦 *Bodegas:* Av. León Febres Cordero Km 4.5 — Guayaquil",
            'contacto' => "*Información de contacto*\n*Corlan Química S.A.*\n\n📱 WhatsApp: +593 98 105 4219\n☎️ Oficina: 04-507-2574\n📧 ventas@corlanquimica.com\n📧 comercial@corlanquimica.com\n🌐 https://corlanquimica.com\n\n*Oficinas:* Ciudadela Guayacanes, Ed. 64, Of. 24\n*Bodegas:* Av. León Febres Cordero Km 4.5\nGuayaquil — Ecuador",
            'envios' => "*Entregas y despachos*\n\nEn *Corlan Química* coordinamos entregas en Guayaquil y envíos a nivel nacional según volumen y tipo de producto.\n\n• Pedidos corporativos: programación con ejecutivo comercial\n• Retiro en bodega o entrega a domicilio\n• Consultas: ventas@corlanquimica.com",
            'pagos' => "*Formas de pago*\n\nAceptamos:\n✅ Transferencia bancaria\n✅ Depósito\n✅ Efectivo en oficinas\n✅ Crédito comercial (clientes registrados)\n\nPara confirmar condiciones y facturación, contacte a *ventas@corlanquimica.com*.",
            'redes' => "*Presencia digital*\n*Corlan Química S.A.*\n\n• Facebook: Corlanquimica S.A.\n• Instagram: @corlanquimica_s.a\n• LinkedIn: Grupo Corlan\n• Web: https://corlanquimica.com",
            'hola' => "Estimado cliente, bienvenido a *Corlan Química S.A.*\n\nProveemos soluciones en:\n• Químicos de limpieza y desinfección\n• Sistemas dispensados e insumos absorbentes\n• Cafetería y suministros de oficina\n• Sector bananero y agroquímicos\n• Equipos de protección personal (EPP)\n\nUse el menú o escriba el SKU del producto.",
            'asesoria' => 'A continuación compartimos el contacto de nuestro equipo comercial para atención personalizada.',
            'soporte' => 'Derivamos su consulta a nuestro equipo de atención comercial. En breve un ejecutivo se comunicará con usted.',
            'ventas' => 'Compartimos los datos de contacto de nuestro departamento de ventas corporativas.',
        ];

        foreach ($updates as $keyword => $response) {
            WhatsappChatbotResponse::where('keyword', $keyword)->update([
                'response' => $response,
                'is_active' => true,
            ]);
        }

        $asesoria = WhatsappChatbotResponse::where('keyword', 'asesoria')->first();
        if ($asesoria) {
            $asesoria->contacts = 'Ventas Corlan|Ventas|Corlan|593981054219|ventas@corlanquimica.com|Corlan Química S.A.|Ventas|Equipo Comercial';
            $asesoria->save();
        }
    }

    private function configureMarketingFlow(): void
    {
        $profile = WhatsappBusinessProfile::first();
        if (!$profile) {
            return;
        }

        $flow = MarketingFlow::updateOrCreate(
            ['business_profile_id' => $profile->id, 'is_default' => true],
            ['name' => 'Flujo comercial Corlan Química', 'is_active' => true]
        );

        $steps = require __DIR__ . '/data/corlanquimica_flow.php';
        $order = 0;

        foreach (MarketingStepKey::defaultOrder() as $stepKey) {
            $order++;
            $data = $steps[$stepKey] ?? ['message' => '', 'type' => 'text'];

            $config = ['interactive_type' => $data['type']];

            if (!empty($data['buttons'])) {
                $config['buttons'] = $data['buttons'];
            }
            if (!empty($data['list'])) {
                $config['list'] = $data['list'];
            }
            if (!empty($data['catalog_source'])) {
                $config['catalog_source'] = $data['catalog_source'];
                $config['max_product_rows'] = $data['max_product_rows'] ?? 8;
                $config['include_navigation'] = $data['include_navigation'] ?? true;
            }
            if (!empty($data['success_message'])) {
                $config['success_message'] = $data['success_message'];
            }
            if (!empty($data['require_proof'])) {
                $config['require_proof'] = true;
                $config['require_for_methods'] = $data['require_for_methods'] ?? ['transferencia', 'tarjeta'];
            }

            MarketingFlowStep::updateOrCreate(
                ['flow_id' => $flow->id, 'step_key' => $stepKey],
                [
                    'name' => MarketingStepKey::all()[$stepKey],
                    'message_template' => $data['message'],
                    'sort_order' => $order,
                    'is_enabled' => true,
                    'config' => $config,
                ]
            );
        }
    }

    private function configurePdf(): void
    {
        app(OrderPdfSettingsService::class)->save([
            'legal_name' => 'Corlan Química S.A.',
            'trade_name' => 'Corlan Química',
            'ruc' => '',
            'address' => 'Ciudadela Guayacanes, Edificio 64, Oficina 24',
            'city' => 'Guayaquil, Ecuador',
            'phone' => '04-507-2574 / 0981054219',
            'email' => 'ventas@corlanquimica.com',
            'website' => 'corlanquimica.com',
            'document_title' => 'ORDEN DE PEDIDO',
            'document_subtitle' => 'Corlan Química — Insumos industriales y limpieza',
            'legal_footer' => 'Precios referenciales demo. Confirmación sujeta a stock y condiciones comerciales Corlan Química S.A.',
            'iva_rate_percent' => 15,
            'prices_include_iva' => false,
            'timezone' => 'America/Guayaquil',
        ]);
    }

    private function activateDemo(): void
    {
        app(PlanLimitsService::class)->savePlatformLimits([
            'active_demo_cliente' => self::DEMO_KEY,
            'subscription_plan' => 'pro',
            'max_products_limit' => 500,
            'max_categories_limit' => 60,
            'bulk_web_order_enabled' => true,
        ]);

        app(DemoClienteService::class)->saveActiveKey(self::DEMO_KEY);
    }
}
