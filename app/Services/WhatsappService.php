<?php

namespace App\Services;

use App\Models\WhatsappBusinessProfile;
use App\Models\WhatsappContact;
use App\Models\WhatsappMessage;
use App\Models\WhatsappTemplate;
use App\Models\WhatsappChatbotResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\WhatsappConversation;
use App\Models\WhatsappChatbotConfig;
use App\Models\WhatsappMenu;
use App\Models\WhatsappMenuItem;
use App\Models\WhatsappPrice;
use App\Models\WhatsappCart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\WhatsappButton;
use App\Mail\MonitoringNotification;
use Illuminate\Support\Facades\Mail;

class WhatsappService
{
    protected $baseUrl;
    protected $apiVersion;
    protected $apiToken;
    protected $businessPhone;
    protected $businessProfile;
    protected $lastMessage;

    public function __construct()
    {
        $this->baseUrl = config('whatsapp.api_url', 'https://graph.facebook.com');
        $this->apiVersion = config('whatsapp.api_version', 'v22.0');
        $this->apiToken = config('whatsapp.token');
        $this->businessPhone = config('whatsapp.phone_number');
        $this->businessProfile = WhatsappBusinessProfile::first();
        $this->lastMessage = null;

        if (empty($this->businessPhone)) {
            Log::error('WhatsApp phone number is not configured');
        }

        if (!$this->businessProfile) {
            Log::warning('No business profile found in database');
        }
    }

