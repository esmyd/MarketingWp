<?php

namespace App\Http\Controllers;

use App\Models\WhatsappContact;
use App\Models\WhatsappTemplate;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HerbalifeMarketingController extends Controller
{
    protected $whatsappService;
    protected $baseUrl;
    protected $apiVersion;
    protected $apiToken;
    protected $businessPhone;

    public function __construct(WhatsappService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
        $this->baseUrl = config('whatsapp.api_url', 'https://graph.facebook.com');
        $this->apiVersion = config('whatsapp.api_version', 'v22.0');
        $this->apiToken = config('whatsapp.token');
        $this->businessPhone = config('whatsapp.phone_number');
    }

    public function verifyAndSubmitTemplate()
    {
        try {
            $template = WhatsappTemplate::where('name', 'herbalife_promo')->firstOrFail();

            // Verificar si la plantilla ya está aprobada
            if ($template->status === 'approved') {
                return response()->json([
                    'success' => true,
                    'message' => 'La plantilla ya está aprobada',
                    'template_id' => $template->template_id
                ]);
            }

            // Preparar el payload para la API de WhatsApp
            $payload = [
                'name' => $template->name,
                'category' => $template->category,
                'language' => $template->language,
                'components' => [
                    [
                        'type' => 'BODY',
                        'text' => $template->content,
                        'example' => [
                            'body_text' => [
                                [
                                    $template->variables[0] => 'Juan',
                                    $template->variables[1] => 'Fórmula 1 - Batido Nutricional Herbalife',
                                    $template->variables[2] => "• Control de peso efectivo\n• Nutrición balanceada",
                                    $template->variables[3] => "¡Lleva 2 Fórmula 1 y te regalamos el shaker! 🎁\nPrecio especial: $99.99",
                                    $template->variables[4] => "¡Comienza tu transformación hoy! 💪"
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'FOOTER',
                        'text' => 'Herbalife - Nutrición y Bienestar'
                    ]
                ]
            ];

            // Enviar la plantilla a WhatsApp para aprobación
            $response = Http::withToken($this->apiToken)
                ->post("{$this->baseUrl}/{$this->apiVersion}/{$this->businessPhone}/message_templates", $payload);

            if ($response->successful()) {
                $data = $response->json();

                // Actualizar el estado de la plantilla
                $template->update([
                    'status' => 'pending',
                    'template_id' => $data['id'] ?? null
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Plantilla enviada para aprobación',
                    'template_id' => $data['id'] ?? null
                ]);
            }

            Log::error('Error al enviar plantilla a WhatsApp', [
                'response' => $response->json(),
                'template' => $template->name
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar la plantilla para aprobación',
                'error' => $response->json()
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error en verificación de plantilla', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sendMarketingMessage(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|exists:whatsapp_contacts,id',
            'product_type' => 'required|in:shake,tea,protein,multivitamin'
        ]);

        $contact = WhatsappContact::findOrFail($request->contact_id);
        $template = WhatsappTemplate::where('name', 'herbalife_promo')->firstOrFail();

        // Definir el contenido según el tipo de producto
        $productInfo = $this->getProductInfo($request->product_type);

        $variables = [
            $contact->name ?? 'Cliente',
            $productInfo['description'],
            $productInfo['benefits'],
            $productInfo['promo'],
            $productInfo['call_to_action']
        ];

        $result = $this->whatsappService->sendTemplateMessage($contact, $template, $variables);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Mensaje de marketing enviado correctamente'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Error al enviar el mensaje de marketing'
        ], 500);
    }

    protected function getProductInfo($productType)
    {
        $products = [
            'shake' => [
                'description' => "Fórmula 1 - Batido Nutricional Herbalife\nEl batido nutricional más vendido del mundo, perfecto para control de peso y nutrición balanceada.",
                'benefits' => "• Control de peso efectivo\n• Nutrición balanceada\n• Rico en proteínas\n• Fácil de preparar\n• Delicioso sabor",
                'promo' => "¡Lleva 2 Fórmula 1 y te regalamos el shaker! 🎁\nPrecio especial: $XX.XX",
                'call_to_action' => "¡Comienza tu transformación hoy! 💪"
            ],
            'tea' => [
                'description' => "Té Concentrado Herbalife\nBebida termogénica que ayuda a mantener tu energía y metabolismo activo.",
                'benefits' => "• Energía natural\n• Metabolismo activo\n• Antioxidantes\n• Sin cafeína\n• Sabor refrescante",
                'promo' => "¡Lleva 2 tés y te regalamos el termo! 🎁\nPrecio especial: $XX.XX",
                'call_to_action' => "¡Energiza tu día! ⚡"
            ],
            'protein' => [
                'description' => "Proteína Personalizada Herbalife\nProteína de alta calidad para mantener tu masa muscular y recuperación.",
                'benefits' => "• 24g de proteína por porción\n• Bajo en calorías\n• Sin azúcar añadida\n• Fácil digestión\n• Variedad de sabores",
                'promo' => "¡Lleva 2 proteínas y te regalamos el shaker! 🎁\nPrecio especial: $XX.XX",
                'call_to_action' => "¡Fortalece tu cuerpo! 💪"
            ],
            'multivitamin' => [
                'description' => "Multivitamínico Herbalife\nComplejo de vitaminas y minerales esenciales para tu bienestar diario.",
                'benefits' => "• 24 vitaminas y minerales\n• Antioxidantes\n• Sistema inmune\n• Energía diaria\n• Bienestar general",
                'promo' => "¡Lleva 2 multivitamínicos y te regalamos el organizador! 🎁\nPrecio especial: $XX.XX",
                'call_to_action' => "¡Cuida tu salud! 🌟"
            ]
        ];

        return $products[$productType] ?? $products['shake'];
    }
}
