<?php

namespace App\Http\Controllers;

use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsappWebhookController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsappService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    public function verify(Request $request)
    {
        Log::info('Parámetros del Webhook:', $request->query());

        $modo = $request->query('hub_mode');
        $tokenRecibido = $request->query('hub_verify_token');
        $desafio = $request->query('hub_challenge');

        $tokenEsperado = 'appSigloTecnologicoWtp2';

        Log::info($modo);
        Log::info($tokenRecibido);
        Log::info($desafio);
        Log::info($tokenEsperado);

        try {
            if ($modo === 'subscribe' && $tokenRecibido === $tokenEsperado) {
                Log::info("Respondiendo con challenge: " . $desafio);
                return response($desafio, 200);
            }
            return response("Token inválido", 403)->header('Content-Type', 'text/plain');
        } catch (\Throwable $excepcion) {
            Log::error("Error en la verificación del webhook: " . $excepcion->getMessage());
            return response()->json([
                'estado' => false,
                'mensaje' => $excepcion->getMessage()
            ], 500);
        }
    }

    public function webhook(Request $request)
    {
        try {
            // Decodificar el JSON recibido
            $data = json_decode($request->getContent(), true);
            //Log::info('Webhook payload:', $data);

            // Extraer la variable 'object'
            $object = $data['object'] ?? null;
            //Log::info("Objeto recibido: " . $object);

            // Extraer la primera entrada (entry)
            $entry = $data['entry'][0] ?? null;
            if (!$entry) {
                Log::warning("No se encontró entry en el payload");
                return response()->json(['estado' => false, 'mensaje' => 'No entry encontrada'], 400);
            }

            $entryId = $entry['id'] ?? null;
            //Log::info("ID de la entrada: " . $entryId);

            // Extraer el primer cambio dentro de la entrada
            $change = $entry['changes'][0] ?? null;
            if (!$change) {
                Log::warning("No se encontró change en la entry");
                return response()->json(['estado' => false, 'mensaje' => 'No change encontrado'], 400);
            }

            $value = $change['value'] ?? null;
            if (!$value) {
                Log::warning("No se encontró value en el change");
                return response()->json(['estado' => false, 'mensaje' => 'No value encontrado'], 400);
            }

            // Extraer variables desde 'value'
            $messagingProduct = $value['messaging_product'] ?? null;
            $metadata = $value['metadata'] ?? [];
            $contactsArray = $value['contacts'] ?? [];
            $messagesArray = $value['messages'] ?? [];
            $statusesArray = $value['statuses'] ?? [];

            //Log::info("Producto de mensajería: " . $messagingProduct);
            //Log::info("Metadata: " . json_encode($metadata, JSON_UNESCAPED_UNICODE));

            // Extraer y desglosar el primer contacto
            $contact = $contactsArray[0] ?? [];
            $contactName = $contact['profile']['name'] ?? null;
            $contactWaId = $contact['wa_id'] ?? null;
            Log::info("Contacto recibido - Nombre: " . $contactName . ", WA_ID: " . $contactWaId);

            // Si hay mensajes, procesarlos
            if (!empty($messagesArray)) {
                $message = $messagesArray[0];
                $from = $message['from'] ?? null;
                $messageId = $message['id'] ?? null;
                $timestamp = $message['timestamp'] ?? null;
                $type = $message['type'] ?? null;
                $mensajeContent = null;

                // Procesar según el tipo de mensaje
                switch ($type) {
                    case 'text':
                        $mensajeContent = $message['text']['body'] ?? null;
                        break;
                    case 'image':
                        $mensajeContent = $message['image'] ?? null;
                        break;
                    case 'audio':
                        $mensajeContent = $message['audio'] ?? null;
                        break;
                    case 'video':
                        $mensajeContent = $message['video'] ?? null;
                        break;
                    case 'document':
                        $mensajeContent = $message['document'] ?? null;
                        break;
                    case 'sticker':
                        $mensajeContent = $message['sticker'] ?? null;
                        break;
                    case 'location':
                        $mensajeContent = $message['location'] ?? null;
                        break;
                    case 'contact':
                        $mensajeContent = $message['contact'] ?? null;
                        break;
                    case 'interactive':
                        $interactive = $message['interactive'] ?? null;
                        if ($interactive) {
                            if (isset($interactive['button_reply'])) {
                                $mensajeContent = $interactive['button_reply'];
                            } elseif (isset($interactive['list_reply'])) {
                                $mensajeContent = $interactive['list_reply'];
                            }
                        }
                        break;
                    default:
                        $mensajeContent = "Tipo de mensaje no soportado";
                        break;
                }

                //Log::info("Mensaje recibido desde: " . $from);
                //Log::info("ID del mensaje: " . $messageId);
                //Log::info("Timestamp: " . $timestamp);
                Log::info("Tipo de mensaje: " . $type);
                Log::info("Contenido del mensaje: " . json_encode($mensajeContent, JSON_UNESCAPED_UNICODE));

                // Preparar datos para el servicio
                $messageData = [
                    'from' => $from,
                    'id' => $messageId,
                    'type' => $type,
                    'timestamp' => $timestamp,
                    'text' => $mensajeContent,
                    'contacts' => $contactsArray,
                    'interactive' => $message['interactive'] ?? null
                ];

                // Procesar el mensaje con el servicio
                $this->whatsappService->processIncomingMessage($messageData);
            }
            // Si hay actualizaciones de estado, procesarlas
            elseif (!empty($statusesArray)) {
                $status = $statusesArray[0];
                Log::info("Actualización de estado recibida:", $status);
                // Aquí puedes procesar las actualizaciones de estado si es necesario
            }
            else {
                Log::info("No hay mensajes ni actualizaciones de estado para procesar");
            }

            return response()->json([
                'estado' => true,
                'mensaje' => 'Webhook procesado correctamente',
                'datos' => [
                    'object' => $object,
                    'entry_id' => $entryId,
                    'messaging_product' => $messagingProduct,
                    'metadata' => $metadata,
                    'contact' => [
                        'nombre' => $contactName,
                        'wa_id' => $contactWaId,
                    ],
                    'from' => $from ?? null,
                    'message_id' => $messageId ?? null,
                    'timestamp' => $timestamp ?? null,
                    'type' => $type ?? null,
                    'contenido' => $mensajeContent ?? null,
                ]
            ], 200);
        } catch (\Throwable $excepcion) {
            Log::error("Error en el procesamiento del webhook: " . $excepcion->getMessage());
            return response()->json([
                'estado' => false,
                'mensaje' => $excepcion->getMessage()
            ], 500);
        }
    }
}
