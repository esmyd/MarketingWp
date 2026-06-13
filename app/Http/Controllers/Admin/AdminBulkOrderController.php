<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappContact;
use App\Services\BulkOrderService;
use App\Services\OrderPdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminBulkOrderController extends Controller
{
    public function __construct(
        private BulkOrderService $bulkOrders
    ) {}

    public function create(Request $request): View
    {
        $initialContact = null;
        if ($request->filled('contact')) {
            $contact = WhatsappContact::query()->find($request->integer('contact'));
            if ($contact) {
                $initialContact = [
                    'id' => $contact->id,
                    'name' => $contact->name ?: 'Cliente',
                    'phone' => $contact->phone_number,
                ];
            }
        }

        return view('admin.bulk-order.create', [
            'initialContact' => $initialContact,
            'catalogUrl' => route('admin.orders.bulk.catalog'),
            'submitUrl' => route('admin.orders.bulk.submit'),
            'contactsSearchUrl' => route('admin.orders.bulk.contacts'),
            'ordersUrl' => route('admin.orders'),
        ]);
    }

    public function searchContacts(Request $request): JsonResponse
    {
        $contacts = $this->bulkOrders->searchContacts(
            $request->string('q')->toString(),
            min(30, max(5, $request->integer('limit', 20)))
        );

        return response()->json(['contacts' => $contacts]);
    }

    public function catalog(Request $request): JsonResponse
    {
        return response()->json(
            $this->bulkOrders->catalogPayload(
                $request->integer('category') ?: null,
                $request->string('q')->toString() ?: null
            )
        );
    }

    public function submit(Request $request, OrderPdfService $pdf): JsonResponse
    {
        $validated = $request->validate([
            'contact_id' => ['required', 'integer', 'exists:whatsapp_contacts,id'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer', 'min:1'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'items.*.note' => ['nullable', 'string', 'max:500'],
            'order_note' => ['nullable', 'string', 'max:1000'],
            'notify_whatsapp' => ['sometimes', 'boolean'],
        ]);

        $contact = WhatsappContact::query()->findOrFail($validated['contact_id']);

        try {
            $cart = $this->bulkOrders->submitFromAdmin(
                $contact,
                $validated['items'],
                $validated['order_note'] ?? null,
                (int) $request->user()->id,
                $request->boolean('notify_whatsapp', true)
            );

            return response()->json([
                'ok' => true,
                'message' => 'Pedido registrado correctamente.',
                'order_number' => $cart->getOrderNumber(),
                'order_id' => $cart->id,
                'pdf_url' => $pdf->signedDownloadUrl($cart),
                'total' => (float) $cart->total,
                'items_count' => $cart->items->count(),
                'contact_name' => $contact->name,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
