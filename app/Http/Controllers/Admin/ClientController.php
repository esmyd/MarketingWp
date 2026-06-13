<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappContact;
use App\Services\ClientInsightsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request, ClientInsightsService $insights): View
    {
        $clients = $insights->paginate($request);
        $summary = $insights->summaryStats($request);

        return view('admin.clients.index', [
            'clients' => $clients,
            'summary' => $summary,
            'segments' => ClientInsightsService::SEGMENTS,
            'sortOptions' => ClientInsightsService::SORT_OPTIONS,
            'filters' => $request->only(['q', 'segment', 'sort', 'activity_from', 'activity_to', 'min_orders']),
            'insights' => $insights,
        ]);
    }

    public function show(WhatsappContact $client, ClientInsightsService $insights): View
    {
        $detail = $insights->contactDetail($client);

        return view('admin.clients.show', array_merge($detail, [
            'insights' => $insights,
        ]));
    }
}