    public function sendTemplateMessage(WhatsappContact $contact, WhatsappTemplate $template, array $variables = [])
    {
        try {
            $response = Http::withToken($this->apiToken)
                ->post("{$this->baseUrl}/{$this->apiVersion}/{$this->businessProfile->phone_number_id}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $contact->phone_number,
                    'type' => 'template',
                    'template' => [
                        'name' => $template->name,
                        'language' => [
                            'code' => $template->language
                        ],
                        'components' => $this->prepareTemplateComponents($template, $variables)
                    ]
                ]);

            if ($response->successful()) {
                $messageId = $response->json()['messages'][0]['id'];

                // Save the message in our database
                WhatsappMessage::create([
                    'business_profile_id' => $this->businessProfile ? $this->businessProfile->id : null,
                    'contact_id' => $contact->id,
                    'message_id' => $messageId,
                    'content' => $template->content,
                    'type' => 'template',
                    'status' => 'sent',
                    'metadata' => [
                        'template_name' => $template->name,
                        'variables' => $variables
                    ]
                ]);

                return true;
            }

            $errorData = $response->json();
            $errorMessage = $errorData['error']['message'] ?? 'Error desconocido al enviar plantilla';
            $errorCode = $errorData['error']['code'] ?? null;
            $errorType = $errorData['error']['type'] ?? null;
            $fullError = "Error {$errorCode}: {$errorMessage}";

            Log::error('WhatsApp API Error', [
                'response' => $errorData,
                'contact' => $contact->phone_number,
                'template' => $template->name
            ]);

            // Retornar array con detalles para campañas, false para compatibilidad
            return [
                'success' => false,
                'error' => $fullError,
                'error_code' => $errorCode,
                'error_type' => $errorType,
                'boolean' => false
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp Service Error', [
                'error' => $e->getMessage(),
                'contact' => $contact->phone_number,
                'template' => $template->name
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'boolean' => false
            ];
        }
    }

    public function sendTextMessage(WhatsappContact $contact, string $message, bool $humanSent = false)
    {
        try {
            if (!$this->businessProfile || !$this->businessProfile->phone_number_id) {
                Log::error('No business profile or phone_number_id found');
                return false;
            }

            $url = "{$this->baseUrl}/{$this->apiVersion}/{$this->businessProfile->phone_number_id}/messages";

            Log::info('Enviando mensaje de texto a WhatsApp API', [
                'url' => $url,
                'to' => $contact->phone_number,
                'message_length' => strlen($message),
                'human_sent' => $humanSent
            ]);

            $response = Http::withToken($this->apiToken)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'to' => $contact->phone_number,
                    'type' => 'text',
                    'text' => [
                        'body' => $message
                    ]
                ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $messageId = $responseData['messages'][0]['id'] ?? null;

                if ($messageId) {
                    $metadata = [];
                    if ($humanSent) {
                        $metadata['human_sent'] = true;
                        $metadata['human_sent_at'] = now()->toIso8601String();
                    }

                    WhatsappMessage::create([
                        'business_profile_id' => $this->businessProfile ? $this->businessProfile->id : null,
                        'contact_id' => $contact->id,
                        'message_id' => $messageId,
                        'content' => $message,
                        'type' => 'text',
                        'status' => 'sent',
                        'sender_type' => $humanSent ? 'humano' : 'system',
                        'receiver_type' => 'client',
                        'metadata' => !empty($metadata) ? $metadata : null
                    ]);

                    Log::info('Mensaje de texto guardado en BD', [
                        'message_id' => $messageId,
                        'contact_id' => $contact->id
                    ]);
                }

                return true;
            }

            $errorData = $response->json();
            $errorMessage = $errorData['error']['message'] ?? 'Error desconocido al enviar mensaje de texto';
            $errorCode = $errorData['error']['code'] ?? null;
            $errorType = $errorData['error']['type'] ?? null;
            $fullError = "Error {$errorCode}: {$errorMessage}";

            Log::error('WhatsApp API Error al enviar texto', [
                'status' => $response->status(),
                'response' => $errorData,
                'contact' => $contact->phone_number
            ]);

            // Retornar array con detalles para campañas, false para compatibilidad
            return [
                'success' => false,
                'error' => $fullError,
                'error_code' => $errorCode,
                'error_type' => $errorType,
                'boolean' => false
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp Service Error al enviar texto', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'contact' => $contact->phone_number
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'boolean' => false
            ];
        }
    }

    public function sendImageMessage(WhatsappContact $contact, $imagePath, ?string $caption = null, bool $humanSent = false)
    {
        try {
            if (!$this->businessProfile) {
                Log::error('No business profile found');
                return false;
            }

            // Primero subir la imagen a WhatsApp Media API
            $uploadResponse = Http::withToken($this->apiToken)
                ->attach('file', file_get_contents($imagePath), basename($imagePath))
                ->post("{$this->baseUrl}/{$this->apiVersion}/{$this->businessProfile->phone_number_id}/media", [
                    'messaging_product' => 'whatsapp',
                    'type' => mime_content_type($imagePath)
                ]);

            if (!$uploadResponse->successful()) {
                Log::error('WhatsApp Media Upload Error', [
                    'response' => $uploadResponse->json(),
                    'contact' => $contact->phone_number
                ]);
                return false;
            }

            $mediaId = $uploadResponse->json()['id'];

            // Ahora enviar el mensaje con la imagen
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $contact->phone_number,
                'type' => 'image',
                'image' => [
                    'id' => $mediaId
                ]
            ];

            if ($caption) {
                $payload['image']['caption'] = $caption;
            }

            $response = Http::withToken($this->apiToken)
                ->post("{$this->baseUrl}/{$this->apiVersion}/{$this->businessProfile->phone_number_id}/messages", $payload);

            if ($response->successful()) {
                $messageId = $response->json()['messages'][0]['id'];

                $metadata = [
                    'media_id' => $mediaId,
                    'has_caption' => !empty($caption)
                ];
                if ($humanSent) {
                    $metadata['human_sent'] = true;
                    $metadata['human_sent_at'] = now()->toIso8601String();
                }

                WhatsappMessage::create([
                    'business_profile_id' => $this->businessProfile ? $this->businessProfile->id : null,
                    'contact_id' => $contact->id,
                    'message_id' => $messageId,
                    'content' => $caption ?? '',
                    'type' => 'image',
                    'status' => 'sent',
                    'sender_type' => $humanSent ? 'humano' : 'system',
                    'receiver_type' => 'client',
                    'metadata' => $metadata
                ]);

                return true;
            }

            Log::error('WhatsApp API Error', [
                'response' => $response->json(),
                'contact' => $contact->phone_number
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('WhatsApp Service Error', [
                'error' => $e->getMessage(),
                'contact' => $contact->phone_number
            ]);

            return false;
        }
    }

    public function handleWebhook(array $payload)
    {
        try {
            Log::info('[handleWebhook] 📱 Webhook recibido', [
                'tipo' => $payload['object'] ?? 'desconocido',
                'entry_id' => $payload['entry'][0]['id'] ?? 'sin_id'
            ]);

            $entry = $payload['entry'][0] ?? null;
            if (!$entry) {
                Log::error('❌ Webhook inválido: No se encontró entry');
                return;
            }

            $changes = $entry['changes'][0] ?? null;
            if (!$changes) {
                Log::error('❌ Webhook inválido: No se encontraron cambios');
                return;
            }

            $value = $changes['value'] ?? null;
            if (!$value) {
                Log::error('❌ Webhook inválido: No se encontró value');
                return;
            }

            // Procesar mensajes entrantes
            if (isset($value['messages']) && is_array($value['messages'])) {
                foreach ($value['messages'] as $message) {
                    try {
                        // Validar estructura del mensaje
                        if (!isset($message['from']) || !isset($message['id'])) {
                            Log::warning('⚠️ Estructura de mensaje inválida', [
                                'tiene_from' => isset($message['from']),
                                'tiene_id' => isset($message['id']),
                                'mensaje' => $message
                            ]);
                            continue;
                        }

                        // Extraer datos del mensaje
                        $messageData = [
                            'from' => $message['from'],
                            'id' => $message['id'],
                            'type' => $message['type'] ?? 'text',
                            'timestamp' => $message['timestamp'] ?? null,
                            'text' => $message['text']['body'] ?? null,
                            'contacts' => $value['contacts'] ?? []
                        ];

                        // Si es una imagen, procesar los datos de la imagen
                        if ($message['type'] === 'image' && isset($message['image'])) {
                            $messageData['image'] = [
                                'id' => $message['image']['id'] ?? null,
                                'mime_type' => $message['image']['mime_type'] ?? null,
                                'sha256' => $message['image']['sha256'] ?? null,
                                'caption' => $message['image']['caption'] ?? null
                            ];

                            // Verificar que tenemos los datos mínimos necesarios de la imagen
                            if (empty($messageData['image']['id']) || empty($messageData['image']['mime_type'])) {
                                Log::warning('⚠️ Datos de imagen incompletos', [
                                    'tiene_id' => !empty($messageData['image']['id']),
                                    'tiene_mime_type' => !empty($messageData['image']['mime_type']),
                                    'mensaje' => $message
                                ]);
                                continue;
                            }

                            Log::info('📸 Datos de imagen recibidos', [
                                'id' => $messageData['image']['id'],
                                'mime_type' => $messageData['image']['mime_type'],
                                'sha256' => $messageData['image']['sha256']
                            ]);
                        }

                        $this->processIncomingMessage($messageData);
                    } catch (\Exception $e) {
                        Log::error('❌ Error procesando mensaje individual', [
                            'error' => $e->getMessage(),
                            'linea' => $e->getLine(),
                            'mensaje' => $message
                        ]);
                    }
                }
            }

            // Procesar actualizaciones de estado
            if (isset($value['statuses']) && is_array($value['statuses'])) {
                foreach ($value['statuses'] as $status) {
                    if (isset($status['id']) && isset($status['status'])) {
                        $this->updateMessageStatus($status);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('❌ Error en webhook', [
                'error' => $e->getMessage(),
                'linea' => $e->getLine()
            ]);
        }
    }

    public function processIncomingMessage(array $message) //principal
    {
        Log::info('[inicio] processIncomingMessage');
        try {
            // Validar datos requeridos
            if (empty($message['from']) || empty($message['id'])) {
                Log::warning('⚠️ Datos de mensaje incompletos', [
                    'tiene_from' => !empty($message['from']),
                    'tiene_id' => !empty($message['id'])
                ]);
                return;
            }

            // Verificar si el mensaje ya fue procesado
            $existingMessage = WhatsappMessage::where('message_id', $message['id'])->first();
            if ($existingMessage) {
                //Log::info('[processIncomingMessage] ⏭️ Mensaje ya procesado anteriormente', [
                //    'message_id' => $message['id'],
                //    'tipo' => $message['type']
                //]);
                return;
            }

            //Log::info('[processIncomingMessage] 📥 Mensaje recibido', [
            //    'de' => substr($message['from'], 0, 4) . '****' . substr($message['from'], -4),
            //    'tipo' => $message['type'],
            //    'id' => $message['id'],
            //    'contenido' => $message['text'] ?? ($message['interactive'] ?? null)
            //]);

            // Procesar según el tipo de mensaje
            if ($message['type'] === 'text') {
                $this->handleTextMessage($message);
            } elseif ($message['type'] === 'interactive') {
                $this->handleInteractiveMessage($message);
            } elseif ($message['type'] === 'image') {
                $this->handleImageMessage($message);
            }elseif ($message['type'] === 'audio') {
                $this->handleAudioMessage($message);
            }elseif ($message['type'] === 'video') {
                $this->handleVideoMessage($message);
            }elseif ($message['type'] === 'document') {
                $this->handleDocumentMessage($message);
            }elseif ($message['type'] === 'location') {
                $this->handleLocationMessage($message);
            }elseif ($message['type'] === 'sticker') {
                $this->handleStickerMessage($message);
            }elseif ($message['type'] === 'button') {
                $this->handleButtonMessage($message);
            }

            // Marcar como leído
            if (!empty($message['id']) && !empty($message['from'])) {
                $this->markMessageAsRead($message['id'], $message['from']);
            }

            // Enviar notificaciones de monitoreo
            $this->sendMonitoringNotifications($message);
        } catch (\Exception $e) {
            Log::error('❌ Error procesando mensaje', [
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'mensaje' => $message
            ]);
        }
    }

    protected function handleButtonMessage($message)
    {
        try {
            Log::info('[handleButtonMessage] 🔘 Button message received', [
                'from' => substr($message['from'], 0, 4) . '****' . substr($message['from'], -4),
                'message_id' => $message['id']
            ]);

            // Marcar el mensaje como leído
            $this->markMessageAsRead($message['id'], $message['from']);

            // Obtener los menús desde la base de datos
            $productosMenu = WhatsappMenu::where('action_id', 'menu_productos')->first();
            $pedidosMenu = WhatsappMenu::where('action_id', 'menu_pedido')->first();
            $infoMenu = WhatsappMenu::where('action_id', 'menu_info')->first();

            $menuMessage = [
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'button',
                    'body' => [
                        'text' => "¿En qué más puedo ayudarte?"
                    ],
                    'action' => [
                        'buttons' => [
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'menu_productos',
                                    'title' => $productosMenu ? $productosMenu->button_text : '🛍️ Productos'
                                ]
                            ],
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'menu_pedido',
                                    'title' => $pedidosMenu ? $pedidosMenu->button_text : '📦 Ver Pedidos'
                                ]
                            ],
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'menu_info',
                                    'title' => $infoMenu ? $infoMenu->button_text : 'ℹ️ Información'
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            $this->sendMessage($message['from'], $menuMessage);

        } catch (\Exception $e) {
            Log::error('Error al manejar mensaje de botón', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'message' => $message
            ]);
        }
    }

    protected function handleAudioMessage($message)
    {
        try {
            $from = $message['from'];
            $messageId = $message['id'];

            Log::info('[handleAudioMessage] 🎵 Audio message received', [
                'from' => substr($from, 0, 4) . '****' . substr($from, -4),
                'message_id' => $messageId
            ]);

            // Marcar como leído
            $this->markMessageAsRead($messageId, $from);

            // Obtener o crear contacto
            $contact = WhatsappContact::firstOrCreate(
                ['phone_number' => $from],
                [
                    'business_profile_id' => $this->businessProfile->id,
                    'name' => 'Contacto sin nombre',
                    'status' => 'active'
                ]
            );

            // Guardar el mensaje
            WhatsappMessage::create([
                'contact_id' => $contact->id,
                'business_profile_id' => $this->businessProfile->id,
                'message_id' => $messageId,
                'type' => 'audio',
                'status' => 'received',
                'sender_type' => 'client',
                'receiver_type' => 'system',
                'metadata' => [
                    'timestamp' => $message['timestamp'] ?? null
                ]
            ]);

            // Obtener los menús desde la base de datos
            $productosMenu = WhatsappMenu::where('action_id', 'menu_productos')->first();
            $infoMenu = WhatsappMenu::where('action_id', 'menu_info')->first();

            // Enviar respuesta
            $response = [
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'button',
                    'body' => [
                        'text' => "🎵 *Mensaje de audio recibido*\n\n" .
                            "Gracias por tu mensaje de audio. ¿En qué más puedo ayudarte?"
                    ],
                    'action' => [
                        'buttons' => [
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'menu_productos',
                                    'title' => $productosMenu ? $productosMenu->button_text : '🛍️ Productos'
                                ]
                            ],
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'menu_info',
                                    'title' => $infoMenu ? $infoMenu->button_text : 'ℹ️ Información'
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            $this->sendMessage($from, $response);

        } catch (\Exception $e) {
            Log::error('❌ Error processing audio message', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);
        }
    }

    protected function handleVideoMessage($message)
    {
        try {
            $from = $message['from'];
            $messageId = $message['id'];

            Log::info('[handleVideoMessage] 🎥 Video message received', [
                'from' => substr($from, 0, 4) . '****' . substr($from, -4),
                'message_id' => $messageId
            ]);

            // Marcar como leído
            $this->markMessageAsRead($messageId, $from);

            // Obtener o crear contacto
            $contact = WhatsappContact::firstOrCreate(
                ['phone_number' => $from],
                [
                    'business_profile_id' => $this->businessProfile->id,
                    'name' => 'Contacto sin nombre',
                    'status' => 'active'
                ]
            );

            // Guardar el mensaje
            WhatsappMessage::create([
                'contact_id' => $contact->id,
                'business_profile_id' => $this->businessProfile->id,
                'message_id' => $messageId,
                'type' => 'video',
                'status' => 'received',
                'sender_type' => 'client',
                'receiver_type' => 'system',
                'metadata' => [
                    'timestamp' => $message['timestamp'] ?? null
                ]
            ]);

            // Obtener los menús desde la base de datos
            $productosMenu = WhatsappMenu::where('action_id', 'menu_productos')->first();
            $infoMenu = WhatsappMenu::where('action_id', 'menu_info')->first();

            // Enviar respuesta
            $response = [
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'button',
                    'body' => [
                        'text' => "🎥 *Video recibido*\n\n" .
                            "Gracias por compartir el video. ¿En qué más puedo ayudarte?"
                    ],
                    'action' => [
                        'buttons' => [
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'menu_productos',
                                    'title' => $productosMenu ? $productosMenu->button_text : '🛍️ Productos'
                                ]
                            ],
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'menu_info',
                                    'title' => $infoMenu ? $infoMenu->button_text : 'ℹ️ Información'
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            $this->sendMessage($from, $response);

        } catch (\Exception $e) {
            Log::error('❌ Error processing video message', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);
        }
    }

    protected function handleDocumentMessage($message)
    {
        try {
            $from = $message['from'];
            $messageId = $message['id'];

            Log::info('[handleDocumentMessage] 📄 Document message received', [
                'from' => substr($from, 0, 4) . '****' . substr($from, -4),
                'message_id' => $messageId
            ]);

            // Marcar como leído
            $this->markMessageAsRead($messageId, $from);

            // Obtener o crear contacto
            $contact = WhatsappContact::firstOrCreate(
                ['phone_number' => $from],
                [
                    'business_profile_id' => $this->businessProfile->id,
                    'name' => 'Contacto sin nombre',
                    'status' => 'active'
                ]
            );

            // Guardar el mensaje
            WhatsappMessage::create([
                'contact_id' => $contact->id,
                'business_profile_id' => $this->businessProfile->id,
                'message_id' => $messageId,
                'type' => 'document',
                'status' => 'received',
                'sender_type' => 'client',
                'receiver_type' => 'system',
                'content' => json_encode($message['text'] ?? $message['document'] ?? 'Documento recibido'),
                'metadata' => [
                    'filename' => $message['document']['filename'] ?? null,
                    'mime_type' => $message['document']['mime_type'] ?? null,
                    'timestamp' => $message['timestamp'] ?? null
                ]
            ]);

            // Obtener los menús desde la base de datos
            $productosMenu = WhatsappMenu::where('action_id', 'menu_productos')->first();
            $infoMenu = WhatsappMenu::where('action_id', 'menu_info')->first();

            // Enviar respuesta interactiva
            $response = [
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'button',
                    'body' => [
                        'text' => "📄 *Documento recibido*\n\n" .
                            "Gracias por compartir el documento. ¿En qué más puedo ayudarte?"
                    ],
                    'action' => [
                        'buttons' => [
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'menu_productos',
                                    'title' => $productosMenu ? $productosMenu->button_text : '🛍️ Productos'
                                ]
                            ],
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'menu_info',
                                    'title' => $infoMenu ? $infoMenu->button_text : 'ℹ️ Información'
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            $this->sendMessage($from, $response);

        } catch (\Exception $e) {
            Log::error('❌ Error processing document message', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);
        }
    }

    protected function handleLocationMessage($message)
    {
        try {
            $from = $message['from'];
            $messageId = $message['id'];

            Log::info('[handleLocationMessage] 📍 Location message received', [
                'from' => substr($from, 0, 4) . '****' . substr($from, -4),
                'message_id' => $messageId
            ]);

            // Marcar como leído
            $this->markMessageAsRead($messageId, $from);

            // Obtener o crear contacto
            $contact = WhatsappContact::firstOrCreate(
                ['phone_number' => $from],
                [
                    'business_profile_id' => $this->businessProfile->id,
                    'name' => 'Contacto sin nombre',
                    'status' => 'active'
                ]
            );

            // Guardar el mensaje
            WhatsappMessage::create([
                'contact_id' => $contact->id,
                'business_profile_id' => $this->businessProfile->id,
                'message_id' => $messageId,
                'type' => 'location',
                'status' => 'received',
                'sender_type' => 'client',
                'receiver_type' => 'system',
                'content' => json_encode($message['location'] ?? 'Ubicación recibida'),
                'metadata' => [
                    'latitude' => $message['location']['latitude'] ?? null,
                    'longitude' => $message['location']['longitude'] ?? null,
                    'timestamp' => $message['timestamp'] ?? null
                ]
            ]);

            // Obtener los menús desde la base de datos
            $productosMenu = WhatsappMenu::where('action_id', 'menu_productos')->first();
            $infoMenu = WhatsappMenu::where('action_id', 'menu_info')->first();

            // Enviar respuesta
            $response = [
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'button',
                    'body' => [
                        'text' => "📍 *Ubicación recibida*\n\n" .
                            "Gracias por compartir tu ubicación. ¿En qué más puedo ayudarte?"
                    ],
                    'action' => [
                        'buttons' => [
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'menu_productos',
                                    'title' => $productosMenu ? $productosMenu->button_text : '🛍️ Productos'
                                ]
                            ],
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'menu_info',
                                    'title' => $infoMenu ? $infoMenu->button_text : 'ℹ️ Información'
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            $this->sendMessage($from, $response);

        } catch (\Exception $e) {
            Log::error('❌ Error processing location message', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);
        }
    }

    protected function handleStickerMessage($message)
    {
        try {
            $from = $message['from'];
            $messageId = $message['id'];

            Log::info('[handleStickerMessage] 🎯 Sticker message received', [
                'from' => substr($from, 0, 4) . '****' . substr($from, -4),
                'message_id' => $messageId
            ]);

            // Marcar como leído
            $this->markMessageAsRead($messageId, $from);

            // Obtener o crear contacto
            $contact = WhatsappContact::firstOrCreate(
                ['phone_number' => $from],
                [
                    'business_profile_id' => $this->businessProfile->id,
                    'name' => 'Contacto sin nombre',
                    'status' => 'active'
                ]
            );

            // Guardar el mensaje
            WhatsappMessage::create([
                'contact_id' => $contact->id,
                'business_profile_id' => $this->businessProfile->id,
                'message_id' => $messageId,
                'type' => 'sticker',
                'status' => 'received',
                'sender_type' => 'client',
                'receiver_type' => 'system',
                'content' => json_encode($message['text'] ?? $message['sticker'] ?? 'Sticker recibido'),
                'metadata' => [
                    'timestamp' => $message['timestamp'] ?? null
                ]
            ]);

            // Obtener los menús desde la base de datos
            $productosMenu = WhatsappMenu::where('action_id', 'menu_productos')->first();
            $infoMenu = WhatsappMenu::where('action_id', 'menu_info')->first();

            // Enviar respuesta
            $response = [
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'button',
                    'body' => [
                        'text' => "🎯 *Sticker recibido*\n\n" .
                            "Gracias por compartir el sticker. ¿En qué más puedo ayudarte?"
                    ],
                    'action' => [
                        'buttons' => [
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'menu_productos',
                                    'title' => $productosMenu ? $productosMenu->button_text : '🛍️ Productos'
                                ]
                            ],
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'menu_info',
                                    'title' => $infoMenu ? $infoMenu->button_text : 'ℹ️ Información'
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            $this->sendMessage($from, $response);

        } catch (\Exception $e) {
            Log::error('❌ Error processing sticker message', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);
        }
    }

    protected function updateMessageStatus($status)
    {
        try {
            $messageId = $status['id'] ?? null;
            $newStatus = $status['status'] ?? null;
            $timestamp = $status['timestamp'] ?? null;

            if (!$messageId || !$newStatus) {
                Log::error('❌ Estado inválido', ['status' => $status]);
                return;
            }

            $message = WhatsappMessage::where('message_id', $messageId)->first();
            if (!$message) {
                Log::warning('⚠️ Mensaje no encontrado', ['id' => $messageId]);
                return;
            }

            // Solo actualizar si el nuevo estado es más reciente
            if ($timestamp && $message->updated_at && strtotime($timestamp) <= strtotime($message->updated_at)) {
                Log::info('[updateMessageStatus] ⏭️ Estado obsoleto ignorado', [
                    'message_id' => $status['id'],
                    'estado_actual' => $message->status,
                    'nuevo_estado' => $status['status']
                ]);
                return;
            }

            $message->status = $newStatus;
            $message->save();

            Log::info('[updateMessageStatus] ✅ Estado actualizado', [
                'message_id' => $status['id'],
                'estado' => $status['status']
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error actualizando estado', [
                'error' => $e->getMessage(),
                'linea' => $e->getLine()
            ]);
        }
    }

    /**
     * Verifica si hay actividad humana reciente en un chat
     * Retorna true si hay actividad humana en las últimas X horas (por defecto 2 horas)
     */
    protected function hasRecentHumanActivity(WhatsappContact $contact, int $hoursThreshold = 2): bool
    {
        $thresholdTime = now()->subHours($hoursThreshold);

        // Buscar el último mensaje enviado por un humano (desde el panel)
        $lastHumanMessage = WhatsappMessage::where('contact_id', $contact->id)
            ->where('sender_type', 'system')
            ->whereNotNull('metadata->human_sent')
            ->where('metadata->human_sent', true)
            ->where('created_at', '>=', $thresholdTime)
            ->latest('created_at')
            ->first();

        if ($lastHumanMessage) {
            Log::info('[hasRecentHumanActivity] ✅ Actividad humana detectada', [
                'contact_id' => $contact->id,
                'last_human_message_id' => $lastHumanMessage->id,
                'last_human_message_at' => $lastHumanMessage->created_at,
                'hours_ago' => now()->diffInHours($lastHumanMessage->created_at)
            ]);
            return true;
        }

        Log::debug('[hasRecentHumanActivity] ❌ No hay actividad humana reciente', [
            'contact_id' => $contact->id,
            'threshold_hours' => $hoursThreshold
        ]);
        return false;
    }

    /**
     * Verifica si hay actividad humana reciente por número de teléfono
     */
    protected function hasRecentHumanActivityByPhone(string $phoneNumber, int $hoursThreshold = 2): bool
    {
        $contact = WhatsappContact::where('phone_number', $phoneNumber)->first();
        if (!$contact) {
            return false;
        }
        return $this->hasRecentHumanActivity($contact, $hoursThreshold);
    }

    protected function handleChatbotResponse($contact, $message)
    {
        try {
            // Refrescar el contacto desde la base de datos para obtener el valor actualizado de bot_enabled
            $contact->refresh();

            // Verificar si el bot está habilitado para este contacto
            if (!$contact->bot_enabled) {
                Log::info('[handleChatbotResponse] 🛑 Bot detenido - Bot deshabilitado manualmente', [
                    'contact_id' => $contact->id,
                    'message_id' => $message->id,
                    'message_content' => substr($message->content, 0, 100),
                    'bot_enabled' => $contact->bot_enabled
                ]);
                return null; // No enviar respuesta automática
            }

            // Si el bot está activado manualmente, NO verificar actividad humana reciente
            // El bot funcionará inmediatamente cuando esté activado

            $response = $this->generateChatbotResponse($message->content, $message->contact->phone_number);

            // Enviar la respuesta
            $result = $this->sendMessageToWhatsApp($contact->phone_number, $response);

            if ($result) {
                // Extraer el contenido del mensaje según el tipo
                $content = '';
                if ($response['type'] === 'text') {
                    $content = $response['text']['body'];
                } elseif ($response['type'] === 'interactive') {
                    $content = $response['interactive']['body']['text'];
                }

                // Crear el mensaje de respuesta
                $responseMessage = WhatsappMessage::create([
                    'contact_id' => $contact->id,
                    'business_profile_id' => $this->businessProfile->id,
                    'message_id' => $result['message_id'],
                    'content' => $content,
                    'type' => $response['type'],
                    'status' => 'sent',
                    'metadata' => [
                        'is_bot_response' => true,
                        'interactive_data' => $response['type'] === 'interactive' ? $response['interactive'] : null
                    ]
                ]);

                Log::info('[handleChatbotResponse] Chatbot response handled successfully', [
                    'contact_id' => $contact->id,
                    'message' => $message
                ]);

                return $responseMessage;
            }

            Log::error('Failed to send chatbot response', [
                'contact_id' => $contact->id,
                'response' => $response
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error handling chatbot response', [
                'error' => $e->getMessage(),
                'contact_id' => $contact->id,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    public function sendAudioMessage(WhatsappContact $contact, $audioPath, ?string $caption = null, bool $humanSent = false)
    {
        try {
            if (!$this->businessProfile) {
                Log::error('No business profile found');
                return false;
            }

            // Detectar el tipo MIME del archivo
            $mimeType = mime_content_type($audioPath);
            $extension = strtolower(pathinfo($audioPath, PATHINFO_EXTENSION));

            // WhatsApp no acepta video/webm para audio. Si se detecta como video/webm,
            // forzar el tipo según la extensión del archivo
            if ($mimeType === 'video/webm' || $mimeType === 'application/octet-stream') {
                // Determinar el tipo según la extensión
                switch ($extension) {
                    case 'ogg':
                        $mimeType = 'audio/ogg';
                        break;
                    case 'webm':
                        // Intentar como audio/webm, pero WhatsApp puede no aceptarlo
                        // Mejor convertir a OGG o rechazar
                        Log::warning('WhatsApp Audio: WebM detectado, puede no ser compatible', [
                            'path' => $audioPath,
                            'extension' => $extension
                        ]);
                        $mimeType = 'audio/webm'; // Intentar de todas formas
                        break;
                    case 'mp3':
                        $mimeType = 'audio/mpeg';
                        break;
                    case 'wav':
                        $mimeType = 'audio/wav';
                        break;
                    case 'm4a':
                        $mimeType = 'audio/mp4';
                        break;
                    case 'aac':
                        $mimeType = 'audio/aac';
                        break;
                    default:
                        Log::error('WhatsApp Audio Error: No se pudo determinar el tipo MIME', [
                            'path' => $audioPath,
                            'detected_mime' => mime_content_type($audioPath),
                            'extension' => $extension
                        ]);
                        return false;
                }
            }

            // Validar que el tipo MIME sea compatible con WhatsApp
            // WhatsApp acepta: audio/aac, audio/mp4, audio/mpeg, audio/amr, audio/ogg, audio/opus
            // NO acepta audio/webm directamente, pero lo intentaremos
            $allowedAudioTypes = ['audio/aac', 'audio/mp4', 'audio/mpeg', 'audio/amr', 'audio/ogg', 'audio/opus', 'audio/wav'];
            if (!in_array($mimeType, $allowedAudioTypes) && $mimeType !== 'audio/webm') {
                Log::error('WhatsApp Audio Error: Tipo MIME no compatible', [
                    'mime_type' => $mimeType,
                    'allowed_types' => $allowedAudioTypes,
                    'path' => $audioPath,
                    'extension' => $extension
                ]);
                return false;
            }

            // Si es webm, advertir que puede fallar
            if ($mimeType === 'audio/webm') {
                Log::warning('WhatsApp Audio: Enviando WebM, puede no ser compatible con WhatsApp', [
                    'path' => $audioPath
                ]);
            }

            // Primero subir el audio a WhatsApp Media API
            $uploadResponse = Http::withToken($this->apiToken)
                ->attach('file', file_get_contents($audioPath), basename($audioPath))
                ->post("{$this->baseUrl}/{$this->apiVersion}/{$this->businessProfile->phone_number_id}/media", [
                    'messaging_product' => 'whatsapp',
                    'type' => $mimeType
                ]);

            if (!$uploadResponse->successful()) {
                Log::error('WhatsApp Media Upload Error (Audio)', [
                    'response' => $uploadResponse->json(),
                    'contact' => $contact->phone_number
                ]);
                return false;
            }

            $mediaId = $uploadResponse->json()['id'];

            // Ahora enviar el mensaje con el audio
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $contact->phone_number,
                'type' => 'audio',
                'audio' => [
                    'id' => $mediaId
                ]
            ];

            $response = Http::withToken($this->apiToken)
                ->post("{$this->baseUrl}/{$this->apiVersion}/{$this->businessProfile->phone_number_id}/messages", $payload);

            if ($response->successful()) {
                $messageId = $response->json()['messages'][0]['id'];

                $metadata = [
                    'media_id' => $mediaId,
                    'filename' => basename($audioPath)
                ];
                if ($humanSent) {
                    $metadata['human_sent'] = true;
                    $metadata['human_sent_at'] = now()->toIso8601String();
                }

                WhatsappMessage::create([
                    'business_profile_id' => $this->businessProfile ? $this->businessProfile->id : null,
                    'contact_id' => $contact->id,
                    'message_id' => $messageId,
                    'content' => $caption ?? 'Audio enviado',
                    'type' => 'audio',
                    'status' => 'sent',
                    'sender_type' => $humanSent ? 'humano' : 'system',
                    'receiver_type' => 'client',
                    'metadata' => $metadata
                ]);

                return true;
            }

            Log::error('WhatsApp API Error (Audio)', [
                'response' => $response->json(),
                'contact' => $contact->phone_number
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('WhatsApp Service Error (Audio)', [
                'error' => $e->getMessage(),
                'contact' => $contact->phone_number
            ]);

            return false;
        }
    }

    public function sendDocumentMessage(WhatsappContact $contact, $documentPath, ?string $filename = null, ?string $caption = null, bool $humanSent = false)
    {
        try {
            if (!$this->businessProfile) {
                Log::error('No business profile found');
                return false;
            }

            // Usar el nombre del archivo proporcionado o el nombre del archivo subido
            $documentFilename = $filename ?? basename($documentPath);

            // Primero subir el documento a WhatsApp Media API
            $uploadResponse = Http::withToken($this->apiToken)
                ->attach('file', file_get_contents($documentPath), basename($documentPath))
                ->post("{$this->baseUrl}/{$this->apiVersion}/{$this->businessProfile->phone_number_id}/media", [
                    'messaging_product' => 'whatsapp',
                    'type' => mime_content_type($documentPath)
                ]);

            if (!$uploadResponse->successful()) {
                Log::error('WhatsApp Media Upload Error (Document)', [
                    'response' => $uploadResponse->json(),
                    'contact' => $contact->phone_number
                ]);
                return false;
            }

            $mediaId = $uploadResponse->json()['id'];

            // Ahora enviar el mensaje con el documento
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $contact->phone_number,
                'type' => 'document',
                'document' => [
                    'id' => $mediaId,
                    'filename' => $documentFilename
                ]
            ];

            if ($caption) {
                $payload['document']['caption'] = $caption;
            }

            $response = Http::withToken($this->apiToken)
                ->post("{$this->baseUrl}/{$this->apiVersion}/{$this->businessProfile->phone_number_id}/messages", $payload);

            if ($response->successful()) {
                $messageId = $response->json()['messages'][0]['id'];

                $metadata = [
                    'media_id' => $mediaId,
                    'filename' => $documentFilename,
                    'mime_type' => mime_content_type($documentPath)
                ];
                if ($humanSent) {
                    $metadata['human_sent'] = true;
                    $metadata['human_sent_at'] = now()->toIso8601String();
                }

                WhatsappMessage::create([
                    'business_profile_id' => $this->businessProfile ? $this->businessProfile->id : null,
                    'contact_id' => $contact->id,
                    'message_id' => $messageId,
                    'content' => $caption ?? $documentFilename,
                    'type' => 'document',
                    'status' => 'sent',
                    'sender_type' => $humanSent ? 'humano' : 'system',
                    'receiver_type' => 'client',
                    'metadata' => $metadata
                ]);

                return true;
            }

            Log::error('WhatsApp API Error (Document)', [
                'response' => $response->json(),
                'contact' => $contact->phone_number
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('WhatsApp Service Error (Document)', [
                'error' => $e->getMessage(),
                'contact' => $contact->phone_number
            ]);

            return false;
        }
    }

    protected function sendMessageToWhatsApp($to, $message)
    {
        try {
            if (!$this->businessProfile) {
                Log::error('No business profile found');
                return false;
            }

            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => $message['type'] ?? 'text'
            ];

            if ($payload['type'] === 'text') {
                $payload['text'] = [
                    'body' => $message['text']['body']
                ];
            } elseif ($payload['type'] === 'interactive') {
                $payload['interactive'] = $message['interactive'];
            } elseif ($payload['type'] === 'contacts') {
                if (isset($message['contacts'])) {
                    $payload['contacts'] = $this->formatContacts($message['contacts']);
                } else {
                    Log::error('Error sending message', [
                        'error' => 'Contacts data not found in message',
                        'to' => substr($to, 0, 4) . '****' . substr($to, -4),
                        'type' => $payload['type']
                    ]);
                    return false;
                }
            }

            $response = Http::withToken($this->apiToken)
                ->post("{$this->baseUrl}/{$this->apiVersion}/{$this->businessProfile->phone_number_id}/messages", $payload);

            if ($response->successful()) {
                $data = $response->json();
                $messageId = $data['messages'][0]['id'] ?? null;

                Log::info('[sendMessageToWhatsApp] Message sent successfully', [
                    'to' => substr($to, 0, 4) . '****' . substr($to, -4),
                    'message_id' => $messageId,
                    'type' => $payload['type']
                ]);

                return [
                    'success' => true,
                    'message_id' => $messageId
                ];
            }

            Log::error('Failed to send message', [
                'response' => $response->json(),
                'to' => substr($to, 0, 4) . '****' . substr($to, -4),
                'type' => $payload['type']
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Error sending message', [
                'error' => $e->getMessage(),
                'to' => substr($to, 0, 4) . '****' . substr($to, -4),
                'type' => $payload['type'] ?? 'text'
            ]);
            return false;
        }
    }

    private function generateChatbotResponse(string $message, string $from): array
    {
        try {
            // Buscar respuesta específica en la base de datos
            $response = WhatsappChatbotResponse::where('keyword', strtolower($message))
                ->where('is_active', true)
                ->first();

            if ($response) {
                Log::info('[generateChatbotResponse] 🤖 Respuesta del chatbot encontrada', [
                    'keyword' => $response->keyword,
                    'tipo' => $response->type,
                    'show_menu' => $response->show_menu
                ]);

                // Si la respuesta es de tipo contacts, enviar primero el contacto
                if ($response->type === 'contacts') {
                    // Intentar obtener el contacto desde la base de datos primero
                    $contactData = $this->getContactFromDatabase($response->keyword);

                    // Si no se encuentra en BD, usar el campo contacts del chatbot response como fallback
                    if (!$contactData && $response->contacts) {
                        $contactData = is_string($response->contacts)
                            ? $response->contacts
                            : (is_array($response->contacts) ? json_encode($response->contacts) : null);
                    }

                    if ($contactData) {
                        $this->sendMessageToWhatsApp($from, [
                            'type' => 'contacts',
                            'contacts' => $contactData,
                            'text' => [
                                'body' => $response->response
                            ]
                        ]);
                    } else {
                        // Si no hay contacto disponible, enviar solo el mensaje de texto
                        $this->sendMessageToWhatsApp($from, [
                            'type' => 'text',
                            'text' => [
                                'body' => $response->response . "\n\n⚠️ Contacto no disponible en este momento."
                            ]
                        ]);
                    }

                    // Si debe mostrar menú, enviar el menú después
                    if ($response->show_menu) {
                        return $this->getMainMenu();
                    }

                    // Retornar null porque ya se envió el mensaje
                    return null;
                }

                // Si debe mostrar menú y es tipo text, enviar el menú con el texto como encabezado
                if ($response->show_menu && $response->type === 'text') {
                    return $this->getMainMenu($response->response);
                }

                return [
                    'type' => 'text',
                    'text' => ['body' => $response->response]
                ];
            }

            // Si no se encuentra respuesta específica, intentar con ChatGPT
            $config = WhatsappChatbotConfig::where('business_profile_id', $this->businessProfile->id)->first();
            if ($config && $config->chatgpt_enabled) {
                try {
                    $chatGPT = new ChatGPTService($config);
                    $aiResponse = $chatGPT->query($message);

                    if ($aiResponse) {
                        Log::info('[generateChatbotResponse] 🤖 Respuesta de ChatGPT', [
                            'message' => $message,
                            'response' => $aiResponse
                        ]);

                        // Enviar el menú principal con la respuesta de ChatGPT como encabezado
                        return $this->getMainMenu($aiResponse);
                    }
                } catch (\Exception $e) {
                    Log::error('[generateChatbotResponse] ❌ Error al consultar ChatGPT', [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Si no hay respuesta de ChatGPT o falló, enviar menú principal
            Log::info('[generateChatbotResponse] ℹ️ No se encontró respuesta específica, enviando menú principal');
            return $this->getMainMenu();

        } catch (\Exception $e) {
            Log::error('[generateChatbotResponse] ❌ Error', [
                'error' => $e->getMessage(),
                'linea' => $e->getLine()
            ]);
            return $this->getMainMenu();
        }
    }

    private function getMainMenu(?string $headerText = null): array
    {
        // Obtener los menús desde la base de datos
        $productosMenu = WhatsappMenu::where('action_id', 'menu_productos')->first();
        $pedidosMenu = WhatsappMenu::where('action_id', 'menu_pedido')->first();
        $infoMenu = WhatsappMenu::where('action_id', 'menu_info')->first();

        return [
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => [
                    'text' => $headerText ?? "¿En qué más puedo ayudarte?"
                ],
                'action' => [
                    'buttons' => [
                        [
                            'type' => 'reply',
                            'reply' => [
                                'id' => 'menu_productos',
                                'title' => $productosMenu ? $productosMenu->button_text : '🛍️ Productos'
                            ]
                        ],
                        [
                            'type' => 'reply',
                            'reply' => [
                                'id' => 'menu_pedido',
                                'title' => $pedidosMenu ? $pedidosMenu->button_text : '📦 Ver Pedidos'
                            ]
                        ],
                        [
                            'type' => 'reply',
                            'reply' => [
                                'id' => 'menu_info',
                                'title' => $infoMenu ? $infoMenu->button_text : 'ℹ️ Información'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    protected function prepareTemplateComponents(WhatsappTemplate $template, array $variables)
    {
        $components = [];
        $varIndex = 0;
        if (is_array($template->components)) {
            foreach ($template->components as $component) {
                $type = strtolower($component['type'] ?? '');
                if (in_array($type, ['header', 'body'])) {
                    $params = [];
                    $text = $component['text'] ?? '';
                    // Contar cuántos {{n}} hay en el texto
                    $varCount = substr_count($text, '{{');
                    for ($i = 0; $i < $varCount; $i++) {
                        $params[] = [
                            'type' => 'text',
                            'text' => $variables[$varIndex] ?? ''
                        ];
                        $varIndex++;
                    }
                    if ($varCount > 0) {
                        $components[] = [
                            'type' => $type,
                            'parameters' => $params
                        ];
                    }
                }
            }
        }
        return $components;
    }

    protected function markMessageAsRead($messageId, $to)
    {
        try {
            if (!$messageId || !$to) {
                Log::warning('⚠️ No se puede marcar como leído', [
                    'tiene_id' => !empty($messageId),
                    'tiene_destino' => !empty($to)
                ]);
                return false;
            }

            $response = Http::withToken($this->apiToken)
                ->post("{$this->baseUrl}/{$this->apiVersion}/{$this->businessProfile->phone_number_id}/messages", [
                    'messaging_product' => 'whatsapp',
                    'status' => 'read',
                    'message_id' => $messageId
                ]);

            if ($response->successful()) {
                Log::info('[markMessageAsRead] ✅ Mensaje marcado como leído', [
                    'id' => $messageId,
                    'para' => substr($to, 0, 4) . '****' . substr($to, -4)
                ]);
                return true;
            }

            Log::error('❌ Error al marcar como leído', [
                'id' => $messageId,
                'error' => $response->json()
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('❌ Error en markMessageAsRead', [
                'error' => $e->getMessage(),
                'linea' => $e->getLine()
            ]);
            return false;
        }
    }

    private function handleInteractiveMessage($data)
    {
        try {
            $from = $data['from'];
            $interactive = $data['interactive'];
            $messageId = $data['id'];

            Log::info('[handleInteractiveMessage] 📥 Mensaje interactivo recibido', [
                'de' => substr($from, 0, 4) . '****' . substr($from, -4),
                'tipo' => 'interactive',
                'id' => $messageId,
                'contenido' => $interactive
            ]);

            $this->markMessageAsRead($messageId, $from);

            $contact = WhatsappContact::where('phone_number', $from)->first();
            if (!$contact) {
                // Obtener datos del contacto del webhook
                $contactData = $message['contacts'][0] ?? [];
                $profile = $contactData['profile'] ?? [];
                $contactName = $profile['name'] ?? 'Contacto sin nombre';

                // Crear el contacto automáticamente
                $contact = WhatsappContact::create([
                    'business_profile_id' => $this->businessProfile->id,
                    'phone_number' => $from,
                    'name' => $contactName,
                    'status' => 'active'
                ]);

                Log::info('✅ Nuevo contacto creado', [
                    'phone' => $from,
                    'contact_id' => $contact->id,
                    'name' => $contactName
                ]);
            } else if ($contact->name === 'Contacto sin nombre') {
                // Si el contacto existe pero tiene nombre genérico, intentar actualizarlo
                $contactData = $message['contacts'][0] ?? [];
                $profile = $contactData['profile'] ?? [];
                $contactName = $profile['name'] ?? null;

                if ($contactName && $contactName !== 'Contacto sin nombre') {
                    $contact->name = $contactName;
                    $contact->save();

                    Log::info('✅ Nombre de contacto actualizado', [
                        'phone' => $from,
                        'contact_id' => $contact->id,
                        'old_name' => 'Contacto sin nombre',
                        'new_name' => $contactName
                    ]);
                }
            }

            // Guardar el mensaje
            $whatsappMessage = WhatsappMessage::create([
                'contact_id' => $contact->id,
                'business_profile_id' => $this->businessProfile->id,
                    'message_id' => $messageId,
                'content' => json_encode($interactive),
                'type' => 'interactive',
                'status' => 'received',
                'sender_type' => 'client',
                'receiver_type' => 'system'
            ]);

            $this->lastMessage = $whatsappMessage;

            // Refrescar el contacto desde la base de datos para obtener el valor actualizado de bot_enabled
            $contact->refresh();

            // Verificar si el bot está habilitado para este contacto ANTES de procesar cualquier respuesta
            if (!$contact->bot_enabled) {
                Log::info('[handleInteractiveMessage] 🛑 Bot detenido - Bot deshabilitado manualmente', [
                    'contact_id' => $contact->id,
                    'phone' => substr($from, 0, 4) . '****' . substr($from, -4),
                    'bot_enabled' => $contact->bot_enabled
                ]);
                return; // No procesar ninguna respuesta automática
            }

            // Si el bot está activado manualmente, NO verificar actividad humana reciente
            // El bot funcionará inmediatamente cuando esté activado

            // Determinar el tipo de respuesta interactiva
            $type = $interactive['type'];
            $content = $type === 'button_reply' ? $interactive['button_reply'] : $interactive['list_reply'];
            $buttonId = $content['id'];
            $buttonTitle = $content['title'];

            Log::info('[handleInteractiveMessage] Botón presionado', [
                'id' => $buttonId,
                'titulo' => $buttonTitle
            ]);

            $response = null;

            switch ($buttonId) {
                // Menús principales
                case 'menu_productos':
                case 'productos':
                    $response = $this->getProductsMenu();
                    break;
                case 'menu_pedido':
                    $response = $this->getOrderMenu();
                    break;
                case 'menu_info':
                    $response = $this->getInfoMenu();
                    break;
                case 'menu_principal':
                case 'return_to_menu':  // Agregado el caso para el botón de retorno
                    $response = $this->generateChatbotResponse('menu', $from);
                    break;

                // Información
                case 'horarios':
                case 'contacto':
                case 'envios':
                case 'pagos':
                case 'asesoria':
                case 'redes':  // Agregado el caso para redes sociales
                    // Buscar la respuesta en la base de datos
                    $chatbotResponse = WhatsappChatbotResponse::where('keyword', $buttonId)
                        ->where('is_active', true)
                        ->first();

                    if ($chatbotResponse) {
                        if ($chatbotResponse->type === 'contacts') {
                            // Intentar obtener el contacto desde la base de datos primero
                            $contactData = $this->getContactFromDatabase($buttonId);

                            // Si no se encuentra en BD, usar el campo contacts del chatbot response como fallback
                            if (!$contactData && $chatbotResponse->contacts) {
                                $contactData = is_string($chatbotResponse->contacts)
                                    ? $chatbotResponse->contacts
                                    : (is_array($chatbotResponse->contacts) ? json_encode($chatbotResponse->contacts) : null);
                            }

                            if ($contactData) {
                                // Enviar el contacto con formato de tarjeta
                                $this->sendMessage($from, [
                                    'type' => 'contacts',
                                    'contacts' => $contactData,
                                    'text' => ['body' => $chatbotResponse->response]
                                ]);
                            } else {
                                // Si no hay contacto disponible, enviar solo el mensaje de texto
                                $this->sendMessage($from, [
                                    'type' => 'text',
                                    'text' => ['body' => $chatbotResponse->response . "\n\n⚠️ Contacto no disponible en este momento."]
                                ]);
                            }
                        } else {
                            $this->sendMessage($from, [
                                'type' => 'text',
                                'text' => ['body' => $chatbotResponse->response]
                            ]);
                        }

                        // Obtener los menús desde la base de datos
                        $productosMenu = WhatsappMenu::where('action_id', 'menu_productos')->first();
                        $pedidosMenu = WhatsappMenu::where('action_id', 'menu_pedido')->first();
                        $infoMenu = WhatsappMenu::where('action_id', 'menu_info')->first();

                        // Enviar el menú principal después de un breve retraso
                        sleep(1); // Pequeño retraso para asegurar el orden de los mensajes

                        $response = [
                            'type' => 'interactive',
                            'interactive' => [
                                'type' => 'button',
                                'body' => [
                                    'text' => "¿En qué más puedo ayudarte?"
                                ],
                                'action' => [
                                    'buttons' => [
                                        [
                                            'type' => 'reply',
                                            'reply' => [
                                                'id' => 'menu_productos',
                                                'title' => $productosMenu ? $productosMenu->button_text : '🛍️ Productos'
                                            ]
                                        ],
                                        [
                                            'type' => 'reply',
                                            'reply' => [
                                                'id' => 'menu_pedido',
                                                'title' => $pedidosMenu ? $pedidosMenu->button_text : '📦 Ver Pedidos'
                                            ]
                                        ],
                                        [
                                            'type' => 'reply',
                                            'reply' => [
                                                'id' => 'menu_info',
                                                'title' => $infoMenu ? $infoMenu->button_text : 'ℹ️ Información'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ];
                    } else {
                        $response = [
                            'type' => 'text',
                            'text' => ['body' => 'Lo siento, esta información no está disponible en este momento.']
                        ];
                    }
                    break;

                case 'soporte':
                    // Buscar la respuesta en la base de datos
                    $chatbotResponse = WhatsappChatbotResponse::where('keyword', $buttonId)
                        ->where('is_active', true)
                        ->first();

                    if ($chatbotResponse) {
                        if ($chatbotResponse->type === 'contacts') {
                            // Intentar obtener el contacto desde la base de datos primero
                            $contactData = $this->getContactFromDatabase($buttonId);

                            // Si no se encuentra en BD, usar el campo contacts del chatbot response como fallback
                            if (!$contactData && $chatbotResponse->contacts) {
                                $contactData = is_string($chatbotResponse->contacts)
                                    ? $chatbotResponse->contacts
                                    : (is_array($chatbotResponse->contacts) ? json_encode($chatbotResponse->contacts) : null);
                            }

                            if ($contactData) {
                                // Enviar el contacto con formato de tarjeta
                                $this->sendMessage($from, [
                                    'type' => 'contacts',
                                    'contacts' => $contactData,
                                    'text' => ['body' => $chatbotResponse->response]
                                ]);
                            } else {
                                // Si no hay contacto disponible, enviar solo el mensaje de texto
                                $this->sendMessage($from, [
                                    'type' => 'text',
                                    'text' => ['body' => $chatbotResponse->response . "\n\n⚠️ Contacto no disponible en este momento."]
                                ]);
                            }

                            // Esperar un momento para asegurar que el contacto se envíe primero
                            sleep(1);

                            // Obtener los menús desde la base de datos
                            $productosMenu = WhatsappMenu::where('action_id', 'menu_productos')->first();
                            $pedidosMenu = WhatsappMenu::where('action_id', 'menu_pedido')->first();
                            $infoMenu = WhatsappMenu::where('action_id', 'menu_info')->first();

                            // Enviar el menú principal
                            $response = [
                                'type' => 'interactive',
                                'interactive' => [
                                    'type' => 'button',
                                    'body' => [
                                        'text' => "¿En qué más puedo ayudarte?"
                                    ],
                                    'action' => [
                                        'buttons' => [
                                            [
                                                'type' => 'reply',
                                                'reply' => [
                                                    'id' => 'menu_productos',
                                                    'title' => $productosMenu ? $productosMenu->button_text : '🛍️ Productos'
                                                ]
                                            ],
                                            [
                                                'type' => 'reply',
                                                'reply' => [
                                                    'id' => 'menu_pedido',
                                                    'title' => $pedidosMenu ? $pedidosMenu->button_text : '📦 Ver Pedidos'
                                                ]
                                            ],
                                            [
                                                'type' => 'reply',
                                                'reply' => [
                                                    'id' => 'menu_info',
                                                    'title' => $infoMenu ? $infoMenu->button_text : 'ℹ️ Información'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ];
                        } else {
                            $this->sendMessage($from, [
                                'type' => 'text',
                                'text' => ['body' => $chatbotResponse->response]
                            ]);
                        }
                    }
                    break;
                case 'ventas':
                    $response = WhatsappChatbotResponse::where('keyword', 'ventas')->first();
                    if ($response && $response->type === 'contacts') {
                        $this->sendMessageToWhatsApp($from, [
                            'type' => 'contacts',
                            'contacts' => $response->contacts // <-- Solo el string plano
                        ]);
                    }
                    // Obtener opciones del menú
                    $menuOptions = WhatsappChatbotResponse::where('is_active', true)
                        ->where('show_menu', true)
                        ->orderBy('order')
                        ->get();
                    break;

                case 'faq':
                    // Buscar la respuesta en la base de datos
                    $chatbotResponse = WhatsappChatbotResponse::where('keyword', $buttonId)
                        ->where('is_active', true)
                        ->first();

                    if ($chatbotResponse) {
                        // Enviar la respuesta de texto
                        $this->sendMessage($from, [
                            'type' => 'text',
                            'text' => ['body' => $chatbotResponse->response]
                        ]);

                        // Obtener los menús desde la base de datos
                        $productosMenu = WhatsappMenu::where('action_id', 'menu_productos')->first();
                        $pedidosMenu = WhatsappMenu::where('action_id', 'menu_pedido')->first();
                        $infoMenu = WhatsappMenu::where('action_id', 'menu_info')->first();

                        // Enviar el menú principal después de un breve retraso
                        sleep(1); // Pequeño retraso para asegurar el orden de los mensajes

            $response = [
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'button',
                    'body' => [
                                    'text' => "¿En qué más puedo ayudarte?"
                                ],
                                'action' => [
                                    'buttons' => [
                                        [
                                            'type' => 'reply',
                                            'reply' => [
                                                'id' => 'menu_productos',
                                                'title' => $productosMenu ? $productosMenu->button_text : '🛍️ Productos'
                                            ]
                                        ],
                                        [
                                            'type' => 'reply',
                                            'reply' => [
                                                'id' => 'menu_pedido',
                                                'title' => $pedidosMenu ? $pedidosMenu->button_text : '📦 Ver Pedidos'
                                            ]
                                        ],
                                        [
                                            'type' => 'reply',
                                            'reply' => [
                                                'id' => 'menu_info',
                                                'title' => $infoMenu ? $infoMenu->button_text : 'ℹ️ Información'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ];
                    } else {
                        $response = [
                            'type' => 'text',
                            'text' => ['body' => 'Lo siento, esta información no está disponible en este momento.']
                        ];
                    }
                    break;

                // Navegación de productos
                case 'ver_mas_precios':
                    $response = $this->getRemainingProducts();
                    break;
                case 'volver_productos':
                    $response = $this->getProductsMenu();
                    break;
                case 'seguir_comprando':
                    $response = $this->getProductsMenu();
                    break;

                // Carrito y compras
                case 'ver_carrito':
                    $response = $this->getCartContents($contact);
                    break;
                case 'finalizar_compra':
                    $response = $this->finalizarCompra($contact);
                    break;

                // Procesamiento de imagen
                case 'cancelar_proceso_imagen':
                    $response = $this->processImageMessage($contact);
                    break;

                // Acciones de productos
                default:
                    // Si es un número simple, es una selección del menú interactivo
                    if (is_numeric($buttonId)) {
                        Log::info('🔍 Producto seleccionado del menú', ['id' => $buttonId]);
                        $response = $this->getProductDetails(intval($buttonId));
                    } elseif (strpos($buttonId, 'comprar_') === 0) {
                        $parts = explode('_', $buttonId);
                        $productId = intval($parts[1]);
                        Log::info('🛒 Iniciando compra de producto', ['id' => $productId, 'button_id' => $buttonId]);
                        $response = $this->showQuantitySelection($productId);
                        if ($response == 1) {
                            $response = $this->addToCart($contact, $productId, 1);
                        }
                    } elseif (strpos($buttonId, 'ver_producto_') === 0) {
                        $parts = explode('_', $buttonId);
                        $productId = intval($parts[2]);
                        Log::info('🔍 Buscando producto por ID', ['id' => $productId, 'button_id' => $buttonId]);
                        $response = $this->getProductDetails($productId);
                    } elseif (strpos($buttonId, 'producto_') === 0) {
                        $parts = explode('_', $buttonId);
                        $productId = intval($parts[1]);
                        Log::info('🔍 Buscando producto por ID', ['id' => $productId, 'button_id' => $buttonId]);
                        $response = $this->getProductDetails($productId);
                    } elseif (strpos($buttonId, 'cantidad_') === 0) {
                        $parts = explode('_', $buttonId);
                        $quantity = intval($parts[1]);
                        $productId = intval($parts[2]);
                        Log::info('📦 Agregando al carrito', ['product_id' => $productId, 'quantity' => $quantity]);
                        $response = $this->addToCart($contact, $productId, $quantity);
                    } elseif (strpos($buttonId, 'agregar_') === 0) {
                        $parts = explode('_', $buttonId);
                        $productId = intval($parts[2]);
                        $quantity = intval($parts[3]);
                        $response = $this->addToCart($contact, $productId, $quantity);
                    } elseif (strpos($buttonId, 'confirmar_pedido_') === 0) {
                        $parts = explode('_', $buttonId);
                        $cartId = intval($parts[2]);
                        $response = $this->confirmarPedido($contact, $cartId);
                    } elseif (strpos($buttonId, 'cancelar_pedido_') === 0) {
                        $parts = explode('_', $buttonId);
                        $cartId = intval($parts[2]);
                        $response = $this->cancelarPedido($contact, $cartId);
                    } elseif (strpos($buttonId, 'pago_transferencia_') === 0) {
                        $parts = explode('_', $buttonId);
                        $cartId = intval($parts[2]);
                        $response = $this->procesarPagoTransferencia($contact, $cartId);
                    } elseif (strpos($buttonId, 'pago_efectivo_') === 0) {
                        $parts = explode('_', $buttonId);
                        $cartId = intval($parts[2]);
                        $response = $this->procesarPagoEfectivo($contact, $cartId);
                    } elseif (preg_match('/^pago_tarjeta_(\d+)$/', $buttonId, $matches)) {
                        $cartId = $matches[1];
                        $response = $this->procesarPagoTarjeta($contact, $cartId);
                    }
                    break;
            }

            if ($response) {
                $this->sendMessage($from, $response);
            } else {
                Log::warning('⚠️ No se encontró respuesta para el botón', [
                    'button_id' => $buttonId,
                    'button_title' => $buttonTitle
                ]);
            }

        } catch (\Exception $e) {
            Log::error('❌ Error al procesar mensaje interactivo', [
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'mensaje' => $data
            ]);
        }
    }

    private function processImageMessage(WhatsappContact $contact)
    {
        try {
            // Obtener el último mensaje de imagen del contacto
            $lastImageMessage = WhatsappMessage::where('contact_id', $contact->id)
                ->where('type', 'image')
                ->latest()
                ->first();

            if (!$lastImageMessage) {
                return [
                    'type' => 'text',
                    'text' => [
                        'body' => "❌ No se encontró ninguna imagen para procesar. Por favor, envía una imagen primero."
                    ]
                ];
            }

            // Aquí puedes agregar la lógica para procesar la imagen
            // Por ejemplo, análisis de imagen, OCR, etc.

            // Limpiar el proceso activo
            $this->clearActiveProcess($contact);

            return [
                'type' => 'text',
                'text' => [
                    'body' => "✅ Imagen procesada correctamente. ¿Qué más puedo hacer por ti?"
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error al procesar imagen: ' . $e->getMessage());
            return [
                'type' => 'text',
                'text' => [
                    'body' => "❌ Lo siento, hubo un error al procesar la imagen. Por favor, intenta de nuevo."
                ]
            ];
        }
    }

    private function clearActiveProcess(WhatsappContact $contact)
    {
        // Limpiar carrito activo con nota pendiente
        $cart = WhatsappCart::where('contact_id', $contact->id)
            ->where('status', 'active')
            ->first();

        if ($cart) {
            $metadata = $cart->metadata ?? [];
            unset($metadata['pending_note']);
            $cart->metadata = $metadata;
            $cart->save();
        }

        // Limpiar mensaje de imagen pendiente de nota
        $lastImageMessage = WhatsappMessage::where('contact_id', $contact->id)
            ->where('type', 'image')
            ->where('metadata->pending_note', true)
            ->latest()
            ->first();

        if ($lastImageMessage) {
            $metadata = $lastImageMessage->metadata;
            unset($metadata['pending_note']);
            $lastImageMessage->metadata = $metadata;
            $lastImageMessage->save();
        }

        // Limpiar mensaje pendiente de cantidad
        $lastMessage = WhatsappMessage::where('contact_id', $contact->id)
            ->whereNotNull('metadata->pending_quantity')
            ->latest()
            ->first();

        if ($lastMessage) {
            $metadata = $lastMessage->metadata;
            unset($metadata['pending_quantity']);
            $lastMessage->metadata = $metadata;
            $lastMessage->save();
        }
    }

    private function getCartContents($contact)
    {
        $cart = WhatsappCart::where('contact_id', $contact->id)
                ->where('status', 'active')
                ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return [
                'type' => 'text',
                'text' => ["Tu carrito está vacío. ¿Qué te gustaría comprar?"],
                        'buttons' => [
                            [
                                'type' => 'reply',
                                'reply' => [
                            'id' => 'productos',
                            'title' => '🛍️ Ver productos'
                                ]
                            ],
                            [
                                'type' => 'reply',
                                'reply' => [
                            'id' => 'menu_principal',
                            'title' => '🏠 Menú principal'
                        ]
                    ]
                ]
            ];
        }

        $message = "🛒 *Tu Carrito*\n\n";
        $total = 0;

        foreach ($cart->items as $item) {
            $price = $item->price;
            $subtotal = $price * $item->quantity;
            $total += $subtotal;
            $message .= "• {$item->name}\n";
            $message .= "  Cantidad: {$item->quantity}\n";
            $message .= "  Precio: \${$price}\n";
            $message .= "  Subtotal: \${$subtotal}\n\n";
        }

        $message .= "💰 *Total: \${$total}*\n\n";
        $message .= "¿Qué deseas hacer?";

                return [
                    'type' => 'interactive',
                    'interactive' => [
                        'type' => 'button',
                        'body' => [
                    'text' => $message
                        ],
                        'action' => [
                            'buttons' => [
                                [
                                    'type' => 'reply',
                                    'reply' => [
                                'id' => 'seguir_comprando',
                                'title' => '🛍️ Seguir comprando'
                            ]
                        ],
                        [
                            'type' => 'reply',
                            'reply' => [
                                'id' => 'finalizar_compra',
                                'title' => '✅ Finalizar compra'
                            ]
                        ],
                        [
                            'type' => 'reply',
                            'reply' => [
                                'id' => 'menu_principal',
                                'title' => '🏠 Menú principal'
                            ]
                                ]
                            ]
                        ]
                    ]
                ];
            }

    private function addToCart(WhatsappContact $contact, $priceId, $quantity = 1)
    {
        try {
            $price = WhatsappPrice::findOrFail($priceId);
            $unitPrice = $price->is_promo ? $price->promo_price : $price->price;

            // Buscar carrito activo o crear uno nuevo
            $cart = WhatsappCart::firstOrCreate(
                ['contact_id' => $contact->id, 'status' => 'active'],
                ['total' => 0]
            );

            // Verificar si el producto ya está en el carrito
            $existingItem = $cart->items()->where('whatsapp_price_id', $priceId)->first();

            if ($existingItem) {
                // Incrementar cantidad si ya existe
                $existingItem->quantity += $quantity;
                $existingItem->save();
            } else {
                // Crear nuevo item si no existe
                $cart->items()->create([
                    'whatsapp_price_id' => $price->id,
                    'name' => $price->name,
                    'price' => $unitPrice,
                    'quantity' => $quantity
                ]);
            }

            // Recalcular total
            $cart->total = $cart->items()->sum(DB::raw('price * quantity'));
            $cart->save();

            return [
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'button',
                    'body' => [
                        'text' => "✅ *Producto agregado al carrito*\n\n" .
                            "• {$price->name}\n" .
                            "• Cantidad: {$quantity}\n" .
                            "• Precio unitario: $" . $unitPrice . "\n" .
                            "• Subtotal: $" . ($unitPrice * $quantity) . "\n" .
                            ($price->is_promo ? "• ¡Aprovecha esta oferta! 🎉\n" : "") . "\n" .
                            "¿Qué deseas hacer?"
                    ],
                    'action' => [
                        'buttons' => [
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'ver_carrito',
                                    'title' => '🛒 Ver carrito'
                                ]
                            ],
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'seguir_comprando',
                                    'title' => '🛍️ Seguir comprando'
                                ]
                            ],
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'menu_principal',
                                    'title' => '🏠 Menú principal'
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error al agregar al carrito', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'contact_id' => $contact->id,
                'price_id' => $priceId
            ]);
            return [
                'type' => 'text',
                'text' => ['body' => 'Lo siento, ha ocurrido un error al agregar el producto al carrito. Por favor, intenta nuevamente.']
            ];
        }
    }

    private function finalizarCompra($contact)
    {
        try {
            $cart = WhatsappCart::where('contact_id', $contact->id)
                ->where('status', 'active')
                ->first();

            if (!$cart || $cart->items->isEmpty()) {
                return [
                    'type' => 'interactive',
                    'interactive' => [
                        'type' => 'button',
                        'body' => [
                            'text' => "¡Gracias por tu interés! 😊\n\n" .
                                "Tu carrito está vacío en este momento.\n\n" .
                                "¿En qué más puedo ayudarte?"
                        ],
                        'action' => [
                            'buttons' => [
                                [
                                    'type' => 'reply',
                                    'reply' => [
                                'id' => 'productos',
                                'title' => '🛍️ Ver productos'
                            ]
                        ],
                        [
                            'type' => 'reply',
                            'reply' => [
                                'id' => 'menu_principal',
                                'title' => '🏠 Menú principal'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
            }

            // Si el carrito no tiene nota, solicitar la nota
            if (empty($cart->note)) {
                $metadata = $cart->metadata ?? [];
                $metadata['pending_note'] = true;
                $cart->metadata = $metadata;
                $cart->save();

                Log::info('[finalizarCompra] 📝 Solicitando nota para el pedido', [
                    'cart_id' => $cart->id,
                    'contact_id' => $contact->id
                ]);

                return [
                    'type' => 'text',
                    'text' => ['body' => "Por favor, escribe una nota para tu pedido (opcional).\nPuedes incluir instrucciones especiales, preferencias o cualquier detalle que consideres importante.\n\nEscribe 'sin nota' si no deseas agregar una nota."]
                ];
            }

            // Si el carrito no tiene método de pago, mostrar opciones de pago
            if (empty($cart->payment_method)) {
            $metadata = $cart->metadata ?? [];
                $metadata['pending_payment_method'] = true;
            $cart->metadata = $metadata;
            $cart->save();

                Log::info('[finalizarCompra] 💳 Solicitando método de pago', [
                    'cart_id' => $cart->id,
                    'contact_id' => $contact->id
                ]);

                return [
                    'type' => 'interactive',
                    'interactive' => [
                        'type' => 'list',
                        'body' => [
                            'text' => "💳 *Selecciona el método de pago*\n\n" .
                                "Por favor, elige cómo deseas realizar el pago:"
                        ],
                        'action' => [
                            'button' => 'Seleccionar método de pago',
                            'sections' => [
                                [
                                    'title' => 'Métodos de pago disponibles',
                                    'rows' => [
                                        [
                                        'id' => 'pago_transferencia_' . $cart->id,
                                            'title' => '🏦 Transferencia',
                                            'description' => 'Pago mediante transferencia bancaria'
                                ],
                                [
                                        'id' => 'pago_efectivo_' . $cart->id,
                                            'title' => '💵 Pago en efectivo',
                                            'description' => 'Pago en efectivo al recibir el pedido'
                                        ],
                                        [
                                            'id' => 'pago_tarjeta_' . $cart->id,
                                            'title' => '💳 Pago con tarjeta',
                                            'description' => 'Pago con tarjeta de crédito/débito'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
            }

            // Preparar los detalles del pedido para guardar en metadata
            $orderDetails = [
                'order_number' => 'ORD-' . str_pad($cart->id, 6, '0', STR_PAD_LEFT),
                'items' => [],
                'total' => $cart->total,
                'note' => $cart->note,
                'created_at' => $cart->created_at->format('Y-m-d H:i:s'),
                'status' => $cart->status,
                'payment_method' => $cart->payment_method,
                'payment_status' => $cart->payment_status
            ];

            foreach ($cart->items as $item) {
                $orderDetails['items'][] = [
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->price * $item->quantity
                ];
            }

            // Guardar los detalles del pedido en metadata
            $metadata = $cart->metadata ?? [];
            $metadata['order_details'] = $orderDetails;
            $cart->metadata = $metadata;
            $cart->save();

            // Preparar resumen del pedido
            $message = "📋 *Resumen de tu Pedido*\n\n";
            $message .= "📦 *Número de Pedido:* {$orderDetails['order_number']}\n\n";
            $message .= "📋 *Detalles del Pedido:*\n";

            foreach ($orderDetails['items'] as $item) {
                $message .= "• {$item['name']}\n";
                $message .= "  Cantidad: {$item['quantity']}\n";
                $message .= "  Precio: \${$item['price']}\n";
                $message .= "  Subtotal: \${$item['subtotal']}\n\n";
            }

            $message .= "💰 *Total:* \${$orderDetails['total']}\n\n";
            $message .= "💳 *Método de pago:* " . $this->getPaymentMethodText($cart->payment_method) . "\n\n";

            if ($cart->note && $cart->note !== 'sin nota') {
                $message .= "📝 *Nota del pedido:*\n{$cart->note}\n\n";
            }

            $message .= "¿Confirmas tu pedido?";

            return [
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'button',
                    'body' => [
                        'text' => $message
                    ],
                    'action' => [
                        'buttons' => [
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'confirmar_pedido_' . $cart->id,
                                    'title' => '✅ Confirmar pedido'
                                ]
                            ],
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'cancelar_pedido_' . $cart->id,
                                    'title' => '❌ Cancelar pedido'
                                ]
                            ]
                        ]
                    ]
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error al finalizar compra', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'type' => 'text',
                'text' => ['body' => 'Lo siento, ha ocurrido un error al procesar tu compra.']
            ];
        }
    }

    private function getPaymentMethodText($method)
    {
        return match ($method) {
            'transferencia' => 'Transferencia bancaria',
            'efectivo' => 'Pago en efectivo',
            'tarjeta' => 'Pago con tarjeta',
            default => 'No especificado'
        };
    }

    /**
     * Procesa los mensajes de texto recibidos de WhatsApp
     * Esta función maneja:
     * 1. Creación/actualización de contactos
     * 2. Guardado de mensajes
     * 3. Procesamiento de notas para pedidos
     * 4. Búsqueda de productos por SKU
     * 5. Generación de respuestas del chatbot
     */
    private function handleTextMessage($message)
    {
        try {
            // Validar que el mensaje tenga la estructura correcta
            if (!isset($message['text'])) {
                Log::error('[handleTextMessage] ❌ Estructura de mensaje inválida', [
                    'message' => $message
                ]);
                return;
            }

            // Obtener el texto del mensaje, manejando tanto strings como arrays
            $text = is_array($message['text']) ? strtolower($message['text']['body']) : strtolower($message['text']);
            $from = $message['from'];
            $contact = WhatsappContact::where('phone_number', $from)->first();

            // Detectar si el cliente pide el catálogo (verificar bot_enabled antes)
            if (preg_match('/(catalogo|catálogo|productos|precios|lista de precios|ver productos)/i', $text)) {
                if ($contact) {
                    $contact->refresh(); // Refrescar para obtener valor actualizado
                    if (!$contact->bot_enabled) {
                        Log::info('[handleTextMessage] 🛑 Catálogo no enviado - Bot deshabilitado manualmente', [
                            'contact_id' => $contact->id,
                            'phone' => substr($from, 0, 4) . '****' . substr($from, -4)
                        ]);
                        return;
                    }
                }
                $this->sendCatalog($from);
                return;
            }

            Log::info('[inicio Texto] handleTextMessage');
            // Extraer información básica del mensaje
            $from = $message['from']; // Número de teléfono del remitente
            $text = is_array($message['text']) ? $message['text']['body'] : $message['text']; // Obtener el texto del mensaje
            $messageId = $message['id']; // ID único del mensaje

            // Lista de respuestas comunes que no deben ser tratadas como SKUs
            $commonResponses = ['no', 'si', 'ok', 'okay', 'gracias', 'thanks', 'bye', 'adios', 'chao', 'hola', 'hi', 'hello'];

            // Marcar el mensaje como leído en WhatsApp
            $this->markMessageAsRead($messageId, $from);

            // Buscar o crear el contacto en la base de datos
            $contact = WhatsappContact::where('phone_number', $from)->first();
            if (!$contact) {
                // Si el contacto no existe, obtener sus datos del webhook
                $contactData = $message['contacts'][0] ?? [];
                $profile = $contactData['profile'] ?? [];
                $contactName = $profile['name'] ?? 'Contacto sin nombre';

                // Crear nuevo contacto en la base de datos
                $contact = WhatsappContact::create([
                    'business_profile_id' => $this->businessProfile->id,
                    'phone_number' => $from,
                    'name' => $contactName,
                    'status' => 'active'
                ]);

                Log::info('[handleTextMessage] ✅ Nuevo contacto creado', [
                    'phone' => $from,
                    'contact_id' => $contact->id,
                    'name' => $contactName
                ]);
            } else if ($contact->name === 'Contacto sin nombre') {
                // Si el contacto existe pero tiene nombre genérico, intentar actualizarlo con el nombre real
                $contactData = $message['contacts'][0] ?? [];
                $profile = $contactData['profile'] ?? [];
                $contactName = $profile['name'] ?? null;

                if ($contactName && $contactName !== 'Contacto sin nombre') {
                    $contact->name = $contactName;
                    $contact->save();

                    Log::info('[handleTextMessage] ✅ Nombre de contacto actualizado', [
                        'phone' => $from,
                        'contact_id' => $contact->id,
                        'old_name' => 'Contacto sin nombre',
                        'new_name' => $contactName
                    ]);
                }
            } else {
                Log::info('[handleTextMessage] 📝 Contacto encontrado en la base de dato');
            }

            // Guardar el mensaje en la base de datos
            $whatsappMessage = WhatsappMessage::create([
                'contact_id' => $contact->id,
                'business_profile_id' => $this->businessProfile->id,
                'message_id' => $messageId,
                'content' => $text,
                'type' => 'text',
                'status' => 'received',
                'sender_type' => 'client',
                'receiver_type' => 'system'
            ]);

            $this->lastMessage = $whatsappMessage;

            // Variables para controlar el flujo de procesamiento
            $response = null;
            $processHandled = false;

            // Verificar si hay un carrito activo esperando una nota
            $cart = WhatsappCart::where('contact_id', $contact->id)
                ->where('status', 'active')
                ->first();

            if ($cart && isset($cart->metadata['pending_note']) && $cart->metadata['pending_note']) {
                // Si hay un carrito esperando nota, procesar el mensaje como nota del pedido
                Log::info('[handleTextMessage] 📝 Procesando nota para pedido', [
                    'cart_id' => $cart->id,
                    'note' => $text
                ]);

                // Actualizar la nota del carrito y continuar con el proceso de compra
                $cart->note = $text;
                $metadata = $cart->metadata ?? [];
                unset($metadata['pending_note']);
                $cart->metadata = $metadata;
                $cart->save();

                // Continuar con el proceso de finalización de compra
                $response = $this->finalizarCompra($contact);
                $processHandled = true;
            }

            // Si no se procesó como nota, verificar si es un SKU de producto
            if (!$processHandled) {
                // Solo buscar productos si el mensaje no es una respuesta común y tiene más de 2 caracteres
                if (!in_array(strtolower($text), $commonResponses) && strlen($text) > 2) {
                    // Buscar producto por SKU o nombre en la base de datos
                    $product = WhatsappPrice::where(function($query) use ($text) {
                            $query->where('sku', 'like', '%' . $text . '%')
                                  ->orWhere('name', 'like', '%' . $text . '%');
                        })
                        ->where('is_active', true)
                        ->first();

                    if ($product) {
                        // Si se encuentra el producto, mostrar sus detalles
                        Log::info('[handleTextMessage] 🔍 Producto encontrado', [
                            'search_term' => $text,
                            'product_id' => $product->id
                        ]);
                        $response = $this->getProductDetails($product->id);
                        $processHandled = true;
                    }
                }
            }

            // Si no se procesó como SKU, generar respuesta del chatbot
            if (!$processHandled) {
                // Refrescar el contacto desde la base de datos para obtener el valor actualizado de bot_enabled
                $contact->refresh();

                // Verificar si el bot está habilitado para este contacto ANTES de generar respuesta
                if (!$contact->bot_enabled) {
                    Log::info('[handleTextMessage] 🛑 Bot detenido - Bot deshabilitado manualmente', [
                        'contact_id' => $contact->id,
                        'phone' => substr($from, 0, 4) . '****' . substr($from, -4),
                        'message_content' => substr($text, 0, 100),
                        'bot_enabled' => $contact->bot_enabled
                    ]);
                    // No generar ni enviar respuesta automática
                    $response = null;
                } else {
                    // Si el bot está activado, responder inmediatamente sin verificar actividad humana reciente
                    $response = $this->generateChatbotResponse($text, $from);
                }
            }

            // Enviar la respuesta al usuario si existe
            if ($response) {
                $this->sendMessage($from, $response);
            }

            // Registrar el procesamiento exitoso del mensaje
            Log::info('[handleTextMessage] ✅ Mensaje de texto procesado', [
                'id' => $messageId,
                'contacto' => substr($from, 0, 4) . '****' . substr($from, -4),
                'nombre' => $contact->name
            ]);

        } catch (\Exception $e) {
            // Registrar cualquier error que ocurra durante el procesamiento
            Log::error('[handleTextMessage] ❌ Error al procesar mensaje de texto', [
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'mensaje' => $message
            ]);
        }
    }

    private function handleImageMessage($message)
    {
        try {
            // Validar datos requeridos
            if (empty($message['from']) || empty($message['id'])) {
                Log::warning('⚠️ Datos básicos de mensaje incompletos', [
                    'tiene_from' => !empty($message['from']),
                    'tiene_id' => !empty($message['id'])
                ]);
                return;
            }

            // Verificar si el mensaje ya fue procesado
            $existingMessage = WhatsappMessage::where('message_id', $message['id'])->first();
            if ($existingMessage) {
                Log::info('⏭️ Mensaje de imagen ya procesado anteriormente', [
                    'message_id' => $message['id']
                ]);
                return;
            }

            // Obtener datos del contacto
            $contactData = $message['contacts'][0] ?? [];
            $profile = $contactData['profile'] ?? [];
            $contactName = $profile['name'] ?? 'Contacto sin nombre';
            $waId = $contactData['wa_id'] ?? null;

            // Crear o actualizar contacto
            $contact = WhatsappContact::where('phone_number', $message['from'])->first();
            if (!$contact) {
                // Crear nuevo contacto con el nombre del webhook
                $contact = WhatsappContact::create([
                    'business_profile_id' => $this->businessProfile->id,
                    'phone_number' => $message['from'],
                    'name' => $contactName,
                    'status' => 'active'
                ]);

                Log::info('✅ Nuevo contacto creado', [
                    'phone' => $message['from'],
                    'contact_id' => $contact->id,
                    'name' => $contactName
                ]);
            } else if ($contact->name === 'Contacto sin nombre') {
                // Si el contacto existe pero tiene nombre genérico, intentar actualizarlo
                if ($contactName && $contactName !== 'Contacto sin nombre') {
                    $contact->name = $contactName;
                    $contact->save();

                    Log::info('✅ Nombre de contacto actualizado', [
                        'phone' => $message['from'],
                        'contact_id' => $contact->id,
                        'old_name' => 'Contacto sin nombre',
                        'new_name' => $contactName
                    ]);
                }
            }

            // Verificar si hay un proceso activo
            if ($this->isProcessActive($contact)) {
                Log::warning('⚠️ Imagen recibida durante proceso activo', [
                    'contact_id' => $contact->id,
                    'message_id' => $message['id']
                ]);

                // Guardar la imagen temporalmente en metadata del último mensaje
                $imageData = [
                    'image_id' => $message['text']['id'] ?? null,
                    'mime_type' => $message['text']['mime_type'] ?? null,
                    'sha256' => $message['text']['sha256'] ?? null,
                    'caption' => $message['text']['caption'] ?? null
                ];

                // Enviar mensaje de confirmación con opciones
                    $response = [
                    'type' => 'interactive',
                    'interactive' => [
                        'type' => 'button',
                        'body' => [
                            'text' => "⚠️ *Proceso en curso*\n\n" .
                                "Tienes un proceso activo que necesita ser completado.\n\n" .
                                "¿Qué deseas hacer?\n\n" .
                                "1️⃣ Cancelar el proceso actual y procesar la imagen\n" .
                                "2️⃣ Continuar con el proceso actual"
                        ],
                        'action' => [
                            'buttons' => [
                                [
                                    'type' => 'reply',
                                    'reply' => [
                                        'id' => 'cancelar_proceso_imagen',
                                        'title' => '📸 Procesar imagen'
                                    ]
                                ],
                                [
                                    'type' => 'reply',
                                    'reply' => [
                                        'id' => 'continuar_proceso',
                                        'title' => '⏳ Continuar proceso'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];

                // Guardar el mensaje de imagen temporalmente
                $whatsappMessage = WhatsappMessage::create([
                    'contact_id' => $contact->id,
                    'business_profile_id' => $this->businessProfile->id,
                    'message_id' => $message['id'],
                    'sender_type' => 'client',
                    'receiver_type' => 'system',
                    'content' => json_encode($imageData),
                    'type' => 'image',
                    'status' => 'pending',
                    'metadata' => [
                        'timestamp' => $message['timestamp'],
                        'wa_id' => $waId,
                        'pending_note' => true,
                        'image_processed' => false,
                        'is_pending_confirmation' => true
                    ]
                ]);

                $this->sendMessage($message['from'], $response);
                return;
            }

            // Si no hay proceso activo, continuar con el procesamiento normal de la imagen
            // Extraer datos de la imagen del mensaje
            $imageData = [
                'image_id' => $message['text']['id'] ?? null,
                'mime_type' => $message['text']['mime_type'] ?? null,
                'sha256' => $message['text']['sha256'] ?? null,
                'caption' => $message['text']['caption'] ?? null
            ];

            // Log para depuración
            Log::info('📸 Datos de imagen recibidos', [
                'image_id' => $imageData['image_id'],
                'mime_type' => $imageData['mime_type'],
                'sha256' => $imageData['sha256']
            ]);

            // Verificar datos de la imagen
            if (empty($imageData['image_id']) || empty($imageData['mime_type'])) {
                Log::error('❌ Datos de imagen incompletos', [
                    'tiene_id' => !empty($imageData['image_id']),
                    'tiene_mime_type' => !empty($imageData['mime_type']),
                    'datos' => $imageData,
                    'mensaje_original' => $message
                ]);
                return;
            }

            // Crear mensaje de imagen
            $whatsappMessage = WhatsappMessage::create([
                'contact_id' => $contact->id,
                'business_profile_id' => $this->businessProfile->id,
                'message_id' => $message['id'],
                'sender_type' => 'client',
                'receiver_type' => 'system',
                'content' => json_encode($imageData),
                'type' => 'image',
                'status' => 'received',
                'metadata' => [
                    'timestamp' => $message['timestamp'],
                    'wa_id' => $waId,
                    'pending_note' => true,
                    'image_processed' => false
                ]
            ]);

            // Establecer como último mensaje
            $this->lastMessage = $whatsappMessage;

            // Enviar solicitud de nota
            $response = [
                'type' => 'text',
                'text' => [
                    'body' => "📸 *Imagen recibida*\n\n" .
                        "Por favor, agrega una nota o descripción para esta imagen.\n" .
                        "Escribe 'sin nota' si no deseas agregar una descripción."
                ]
            ];

            $this->sendMessage($message['from'], $response);

            Log::info('✅ Mensaje de imagen procesado', [
                'id' => $message['id'],
                'contacto' => substr($message['from'], 0, 4) . '****' . substr($message['from'], -4),
                'nombre' => $contactName,
                'image_id' => $imageData['image_id']
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error procesando mensaje de imagen', [
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'mensaje' => $message,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function saveMessage(array $messageData)
    {
        try {
            // Verificar si el mensaje ya existe
            $messageId = is_array($messageData['message_id'])
                ? $messageData['message_id']['message_id']
                : $messageData['message_id'];

            $existingMessage = WhatsappMessage::where('message_id', $messageId)->first();
            if ($existingMessage) {
                Log::info('⏭️ Mensaje ya guardado', ['id' => $messageId]);
                return $existingMessage;
            }

            // Obtener datos del contacto
            $contact = WhatsappContact::find($messageData['contact_id']);
            if (!$contact) {
                Log::error('❌ Contacto no encontrado', ['contact_id' => $messageData['contact_id']]);
                return null;
            }

            // Determinar el contenido del mensaje según el tipo
            $content = '';
            if ($messageData['type'] === 'text') {
                $content = $messageData['content'] ?? '';
            } elseif ($messageData['type'] === 'interactive') {
                $interactiveContent = json_decode($messageData['content'], true);
                if (isset($interactiveContent['button_reply'])) {
                    $content = $interactiveContent['button_reply']['title'] ?? '';
                } elseif (isset($interactiveContent['list_reply'])) {
                    $content = $interactiveContent['list_reply']['title'] ?? '';
                } elseif (isset($interactiveContent['interactive'])) {
                    $content = $interactiveContent['interactive']['body']['text'] ?? '';
                }
            }

            // Crear mensaje
            $whatsappMessage = WhatsappMessage::create([
                'contact_id' => $messageData['contact_id'],
                'business_profile_id' => $contact->business_profile_id,
                'message_id' => $messageId,
                'sender_type' => $messageData['from'] === $contact->phone_number ? 'client' : 'system',
                'receiver_type' => $messageData['from'] === $contact->phone_number ? 'system' : 'client',
                'content' => $content,
                'type' => $messageData['type'],
                'status' => 'received',
                'metadata' => [
                    'timestamp' => $messageData['timestamp'] ?? null,
                    'raw_message' => $messageData
                ]
            ]);

            Log::info('✅ Mensaje guardado correctamente', [
                'id' => $messageId,
                'contacto' => substr($contact->phone_number, 0, 4) . '****' . substr($contact->phone_number, -4),
                'nombre' => $contact->name
            ]);

            return $whatsappMessage;
        } catch (\Exception $e) {
            Log::error('❌ Error guardando mensaje', [
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'mensaje' => $messageData
            ]);
            return null;
        }
    }

    private function getRemainingProducts()
    {

        try {
            $menu = WhatsappMenu::where('action_id', 'prices_menu')->first();

            if (!$menu) {
                return [
                    'type' => 'text',
                    'text' => ['body' => 'Lo siento, no hay más productos disponibles.']
                ];
            }

            $menuItems = $menu->items()->where('is_active', true)->orderBy('order')->get();

            if ($menuItems->isEmpty()) {
                return [
                    'type' => 'text',
                    'text' => ['body' => 'Lo siento, no hay más productos disponibles.']
                ];
            }

            $message = "📋 *Lista Completa de Productos*\n\n";
            $products = [];
            $number = 1;

            foreach ($menuItems as $item) {
                $prices = $item->prices()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();

                if ($prices->isEmpty()) {
                    continue;
                }
                //COLOQUEMOS EN MAYUSCULA EL TITULO
                $message .= "━━━━━━━━━━━━━━━━━━━━━\n";
                $message .= "          *" . strtoupper($item->title) . "*\n";
                $message .= "━━━━━━━━━━━━━━━━━━━━━\n\n";
                foreach ($prices as $price) {
                    $priceText = $price->is_promo
                        ? "💰 $" . number_format($price->promo_price, 2) . " (Oferta)"
                        : "💰 $" . number_format($price->price, 2);

                    // Incluir SKU en la lista
                    $message .= "*[SKU: {$price->sku}] {$price->name}*\n";
                   /*  if ($price->description) {
                        $message .= "   " . Str::limit($price->description, 50, '...') . "\n";
                    } */
                    $message .= "   {$priceText}\n\n";

                    // Guardar el producto en el array con su número y SKU
                    $products[$number] = [
                        'price' => $price,
                        'sku' => $price->sku
                    ];
                    $number++;
                }
            }

            if (empty($products)) {
                return [
                    'type' => 'text',
                    'text' => ['body' => 'Lo siento, no hay productos disponibles en este momento.']
                ];
            }

            // Guardar la lista de productos en el último mensaje para referencia
            if ($this->lastMessage) {
                $metadata = $this->lastMessage->metadata ?? [];
                $metadata['product_list'] = $products;
                $this->lastMessage->metadata = $metadata;
                $this->lastMessage->save();
            }

            $message .= "Para seleccionar un producto, puedes:\n";

            $message .= "Escribir el SKU del producto (ej: {$products[1]['sku']})";

            return [
                'type' => 'text',
                'text' => ['body' => $message]
            ];
        } catch (\Exception $e) {
            Log::error('❌ Error al obtener precios restantes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'type' => 'text',
                'text' => ['body' => 'Lo siento, ha ocurrido un error al cargar los productos.']
            ];
        }
    }

    private function getProductDetails($productId)
    {
        try {
            $price = WhatsappPrice::find($productId);
            if (!$price) {
                Log::warning('❌ Producto no encontrado', ['id' => $productId]);
                return null;
            }

            $message = "*{$price->name}*\n";
            $message .= "📦 Código: {$price->sku}\n\n";
            $message .= "{$price->description}\n\n";

            // Agregar características del producto
            if ($price->characteristics) {
                $characteristics = is_string($price->characteristics)
                    ? json_decode($price->characteristics, true)
                    : $price->characteristics;

                if (is_array($characteristics) && !empty($characteristics)) {
                    $message .= "✨ *Características:*\n";
                    foreach ($characteristics as $characteristic) {
                        $message .= "• {$characteristic}\n";
                    }
                    $message .= "\n";
                }
            }

            if ($price->is_promo && $price->promo_price) {
                $message .= "💰 *Precio Promocional:* \${$price->promo_price}\n";
                if ($price->promo_end_date) {
                    $message .= "📅 Válido hasta: " . date('d/m/Y', strtotime($price->promo_end_date)) . "\n\n";
                }
                $message .= "~~Precio Regular: \${$price->price}~~";
            } else {
                $message .= "💰 *Precio:* \${$price->price}";
            }

            // Obtener los botones activos para este perfil y producto
            $buttons = WhatsappButton::getActiveButtonsForProfile($this->businessProfile->id, $productId);

            // Si no hay botones, agregar los botones por defecto
            if (empty($buttons)) {
                $buttons = [
                    [
                        'type' => 'reply',
                        'reply' => [
                            'id' => 'comprar_' . $productId,
                            'title' => '🛒 Comprar'
                        ]
                    ],
                    [
                        'type' => 'reply',
                        'reply' => [
                            'id' => 'volver_productos',
                            'title' => '🔙 Volver a productos'
                        ]
                    ],
                        [
                            'type' => 'reply',
                            'reply' => [
                                'id' => 'menu_principal',
                                'title' => '🔙 Volver al menú principal'
                            ]
                        ]
                ];
            }
            //validar que los titulos no sean mas de 20 caracteres
            foreach ($buttons as $button) {
                if (strlen($button['reply']['title']) > 20) {
                    $button['reply']['title'] = substr($button['reply']['title'], 0, 17) . '...';
                }
            }

            //validar que los botones no sean mas de 3
            if (count($buttons) > 3) {
                $buttons = array_slice($buttons, 0, 3);
            }


            $interactive = [
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'button',
                    'body' => [
                        'text' => $message
                    ],
                    'action' => [
                        'buttons' => $buttons
                    ]
                ]
            ];

            // Si hay una imagen, agregarla como header
            if ($price->image) {
                $interactive['interactive']['header'] = [
                    'type' => 'image',
                    'image' => [
                        'link' => $price->image
                    ]
                ];
            }

            return $interactive;
        } catch (\Exception $e) {
            Log::error('Error al obtener detalles del producto', [
                'error' => $e->getMessage(),
                'product_id' => $productId
            ]);
            return null;
        }
    }

    // Agregar nuevo método para mostrar la selección de cantidad
    private function showQuantitySelection($productId)
    {
        try {
            $price = WhatsappPrice::find($productId);

            if (!$price) {
                return null;
            }

            if ($price->allow_quantity_selection==0 || $price->allow_quantity_selection==null || $price->allow_quantity_selection==false) {
                //pasar de una vez a Producto agregado al carrito con una unidad
                return 1;
            }

            return [
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'list',
                    'body' => [
                        'text' => "Selecciona la cantidad que deseas agregar al carrito:"
                    ],
                    'action' => [
                        'button' => 'Seleccionar cantidad',
                        'sections' => [
                            [
                                'title' => 'Cantidad',
                                'rows' => [
                                    [
                                        'id' => 'cantidad_1_' . $productId,
                                        'title' => '1 unidad'
                                    ],
                                    [
                                        'id' => 'cantidad_2_' . $productId,
                                        'title' => '2 unidades'
                                    ],
                                    [
                                        'id' => 'cantidad_3_' . $productId,
                                        'title' => '3 unidades'
                                    ],
                                    [
                                        'id' => 'cantidad_4_' . $productId,
                                        'title' => '4 unidades'
                                    ],
                                    [
                                        'id' => 'cantidad_5_' . $productId,
                                        'title' => '5 unidades'
                                    ],
                                    [
                                        'id' => 'cantidad_6_' . $productId,
                                        'title' => '6 unidades'
                                    ],
                                    [
                                        'id' => 'cantidad_7_' . $productId,
                                        'title' => '7 unidades'
                                    ],
                                    [
                                        'id' => 'cantidad_8_' . $productId,
                                        'title' => '8 unidades'
                                    ],
                                    [
                                        'id' => 'volver_productos',
                                        'title' => 'Volver a productos'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('❌ Error al mostrar selección de cantidad', [
                'error' => $e->getMessage(),
                'product_id' => $productId
            ]);
            return null;
        }
    }

    private function getProductsMenu()
    {
        try {
            $menu = WhatsappMenu::where('action_id', 'prices_menu')->first();
            if (!$menu) {
                Log::warning('⚠️ Menú de precios no encontrado', ['action_id' => 'prices_menu']);
                return [
                    'type' => 'text',
                    'text' => ['body' => 'Lo siento, el catálogo de productos no está disponible en este momento. Por favor, intenta más tarde.']
                ];
            }

            $menuItems = $menu->items()->where('is_active', true)->orderBy('order')->get();
            if ($menuItems->isEmpty()) {
                Log::warning('⚠️ No hay categorías de precios disponibles', [
                    'menu_id' => $menu->id,
                    'menu_title' => $menu->title
                ]);
                return [
                    'type' => 'text',
                    'text' => ['body' => 'Lo siento, no hay productos disponibles en este momento. Por favor, intenta más tarde.']
                ];
            }

            $sections = [];
            $totalRows = 0;
            $maxRows = 8; // Máximo 8 productos por sección para cumplir con el límite de 10 elementos

            foreach ($menuItems as $menuItem) {
                $prices = $menuItem->prices()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();

                if ($prices->isEmpty()) {
                    continue;
                }

                $rows = [];
                foreach ($prices as $price) {
                    // Asegurar que el título no exceda 24 caracteres
                    $title = Str::limit("[{$price->sku}] " . $price->name, 24, '');

                    // Preparar el texto del precio
                    $priceText = $price->is_promo
                        ? "💰 $" . number_format($price->promo_price, 2) . " (Oferta)"
                        : "💰 $" . number_format($price->price, 2);

                    // Calcular el espacio disponible para la descripción
                    $priceTextLength = mb_strlen($priceText);
                    $separatorLength = 3; // " - "
                    $maxDescLength = 72 - $priceTextLength - $separatorLength;

                    // Asegurar que la descripción no exceda el espacio disponible
                    $description = Str::limit($price->description, $maxDescLength, '...');

                    // Combinar precio y descripción
                    $fullDescription = $priceText . " - " . $description;

                    // Verificación final de longitud
                    if (mb_strlen($fullDescription) > 72) {
                        // Si aún excede, truncar más la descripción
                        $excess = mb_strlen($fullDescription) - 72;
                        $description = Str::limit($description, $maxDescLength - $excess, '...');
                        $fullDescription = $priceText . " - " . $description;
                    }

                    $rows[] = [
                        'id' => $price->id,
                        'title' => $title,
                        'description' => $fullDescription
                    ];

                    $totalRows++;
                    if ($totalRows >= $maxRows) {
                        break;
                    }
                }

                if (!empty($rows)) {
                    $sections[] = [
                        'title' => Str::limit($menuItem->title, 24, ''),
                        'rows' => $rows
                    ];
                }

                if ($totalRows >= $maxRows) {
                    break;
                }
            }

            // Agregar sección de navegación
                $sections[] = [
                'title' => 'Navegación',
                    'rows' => [
                        [
                            'id' => 'ver_mas_precios',
                        'title' => 'Ver más productos',
                        'description' => 'Ver todos los productos disponibles'
                    ],
                    [
                        'id' => 'menu_principal',
                        'title' => 'Volver al menú principal',
                        'description' => 'Regresar al menú de inicio'
                    ]
                ]
            ];

            return [
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'list',
                    'body' => [
                        'text' => "🛍️ *Catálogo de Productos*\n\n" .
                            "Por favor, selecciona un producto para ver más detalles.\n" .
                            "También puedes escribir el código del producto (ej: 1001) para verlo directamente."
                    ],
                    'action' => [
                        'button' => 'Ver Productos',
                        'sections' => $sections
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('❌ Error al generar el menú de precios', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'type' => 'text',
                'text' => [
                    'body' => 'Lo siento, ha ocurrido un error al cargar los productos. Por favor, intenta nuevamente más tarde.'
                ]
            ];
        }
    }

    private function sendMessage($to, $message)
    {
        try {
            // Validar y ajustar la longitud del texto del mensaje interactivo
            if (isset($message['interactive'])) {
                // Validar texto del cuerpo (máximo 1024 caracteres)
                if (isset($message['interactive']['body']['text'])) {
                    $text = $message['interactive']['body']['text'];
                    if (mb_strlen($text) > 1024) {
                        $message['interactive']['body']['text'] = mb_substr($text, 0, 1021) . '...';
                        Log::warning('⚠️ Texto del cuerpo truncado', [
                            'longitud_original' => mb_strlen($text),
                            'longitud_final' => mb_strlen($message['interactive']['body']['text'])
                        ]);
                    }
                }

                // Validar títulos de botones (máximo 20 caracteres)
                if (isset($message['interactive']['action']['buttons'])) {
                    foreach ($message['interactive']['action']['buttons'] as &$button) {
                        if (isset($button['reply']['title'])) {
                            $title = $button['reply']['title'];
                            if (mb_strlen($title) > 20) {
                                $button['reply']['title'] = mb_substr($title, 0, 17) . '...';
                                Log::warning('⚠️ Título de botón truncado', [
                                    'título_original' => $title,
                                    'título_final' => $button['reply']['title']
                                ]);
                            }
                        }
                    }
                }

                // Validar elementos de lista
                if (isset($message['interactive']['action']['sections'])) {
                    foreach ($message['interactive']['action']['sections'] as &$section) {
                        // Validar título de sección (máximo 24 caracteres)
                        if (isset($section['title']) && mb_strlen($section['title']) > 24) {
                            $section['title'] = mb_substr($section['title'], 0, 21) . '...';
                            Log::warning('⚠️ Título de sección truncado', [
                                'título_original' => $section['title'],
                                'título_final' => $section['title']
                            ]);
                        }

                        // Validar filas de la sección
                        if (isset($section['rows'])) {
                            foreach ($section['rows'] as &$row) {
                                // Validar título de fila (máximo 24 caracteres)
                                if (isset($row['title']) && mb_strlen($row['title']) > 24) {
                                    $row['title'] = mb_substr($row['title'], 0, 21) . '...';
                                    Log::warning('⚠️ Título de fila truncado', [
                                        'título_original' => $row['title'],
                                        'título_final' => $row['title']
                                    ]);
                                }

                                // Validar descripción de fila (máximo 72 caracteres)
                                if (isset($row['description']) && mb_strlen($row['description']) > 72) {
                                    $row['description'] = mb_substr($row['description'], 0, 69) . '...';
                                    Log::warning('⚠️ Descripción de fila truncada', [
                                        'descripción_original' => $row['description'],
                                        'descripción_final' => $row['description']
                                    ]);
                                }
                            }
                        }
                    }
                }

                // Validar texto del botón de acción (máximo 20 caracteres)
                if (isset($message['interactive']['action']['button'])) {
                    $buttonText = $message['interactive']['action']['button'];
                    if (mb_strlen($buttonText) > 20) {
                        $message['interactive']['action']['button'] = mb_substr($buttonText, 0, 17) . '...';
                        Log::warning('⚠️ Texto del botón de acción truncado', [
                            'texto_original' => $buttonText,
                            'texto_final' => $message['interactive']['action']['button']
                        ]);
                    }
                }
            }

            $result = $this->sendMessageToWhatsApp($to, $message);
            if (!$result) {
                Log::error('❌ Error al enviar mensaje', [
                    'to' => $to,
                    'message' => $message
                ]);
                return false;
            }

            // Obtener el contacto
            $contact = WhatsappContact::where('phone_number', $to)->first();
            if (!$contact) {
                Log::error('❌ Contacto no encontrado al guardar mensaje del sistema', ['phone' => $to]);
                return false;
            }

            // Determinar el contenido del mensaje según el tipo
            $content = '';
            if ($message['type'] === 'text') {
                $content = $message['text']['body'] ?? '';
            } elseif ($message['type'] === 'interactive') {
                if (isset($message['interactive']['body']['text'])) {
                    $content = $message['interactive']['body']['text'];
                } elseif (isset($message['interactive']['header']['text'])) {
                    $content = $message['interactive']['header']['text'];
                }
            }

            // Guardar el mensaje del sistema
            WhatsappMessage::create([
                'contact_id' => $contact->id,
                'business_profile_id' => $this->businessProfile->id,
                'message_id' => $result['message_id'],
                'sender_type' => 'system',
                'receiver_type' => 'client',
                'content' => $content,
                'type' => $message['type'],
                'status' => 'sent',
                'metadata' => [
                    'raw_message' => $message,
                    'timestamp' => now()
                ]
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('❌ Error al enviar mensaje', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    private function getOrderMenu()
    {
        try {
            // Obtener el contacto del último mensaje
            if (!$this->lastMessage || !$this->lastMessage->contact) {
                return [
                    'type' => 'text',
                    'text' => ['body' => 'Lo siento, no se pudo identificar tu contacto. Por favor, envía un mensaje primero.']
                ];
            }

            $contact = $this->lastMessage->contact;

            // Obtener todos los pedidos del usuario
            $orders = WhatsappCart::where('contact_id', $contact->id)
                ->whereIn('status', [
                    WhatsappCart::STATUS_PENDING,
                    WhatsappCart::STATUS_CONFIRMED,
                    WhatsappCart::STATUS_PAYMENT_PENDING,
                    WhatsappCart::STATUS_PAID,
                    WhatsappCart::STATUS_COMPLETED
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            if ($orders->isEmpty()) {
                return [
                    'type' => 'interactive',
                    'interactive' => [
                        'type' => 'button',
                        'body' => [
                            'text' => "📦 *Historial de Pedidos*\n\n" .
                                "No tienes pedidos realizados aún.\n\n" .
                                "¿Te gustaría ver nuestros productos?"
                        ],
                        'action' => [
                            'buttons' => [
                                [
                                    'type' => 'reply',
                                    'reply' => [
                                        'id' => 'productos',
                                        'title' => '🛍️ Ver productos'
                                    ]
                                ],
                                [
                                    'type' => 'reply',
                                    'reply' => [
                                        'id' => 'menu_principal',
                                        'title' => '🏠 Menú principal'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
            }

            // Agrupar pedidos por estado
            $pendingOrders = $orders->where('status', WhatsappCart::STATUS_PENDING);
            $confirmedOrders = $orders->where('status', WhatsappCart::STATUS_CONFIRMED);
            $paymentPendingOrders = $orders->where('status', WhatsappCart::STATUS_PAYMENT_PENDING);
            $completedOrders = $orders->whereIn('status', [WhatsappCart::STATUS_PAID, WhatsappCart::STATUS_COMPLETED]);

            $message = "📦 *Tus Pedidos*\n\n";

            // Mostrar pedidos pendientes de confirmación
            if ($pendingOrders->isNotEmpty()) {
                $message .= "⏳ *Pedidos Pendientes de Confirmación*\n";
                foreach ($pendingOrders as $order) {
                    $orderDetails = $order->metadata['order_details'] ?? null;
                    if ($orderDetails) {
                        $message .= "🛒 *{$orderDetails['order_number']}*\n";
                        $message .= "📅 Fecha: " . date('d/m/Y H:i', strtotime($orderDetails['created_at'])) . "\n";
                        $message .= "💰 Total: \${$orderDetails['total']}\n";
                        $message .= "💳 Método de pago: " . $this->getPaymentMethodText($orderDetails['payment_method']) . "\n";
                $message .= "📋 Items:\n";
                        foreach ($orderDetails['items'] as $item) {
                            $message .= "  • {$item['name']} x{$item['quantity']}\n";
                        }
                        if (!empty($orderDetails['note']) && $orderDetails['note'] !== 'sin nota') {
                            $message .= "📝 Nota: {$orderDetails['note']}\n";
                        }
                        $message .= "\n";
                    }
                }
            }

            // Mostrar pedidos confirmados
            if ($confirmedOrders->isNotEmpty()) {
                $message .= "✅ *Pedidos Confirmados*\n";
                foreach ($confirmedOrders as $order) {
                    $orderDetails = $order->metadata['order_details'] ?? null;
                    if ($orderDetails) {
                        $message .= "🛒 *{$orderDetails['order_number']}*\n";
                        $message .= "📅 Fecha: " . date('d/m/Y H:i', strtotime($orderDetails['created_at'])) . "\n";
                        $message .= "💰 Total: \${$orderDetails['total']}\n";
                        $message .= "💳 Método de pago: " . $this->getPaymentMethodText($orderDetails['payment_method']) . "\n";
                        $message .= "📋 Items:\n";
                        foreach ($orderDetails['items'] as $item) {
                            $message .= "  • {$item['name']} x{$item['quantity']}\n";
                        }
                        if (!empty($orderDetails['note']) && $orderDetails['note'] !== 'sin nota') {
                            $message .= "📝 Nota: {$orderDetails['note']}\n";
                }
                $message .= "\n";
                    }
                }
            }

            // Mostrar pedidos pendientes de pago
            if ($paymentPendingOrders->isNotEmpty()) {
                $message .= "💳 *Pedidos Pendientes de Pago*\n";
                foreach ($paymentPendingOrders as $order) {
                    $orderDetails = $order->metadata['order_details'] ?? null;
                    if ($orderDetails) {
                        $message .= "🛒 *{$orderDetails['order_number']}*\n";
                        $message .= "📅 Fecha: " . date('d/m/Y H:i', strtotime($orderDetails['created_at'])) . "\n";
                        $message .= "💰 Total: \${$orderDetails['total']}\n";
                        $message .= "💳 Método de pago: " . $this->getPaymentMethodText($orderDetails['payment_method']) . "\n";
                        $message .= "📋 Items:\n";
                        foreach ($orderDetails['items'] as $item) {
                            $message .= "  • {$item['name']} x{$item['quantity']}\n";
                        }
                        if (!empty($orderDetails['note']) && $orderDetails['note'] !== 'sin nota') {
                            $message .= "📝 Nota: {$orderDetails['note']}\n";
                        }
                        $message .= "\n";
                    }
                }
            }

            // Mostrar pedidos completados
            if ($completedOrders->isNotEmpty()) {
                $message .= "✨ *Pedidos Completados*\n";
                foreach ($completedOrders as $order) {
                    $orderDetails = $order->metadata['order_details'] ?? null;
                    if ($orderDetails) {
                        $message .= "🛒 *{$orderDetails['order_number']}*\n";
                        $message .= "📅 Fecha: " . date('d/m/Y H:i', strtotime($orderDetails['created_at'])) . "\n";
                        $message .= "💰 Total: \${$orderDetails['total']}\n";
                        $message .= "💳 Método de pago: " . $this->getPaymentMethodText($orderDetails['payment_method']) . "\n";
                        $message .= "📋 Items:\n";
                        foreach ($orderDetails['items'] as $item) {
                            $message .= "  • {$item['name']} x{$item['quantity']}\n";
                        }
                        if (!empty($orderDetails['note']) && $orderDetails['note'] !== 'sin nota') {
                            $message .= "📝 Nota: {$orderDetails['note']}\n";
                        }
                        $message .= "\n";
                    }
                }
            }

            $message .= "¿Qué deseas hacer?";

            // Preparar botones según el estado de los pedidos
            $buttons = [];

            if ($pendingOrders->isNotEmpty()) {
                $buttons[] = [
                    'type' => 'reply',
                    'reply' => [
                        'id' => 'confirmar_pedidos_pendientes',
                        'title' => '✅ Confirmar pedidos pendientes'
                    ]
                ];
            }

            if ($paymentPendingOrders->isNotEmpty()) {
                $buttons[] = [
                    'type' => 'reply',
                    'reply' => [
                        'id' => 'ver_instrucciones_pago',
                        'title' => '💳 Ver instrucciones de pago'
                    ]
                ];
            }

            $buttons[] = [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'productos',
                                    'title' => '🛍️ Ver productos'
                                ]
            ];

            $buttons[] = [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'menu_principal',
                                    'title' => '🏠 Menú principal'
                                ]
            ];

            return [
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'button',
                    'body' => [
                        'text' => $message
                    ],
                    'action' => [
                        'buttons' => $buttons
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('❌ Error al obtener menú de pedidos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'type' => 'text',
                'text' => ['body' => 'Lo siento, ha ocurrido un error al cargar el historial de pedidos.']
            ];
        }
    }

    private function getInfoMenu()
    {
        try {
            $menu = WhatsappMenu::where('action_id', 'info_menu')->first();
            if (!$menu) {
                return [
                    'type' => 'text',
                    'text' => ['body' => 'Lo siento, el menú de información no está disponible en este momento.']
                ];
            }

            // Obtener el menú principal para el botón de retorno
            $mainMenu = WhatsappMenu::where('action_id', 'main_menu')->first();
            $mainMenuButton = $mainMenu ? $mainMenu->button_text : 'Volver al Menú';

            // Obtener la respuesta de soporte
            $soporteResponse = WhatsappChatbotResponse::where('keyword', 'soporte')
                ->where('is_active', true)
                ->first();

            // Preparar las secciones del menú
            $sections = $menu->metadata['sections'] ?? [];

            // Agregar la sección de soporte si existe la respuesta
            if ($soporteResponse) {
                $sections[] = [
                    'title' => 'Soporte',
                    'rows' => [
                        [
                            'id' => 'soporte',
                            'title' => '🛟 Soporte Técnico',
                            'description' => 'Contacta con nuestro equipo de soporte'
                        ]
                    ]
                ];
            }

                            // Agregar el botón de retorno al menú en cada sección
            $sections = array_map(function($section) use ($mainMenuButton) {
                            if (isset($section['rows'])) {
                                $section['rows'][] = [
                                    'id' => 'return_to_menu',
                                    'title' => $mainMenuButton,
                                    'description' => 'Volver al menú principal'
                                ];
                            }
                            return $section;
            }, $sections);

            return [
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'list',
                    'body' => [
                        'text' => $menu->content
                    ],
                    'action' => [
                        'button' => $menu->button_text,
                        'sections' => $sections
                    ],
                    'footer' => [
                        'text' => 'Selecciona una opción o vuelve al menú principal'
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('❌ Error al obtener menú de información', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'type' => 'text',
                'text' => ['body' => 'Lo siento, ha ocurrido un error al cargar el menú de información.']
            ];
        }
    }

    private function isProcessActive(WhatsappContact $contact): bool
    {
        // Verificar si hay un carrito activo con nota pendiente
        $cart = WhatsappCart::where('contact_id', $contact->id)
            ->where('status', 'active')
            ->first();

        if ($cart && isset($cart->metadata['pending_note']) && $cart->metadata['pending_note']) {
                return true;
        }

        // Verificar si hay un mensaje de imagen pendiente de nota
        $lastImageMessage = WhatsappMessage::where('contact_id', $contact->id)
            ->where('type', 'image')
            ->where('metadata->pending_note', true)
            ->latest()
            ->first();

        if ($lastImageMessage) {
            return true;
        }

        // Verificar si hay un mensaje pendiente de cantidad
        $lastMessage = WhatsappMessage::where('contact_id', $contact->id)
            ->whereNotNull('metadata->pending_quantity')
            ->latest()
            ->first();

        if ($lastMessage) {
            return true;
        }

        return false;
    }

    private function confirmarPedido(WhatsappContact $contact, $cartId)
    {
        try {
            $cart = WhatsappCart::where('id', $cartId)
                ->where('contact_id', $contact->id)
                ->first();

            if (!$cart) {
                return [
                    'type' => 'text',
                    'text' => ['body' => 'Lo siento, no se encontró el pedido.']
                ];
            }

            $cart->status = WhatsappCart::STATUS_CONFIRMED;
            $cart->save();

            return $this->finalizarCompra($contact);
        } catch (\Exception $e) {
            Log::error('Error al confirmar pedido', [
                'error' => $e->getMessage(),
                'cart_id' => $cartId
            ]);
            return [
                'type' => 'text',
                'text' => ['body' => 'Lo siento, ha ocurrido un error al confirmar tu pedido.']
            ];
        }
    }

    private function cancelarPedido(WhatsappContact $contact, $cartId)
    {
        try {
            $cart = WhatsappCart::where('id', $cartId)
                ->where('contact_id', $contact->id)
                ->first();

            if (!$cart) {
                return [
                    'type' => 'text',
                    'text' => ['body' => 'Lo siento, no se encontró el pedido.']
                ];
            }

            $cart->status = WhatsappCart::STATUS_CANCELLED;
            $cart->save();

            return [
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'button',
                    'body' => [
                        'text' => "❌ *Pedido cancelado*\n\n" .
                            "Tu pedido ha sido cancelado.\n\n" .
                            "¿Qué deseas hacer?"
                    ],
                    'action' => [
                        'buttons' => [
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'menu_productos',
                                    'title' => '🛍️ Ver productos'
                                ]
                            ],
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'menu_principal',
                                    'title' => '🏠 Menú principal'
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error al cancelar pedido', [
                'error' => $e->getMessage(),
                'cart_id' => $cartId
            ]);
            return [
                'type' => 'text',
                'text' => ['body' => 'Lo siento, ha ocurrido un error al cancelar tu pedido.']
            ];
        }
    }

    private function procesarPagoTransferencia(WhatsappContact $contact, $cartId)
    {
        try {
            $cart = WhatsappCart::where('id', $cartId)
                ->where('contact_id', $contact->id)
                ->first();

            if (!$cart) {
                return [
                    'type' => 'text',
                    'text' => ['body' => 'Lo siento, no se encontró el pedido.']
                ];
            }

            // Actualizar el método de pago y estado
            $cart->payment_method = 'transferencia';
            $cart->status = WhatsappCart::STATUS_PAYMENT_PENDING;
            $cart->save();

            // Preparar los detalles del pedido para guardar en metadata
            $orderDetails = [
                'order_number' => 'ORD-' . str_pad($cart->id, 6, '0', STR_PAD_LEFT),
                'items' => [],
                'total' => $cart->total,
                'note' => $cart->note,
                'created_at' => $cart->created_at->format('Y-m-d H:i:s'),
                'status' => $cart->status,
                'payment_method' => $cart->payment_method,
                'payment_status' => $cart->payment_status
            ];

            foreach ($cart->items as $item) {
                $orderDetails['items'][] = [
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->price * $item->quantity
                ];
            }

            // Guardar los detalles del pedido en metadata
            $metadata = $cart->metadata ?? [];
            $metadata['order_details'] = $orderDetails;
            $cart->metadata = $metadata;
            $cart->save();

            // Preparar resumen del pedido
            $message = "📋 *Resumen de tu Pedido*\n\n";
            $message .= "📦 *Número de Pedido:* {$orderDetails['order_number']}\n\n";
            $message .= "📋 *Detalles del Pedido:*\n";

            foreach ($orderDetails['items'] as $item) {
                $message .= "• {$item['name']}\n";
                $message .= "  Cantidad: {$item['quantity']}\n";
                $message .= "  Precio: \${$item['price']}\n";
                $message .= "  Subtotal: \${$item['subtotal']}\n\n";
            }

            $message .= "💰 *Total:* \${$orderDetails['total']}\n\n";
            $message .= "💳 *Método de pago:* Transferencia bancaria\n\n";

            if ($cart->note && $cart->note !== 'sin nota') {
                $message .= "📝 *Nota del pedido:*\n{$cart->note}\n\n";
            }

            $message .= "¿Confirmas tu pedido?";

            return [
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'button',
                    'body' => [
                        'text' => $message
                    ],
                    'action' => [
                        'buttons' => [
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'confirmar_pedido_' . $cart->id,
                                    'title' => '✅ Confirmar pedido'
                                ]
                            ],
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'cancelar_pedido_' . $cart->id,
                                    'title' => '❌ Cancelar pedido'
                                ]
                            ]
                        ]
                    ]
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error al procesar pago por transferencia', [
                'error' => $e->getMessage(),
                'cart_id' => $cartId
            ]);
            return [
                'type' => 'text',
                'text' => ['body' => 'Lo siento, ha ocurrido un error al procesar el pago.']
            ];
        }
    }

    private function procesarPagoEfectivo(WhatsappContact $contact, $cartId)
    {
        try {
            $cart = WhatsappCart::where('id', $cartId)
                ->where('contact_id', $contact->id)
                ->first();

            if (!$cart) {
                return [
                    'type' => 'text',
                    'text' => ['body' => 'Lo siento, no se encontró el pedido.']
                ];
            }

            // Actualizar el método de pago y estado
            $cart->payment_method = 'efectivo';
            $cart->status = WhatsappCart::STATUS_PAYMENT_PENDING;
            $cart->save();

            // Preparar los detalles del pedido para guardar en metadata
            $orderDetails = [
                'order_number' => 'ORD-' . str_pad($cart->id, 6, '0', STR_PAD_LEFT),
                'items' => [],
                'total' => $cart->total,
                'note' => $cart->note,
                'created_at' => $cart->created_at->format('Y-m-d H:i:s'),
                'status' => $cart->status,
                'payment_method' => $cart->payment_method,
                'payment_status' => $cart->payment_status
            ];

            foreach ($cart->items as $item) {
                $orderDetails['items'][] = [
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->price * $item->quantity
                ];
            }

            // Guardar los detalles del pedido en metadata
            $metadata = $cart->metadata ?? [];
            $metadata['order_details'] = $orderDetails;
            $cart->metadata = $metadata;
            $cart->save();

            // Preparar resumen del pedido
            $message = "📋 *Resumen de tu Pedido*\n\n";
            $message .= "📦 *Número de Pedido:* {$orderDetails['order_number']}\n\n";
            $message .= "📋 *Detalles del Pedido:*\n";

            foreach ($orderDetails['items'] as $item) {
                $message .= "• {$item['name']}\n";
                $message .= "  Cantidad: {$item['quantity']}\n";
                $message .= "  Precio: \${$item['price']}\n";
                $message .= "  Subtotal: \${$item['subtotal']}\n\n";
            }

            $message .= "💰 *Total:* \${$orderDetails['total']}\n\n";
            $message .= "💳 *Método de pago:* Pago en efectivo\n\n";

            if ($cart->note && $cart->note !== 'sin nota') {
                $message .= "📝 *Nota del pedido:*\n{$cart->note}\n\n";
            }

            $message .= "¿Confirmas tu pedido?";

            return [
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'button',
                    'body' => [
                        'text' => $message
                    ],
                    'action' => [
                        'buttons' => [
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'confirmar_pedido_' . $cart->id,
                                    'title' => '✅ Confirmar pedido'
                                ]
                            ],
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'cancelar_pedido_' . $cart->id,
                                    'title' => '❌ Cancelar pedido'
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error al procesar pago en efectivo', [
                'error' => $e->getMessage(),
                'cart_id' => $cartId
            ]);
            return [
                'type' => 'text',
                'text' => ['body' => 'Lo siento, ha ocurrido un error al procesar el pago en efectivo.']
            ];
        }
    }

    private function procesarPagoTarjeta(WhatsappContact $contact, $cartId)
    {
        try {
            $cart = WhatsappCart::where('id', $cartId)
                ->where('contact_id', $contact->id)
                ->first();

            if (!$cart) {
                return [
                    'type' => 'text',
                    'text' => ['body' => 'Lo siento, no se encontró el pedido.']
                ];
            }

            // Actualizar el método de pago y estado
            $cart->payment_method = 'tarjeta';
            $cart->status = WhatsappCart::STATUS_PAYMENT_PENDING;
            $cart->save();

            // Preparar los detalles del pedido para guardar en metadata
            $orderDetails = [
                'order_number' => 'ORD-' . str_pad($cart->id, 6, '0', STR_PAD_LEFT),
                'items' => [],
                'total' => $cart->total,
                'note' => $cart->note,
                'created_at' => $cart->created_at->format('Y-m-d H:i:s'),
                'status' => $cart->status,
                'payment_method' => $cart->payment_method,
                'payment_status' => $cart->payment_status
            ];

            foreach ($cart->items as $item) {
                $orderDetails['items'][] = [
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->price * $item->quantity
                ];
            }

            // Guardar los detalles del pedido en metadata
            $metadata = $cart->metadata ?? [];
            $metadata['order_details'] = $orderDetails;
            $cart->metadata = $metadata;
            $cart->save();

            // Preparar resumen del pedido
            $message = "📋 *Resumen de tu Pedido*\n\n";
            $message .= "📦 *Número de Pedido:* {$orderDetails['order_number']}\n\n";
            $message .= "📋 *Detalles del Pedido:*\n";

            foreach ($orderDetails['items'] as $item) {
                $message .= "• {$item['name']}\n";
                $message .= "  Cantidad: {$item['quantity']}\n";
                $message .= "  Precio: \${$item['price']}\n";
                $message .= "  Subtotal: \${$item['subtotal']}\n\n";
            }

            $message .= "💰 *Total:* \${$orderDetails['total']}\n\n";
            $message .= "💳 *Método de pago:* Pago con tarjeta\n\n";

            if ($cart->note && $cart->note !== 'sin nota') {
                $message .= "📝 *Nota del pedido:*\n{$cart->note}\n\n";
            }

            $message .= "¿Confirmas tu pedido?";

            return [
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'button',
                    'body' => [
                        'text' => $message
                    ],
                    'action' => [
                        'buttons' => [
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'confirmar_pedido_' . $cart->id,
                                    'title' => '✅ Confirmar pedido'
                                ]
                            ],
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'cancelar_pedido_' . $cart->id,
                                    'title' => '❌ Cancelar pedido'
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error al procesar pago con tarjeta', [
                'error' => $e->getMessage(),
                'cart_id' => $cartId
            ]);
            return [
                'type' => 'text',
                'text' => ['body' => 'Lo siento, ha ocurrido un error al procesar el pago con tarjeta.']
            ];
        }
    }

    /**
     * Obtiene el contacto desde la base de datos basándose en el keyword
     * Busca en whatsapp_contacts usando metadata->role o metadata->type
     */
    private function getContactFromDatabase($keyword): ?string
    {
        try {
            // Buscar contacto en la base de datos por rol/tipo en metadata
            $contact = WhatsappContact::where('business_profile_id', $this->businessProfile->id)
                ->where('status', 'active')
                ->where(function($query) use ($keyword) {
                    $query->whereJsonContains('metadata->role', $keyword)
                        ->orWhereJsonContains('metadata->type', $keyword)
                        ->orWhere('name', 'LIKE', '%' . $keyword . '%');
                })
                ->first();

            if ($contact) {
                // Obtener información del contacto
                $metadata = $contact->metadata ?? [];
                $name = $contact->name ?? 'Contacto';
                $phone = $contact->phone_number;

                // Extraer nombre completo (formatted_name|first_name|last_name)
                $nameParts = explode(' ', $name, 2);
                $firstName = $nameParts[0] ?? $name;
                $lastName = $nameParts[1] ?? '';

                // Construir el formato de contacto
                $formattedContact = sprintf(
                    "%s|%s|%s|%s|%s|%s|%s|%s",
                    $name,                                    // formatted_name
                    $firstName,                               // first_name
                    $lastName,                                // last_name
                    $phone,                                   // phone
                    $metadata['email'] ?? '',                 // email
                    $metadata['company'] ?? ($this->businessProfile->business_name ?? ''), // company
                    $metadata['department'] ?? '',            // department
                    $metadata['title'] ?? ''                  // title
                );

                Log::info('[getContactFromDatabase] ✅ Contacto encontrado en BD', [
                    'keyword' => $keyword,
                    'contact_id' => $contact->id,
                    'name' => $name,
                    'phone' => $phone
                ]);

                return $formattedContact;
            }

            Log::info('[getContactFromDatabase] ⚠️ No se encontró contacto en BD', [
                'keyword' => $keyword
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('[getContactFromDatabase] ❌ Error al buscar contacto', [
                'keyword' => $keyword,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    private function formatContacts($contacts)
    {
        Log::info('Formateando contactos', ['input' => $contacts]);

        // Obtener el número de WhatsApp Business para usar como wa_id
        // Esto hace que WhatsApp muestre "Escribir mensaje" en lugar de "Invitar"
        $businessPhoneNumber = $this->businessProfile ? $this->businessProfile->phone_number : null;

        // Si es un string con formato plano (usando | como separador)
        if (is_string($contacts) && strpos($contacts, '|') !== false) {
            Log::info('Detectado formato plano con separador |');
            $fields = explode('|', $contacts);

            if (count($fields) < 8) {
                Log::error('Formato de contacto inválido', [
                    'campos_esperados' => 8,
                    'campos_recibidos' => count($fields),
                    'campos' => $fields
                ]);
                return [];
            }

            // Usar el número del business profile como wa_id si está disponible
            // Esto asegura que WhatsApp muestre "Escribir mensaje" en lugar de "Invitar"
            $waId = $businessPhoneNumber ? preg_replace('/[^0-9]/', '', $businessPhoneNumber) : preg_replace('/[^0-9]/', '', $fields[3]);

            $contact = [
                'name' => [
                    'formatted_name' => $fields[0],
                    'first_name' => $fields[1],
                    'last_name' => $fields[2]
                ],
                'phones' => [
                    [
                        'phone' => $fields[3],
                        'type' => 'CELL',
                        'wa_id' => $waId  // Usar el número del business para que muestre "Escribir mensaje"
                    ]
                ],
                'emails' => [
                    [
                        'email' => $fields[4],
                        'type' => 'WORK'
                    ]
                ],
                'org' => [
                    'company' => $fields[5],
                    'department' => $fields[6],
                    'title' => $fields[7]
                ]
            ];

            Log::info('Contacto formateado exitosamente', [
                'contacto' => $contact,
                'wa_id_usado' => $waId,
                'business_phone' => $businessPhoneNumber
            ]);
            return [$contact];
        }

        // Si es un JSON string, decodificarlo
        if (is_string($contacts)) {
            Log::info('Intentando decodificar JSON string');
            $decoded = json_decode($contacts, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $contacts = $decoded;
                Log::info('JSON decodificado exitosamente');
            } else {
                Log::error('Error decodificando JSON', ['error' => json_last_error_msg()]);
                return [];
            }
        }

        // Si no es un array después de la decodificación, retornar array vacío
        if (!is_array($contacts)) {
            Log::error('Formato de contactos inválido después de procesamiento', [
                'tipo' => gettype($contacts)
            ]);
            return [];
        }

        // Obtener el número de WhatsApp Business para usar como wa_id
        $businessPhoneNumber = $this->businessProfile ? $this->businessProfile->phone_number : null;

        // Formatear los contactos según la API de WhatsApp
        $formattedContacts = [];
        foreach ($contacts as $contact) {
            $formattedContact = [
                'name' => [
                    'formatted_name' => $contact['name'] ?? '',
                    'first_name' => $contact['first_name'] ?? '',
                    'last_name' => $contact['last_name'] ?? ''
                ]
            ];

            // Agregar teléfono si existe
            if (!empty($contact['phone'])) {
                // Usar el número del business profile como wa_id si está disponible
                // Esto asegura que WhatsApp muestre "Escribir mensaje" en lugar de "Invitar"
                $waId = $businessPhoneNumber ? preg_replace('/[^0-9]/', '', $businessPhoneNumber) : preg_replace('/[^0-9]/', '', $contact['phone']);

                $formattedContact['phones'] = [
                    [
                        'phone' => $contact['phone'],
                        'type' => 'CELL',
                        'wa_id' => $waId  // Usar el número del business para que muestre "Escribir mensaje"
                    ]
                ];
            }

            // Agregar email si existe
            if (!empty($contact['email'])) {
                $formattedContact['emails'] = [
                    [
                        'email' => $contact['email'],
                        'type' => 'WORK'
                    ]
                ];
            }

            // Agregar organización si existe
            if (!empty($contact['company']) || !empty($contact['department']) || !empty($contact['title'])) {
                $formattedContact['org'] = [
                    'company' => $contact['company'] ?? '',
                    'department' => $contact['department'] ?? '',
                    'title' => $contact['title'] ?? ''
                ];
            }

            $formattedContacts[] = $formattedContact;
        }

        Log::info('Contactos formateados exitosamente', ['cantidad' => count($formattedContacts)]);
        return $formattedContacts;
    }

    /**
     * Convierte información de contacto a mensaje de texto formateado
     * Útil para evitar el botón "Invitar" de WhatsApp cuando se comparten contactos
     */
    private function formatContactAsText($contacts): string
    {
        // Parsear el contacto desde el string
        $formattedContacts = $this->formatContacts($contacts);

        if (empty($formattedContacts)) {
            return 'Información de contacto no disponible.';
        }

        $contact = $formattedContacts[0];
        $text = "📞 *Información de Contacto*\n\n";

        // Nombre
        if (!empty($contact['name']['formatted_name'])) {
            $text .= "👤 *Nombre:* " . $contact['name']['formatted_name'] . "\n";
        }

        // Teléfono
        if (!empty($contact['phones'][0]['phone'])) {
            $phone = $contact['phones'][0]['phone'];
            $text .= "📱 *Teléfono:* " . $phone . "\n";
            $text .= "💬 *Escribe directamente:* wa.me/" . preg_replace('/[^0-9]/', '', $phone) . "\n\n";
        }

        // Email
        if (!empty($contact['emails'][0]['email'])) {
            $text .= "📧 *Email:* " . $contact['emails'][0]['email'] . "\n";
        }

        // Organización
        if (!empty($contact['org'])) {
            if (!empty($contact['org']['company'])) {
                $text .= "🏢 *Empresa:* " . $contact['org']['company'] . "\n";
            }
            if (!empty($contact['org']['title'])) {
                $text .= "💼 *Cargo:* " . $contact['org']['title'] . "\n";
            }
            if (!empty($contact['org']['department'])) {
                $text .= "📋 *Departamento:* " . $contact['org']['department'] . "\n";
            }
        }

        $text .= "\n_Puedes escribir directamente a este número para contactar con soporte._";

        return $text;
    }

    /**
     * Helper para armar automáticamente el array de variables según la estructura de la plantilla y los datos del contacto.
     * Puedes extender la lógica para mapear más campos del contacto o de otros modelos.
     */
    public function buildTemplateVariables(WhatsappTemplate $template, WhatsappContact $contact, array $customValues = [])
    {
        $variables = [];

        // Si hay valores personalizados, usarlos directamente
        if (!empty($customValues)) {
            $variables = $customValues;
        }
        // Si no hay valores personalizados, usar valores por defecto
        else {
            foreach ($template->components as $component) {
                if (strtolower($component['type']) === 'header' &&
                    isset($component['format']) &&
                    strtolower($component['format']) === 'text') {
                    $variables[] = $contact->name ?? 'Cliente';
                }
            }
        }

        Log::info('Variables construidas:', [
            'template' => $template->name,
            'customValues' => $customValues,
            'finalVariables' => $variables
        ]);

        return $variables;
    }

    /**
     * Envía el catálogo de WhatsApp Business al cliente
     *
     * @param string $to Número de teléfono del destinatario
     * @return void
     */
    public function sendCatalog($to)
    {
        try {
            // Obtener el contacto
            $contact = WhatsappContact::where('phone_number', $to)->first();
            if (!$contact) {
                return false;
            }

            // Refrescar el contacto desde la base de datos para obtener el valor actualizado de bot_enabled
            $contact->refresh();

            // Verificar si el bot está habilitado para este contacto
            if (!$contact->bot_enabled) {
                Log::info('[sendCatalog] 🛑 Catálogo no enviado - Bot deshabilitado manualmente', [
                    'to' => substr($to, 0, 4) . '****' . substr($to, -4),
                    'contact_id' => $contact->id,
                    'bot_enabled' => $contact->bot_enabled
                ]);
                return false;
            }

            // Si el bot está activado manualmente, NO verificar actividad humana reciente
            // El catálogo se enviará inmediatamente cuando el bot esté activado

            // Obtener la configuración del catálogo desde la base de datos
            $catalogMenu = WhatsappMenu::where('action_id', 'prices_menu')->first();

            if (!$catalogMenu) {
                Log::warning('⚠️ Menú de catálogo no encontrado', ['action_id' => 'prices_menu']);
                $response = [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $to,
                    'type' => 'interactive',
                    'interactive' => [
                        'type' => 'catalog_message',
                        'body' => [
                            'text' => 'Aquí está nuestro catálogo de productos. Puedes explorar y seleccionar los productos que te interesen.'
                        ],
                        'action' => [
                            'name' => 'catalog_message'
                        ]
                    ]
                ];
            } else {
                $response = [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $to,
                    'type' => 'interactive',
                    'interactive' => [
                        'type' => 'catalog_message',
                        'body' => [
                            'text' => $catalogMenu->content ?? 'Aquí está nuestro catálogo de productos. Puedes explorar y seleccionar los productos que te interesen.'
                        ],
                        'action' => [
                            'name' => 'catalog_message'
                        ]
                    ]
                ];
            }

            $this->sendMessageToWhatsApp($to, $response);
            Log::info('✅ Catálogo enviado exitosamente', [
                'to' => substr($to, 0, 4) . '****' . substr($to, -4),
                'menu_id' => $catalogMenu->id ?? null
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error al enviar catálogo', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'to' => $to
            ]);
        }
    }

    /**
     * Envía notificaciones de monitoreo (WhatsApp y Email) cuando se recibe un mensaje
     */
    private function sendMonitoringNotifications(array $message)
    {
        try {
            // Obtener configuración de monitoreo
            $config = WhatsappChatbotConfig::first();

            if (!$config || !$config->monitoring_enabled) {
                return;
            }

            // Obtener información del contacto
            $from = $message['from'];
            $contact = WhatsappContact::where('phone_number', $from)->first();
            $contactName = $contact ? $contact->name : 'Contacto sin nombre';

            // Extraer contenido del mensaje según su tipo
            $messageContent = $this->extractMessageContent($message);
            $messageType = $message['type'] ?? 'desconocido';
            $timestamp = isset($message['timestamp'])
                ? date('Y-m-d H:i:s', $message['timestamp'])
                : now()->format('Y-m-d H:i:s');

            // Enviar mensaje de WhatsApp si está configurado
            // No enviar si el número de monitoreo es el mismo que el que está escribiendo
            if (!empty($config->monitoring_phone_number)) {
                // Normalizar números para comparación (quitar espacios, guiones, etc.)
                $normalizedFrom = preg_replace('/[^0-9]/', '', $from);
                $normalizedMonitoring = preg_replace('/[^0-9]/', '', $config->monitoring_phone_number);

                // Solo enviar si los números son diferentes
                if ($normalizedFrom !== $normalizedMonitoring) {
                    $this->sendMonitoringWhatsAppMessage(
                        $config->monitoring_phone_number,
                        $contactName,
                        $from,
                        $messageContent,
                        $messageType,
                        $timestamp
                    );
                } else {
                    Log::info('⏭️ Mensaje de monitoreo omitido: el número de monitoreo es el mismo que el remitente', [
                        'phone' => substr($from, 0, 4) . '****' . substr($from, -4)
                    ]);
                }
            }

            // Enviar email si está configurado
            if (!empty($config->monitoring_email)) {
                $this->sendMonitoringEmail(
                    $config->monitoring_email,
                    $contactName,
                    $from,
                    $messageContent,
                    $messageType,
                    $timestamp
                );
            }
        } catch (\Exception $e) {
            Log::error('❌ Error enviando notificaciones de monitoreo', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Extrae el contenido del mensaje según su tipo
     */
    private function extractMessageContent(array $message): string
    {
        $type = $message['type'] ?? 'text';

        switch ($type) {
            case 'text':
                if (is_array($message['text'] ?? null)) {
                    return $message['text']['body'] ?? 'Mensaje de texto sin contenido';
                }
                return $message['text'] ?? 'Mensaje de texto sin contenido';

            case 'interactive':
                $interactive = $message['interactive'] ?? null;
                if ($interactive) {
                    if (isset($interactive['button_reply']['title'])) {
                        return 'Botón: ' . $interactive['button_reply']['title'];
                    }
                    if (isset($interactive['list_reply']['title'])) {
                        return 'Lista: ' . $interactive['list_reply']['title'];
                    }
                }
                return 'Mensaje interactivo';

            case 'image':
                return '📷 Imagen enviada';

            case 'audio':
                return '🎵 Audio enviado';

            case 'video':
                return '🎥 Video enviado';

            case 'document':
                $document = $message['document'] ?? [];
                $filename = $document['filename'] ?? 'Documento';
                return '📄 Documento: ' . $filename;

            case 'location':
                $location = $message['location'] ?? [];
                $latitude = $location['latitude'] ?? '';
                $longitude = $location['longitude'] ?? '';
                return '📍 Ubicación: ' . $latitude . ', ' . $longitude;

            case 'sticker':
                return '😊 Sticker enviado';

            default:
                return 'Tipo de mensaje: ' . $type;
        }
    }

    /**
     * Envía un mensaje de WhatsApp de monitoreo
     */
    private function sendMonitoringWhatsAppMessage(
        string $monitoringPhone,
        string $contactName,
        string $contactPhone,
        string $messageContent,
        string $messageType,
        string $timestamp
    ) {
        try {
            // Crear o obtener el contacto de monitoreo
            $monitoringContact = WhatsappContact::where('phone_number', $monitoringPhone)->first();

            if (!$monitoringContact) {
                // Crear contacto de monitoreo si no existe
                $monitoringContact = WhatsappContact::create([
                    'business_profile_id' => $this->businessProfile->id,
                    'phone_number' => $monitoringPhone,
                    'name' => 'Monitoreo',
                    'status' => 'active'
                ]);
            }

            // Formatear el mensaje de monitoreo
            $monitoringMessage = "🔔 *Nuevo mensaje recibido*\n\n";
            $monitoringMessage .= "👤 *Contacto:* " . $contactName . "\n";
            $monitoringMessage .= "📱 *Teléfono:* " . $contactPhone . "\n";
            $monitoringMessage .= "📝 *Tipo:* " . ucfirst($messageType) . "\n";
            $monitoringMessage .= "🕐 *Fecha/Hora:* " . $timestamp . "\n\n";
            $monitoringMessage .= "*Mensaje:*\n" . $messageContent;

            // Enviar el mensaje
            $this->sendTextMessage($monitoringContact, $monitoringMessage, false);

            Log::info('✅ Mensaje de monitoreo enviado a WhatsApp', [
                'monitoring_phone' => substr($monitoringPhone, 0, 4) . '****' . substr($monitoringPhone, -4),
                'contact' => substr($contactPhone, 0, 4) . '****' . substr($contactPhone, -4)
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error enviando mensaje de monitoreo a WhatsApp', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'monitoring_phone' => substr($monitoringPhone, 0, 4) . '****' . substr($monitoringPhone, -4)
            ]);
        }
    }

    /**
     * Envía un email de monitoreo
     */
    private function sendMonitoringEmail(
        string $monitoringEmail,
        string $contactName,
        string $contactPhone,
        string $messageContent,
        string $messageType,
        string $timestamp
    ) {
        try {
            // Verificar que la configuración de correo esté disponible
            $mailDriver = config('mail.default', 'smtp');

            // Si el driver es 'log', solo registrar en logs (no intentar enviar realmente)
            if ($mailDriver === 'log') {
                Log::info('📧 Email de monitoreo (modo log)', [
                    'email' => $monitoringEmail,
                    'contact' => substr($contactPhone, 0, 4) . '****' . substr($contactPhone, -4),
                    'message' => 'El email se registró en los logs. Configura un servidor SMTP para enviar emails reales.'
                ]);
                return;
            }

            // Verificar configuración SMTP básica
            if ($mailDriver === 'smtp') {
                $mailHost = config('mail.mailers.smtp.host');
                if (empty($mailHost) || $mailHost === 'smtp.mailgun.org') {
                    Log::warning('⚠️ Configuración de correo no válida', [
                        'mail_driver' => $mailDriver,
                        'mail_host' => $mailHost,
                        'message' => 'Por favor, configura MAIL_HOST, MAIL_USERNAME y MAIL_PASSWORD en tu archivo .env'
                    ]);
                    return;
                }
            }

            Mail::to($monitoringEmail)->send(
                new MonitoringNotification(
                    $contactName,
                    $contactPhone,
                    $messageContent,
                    $messageType,
                    $timestamp
                )
            );

            Log::info('✅ Email de monitoreo enviado', [
                'email' => $monitoringEmail,
                'contact' => substr($contactPhone, 0, 4) . '****' . substr($contactPhone, -4)
            ]);
        } catch (\Exception $e) {
            // No lanzar excepción, solo registrar el error para que el sistema continúe funcionando
            Log::error('❌ Error enviando email de monitoreo', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'email' => $monitoringEmail,
                'suggestion' => 'Verifica tu configuración de correo en el archivo .env. Puedes usar MAIL_MAILER=log para desarrollo.'
            ]);
        }
    }
}
