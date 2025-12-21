@extends('admin.layouts.app')

@section('header', 'Pedidos')

@section('content')
<div class="bg-white shadow-sm rounded-lg overflow-hidden">
    <div class="p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-100 sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700 uppercase tracking-wider bg-gray-100">
                            CLIENTE <span class="ml-1 cursor-pointer" title="Nombre y teléfono del cliente que realizó el pedido">ℹ️</span>
                        </th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700 uppercase tracking-wider bg-gray-100">
                            FECHA <span class="ml-1 cursor-pointer" title="Fecha y hora en que se realizó el pedido">ℹ️</span>
                        </th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700 uppercase tracking-wider bg-gray-100">
                            ESTADO <span class="ml-1 cursor-pointer" title="Estado actual del pedido. Puedes cambiarlo desde aquí.">ℹ️</span>
                        </th>
                        <th class="px-6 py-3 text-right font-semibold text-gray-700 uppercase tracking-wider bg-gray-100">
                            TOTAL <span class="ml-1 cursor-pointer" title="Suma total del pedido en moneda local">ℹ️</span>
                        </th>
                        <th class="px-6 py-3 text-center font-semibold text-gray-700 uppercase tracking-wider bg-gray-100">ACCIONES</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($orders as $order)
                        <tr id="order-row-{{ $order->id }}" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-gray-900 font-medium flex items-center">
                                    <button class="text-blue-600 hover:underline focus:outline-none" onclick="showContactModal({{ $order->contact->id }})">
                                        {{ $order->contact->name ?? 'Cliente' }}
                                    </button>
                                    <button class="ml-2 text-xs text-blue-400 hover:text-blue-700 focus:outline-none" title="Copiar teléfono" onclick="navigator.clipboard.writeText('{{ $order->contact->phone_number ?? '' }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16h8M8 12h8m-7 8h6a2 2 0 002-2V6a2 2 0 00-2-2H8a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </button>
                                </div>
                                <div class="text-gray-500 text-xs">
                                    {{ $order->contact->phone_number ?? 'Sin teléfono' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span id="badge-{{ $order->id }}" class="inline-block px-3 py-1 rounded-2xl text-xs font-semibold shadow-sm align-middle
                                    @if($order->status === 'completed') bg-green-100 text-green-800
                                    @elseif($order->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                    @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800
                                    @elseif($order->status === 'paid') bg-indigo-100 text-indigo-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($order->status) }}
                                </span>
                                <select class="ml-2 border rounded text-xs py-1 px-2 bg-white shadow-sm focus:ring focus:ring-blue-200" onchange="changeOrderStatus({{ $order->id }}, this.value)" style="min-width: 100px;">
                                    @foreach(['pending','confirmed','completed','cancelled','payment_pending','paid'] as $status)
                                        <option value="{{ $status }}" @if($order->status === $status) selected @endif>{{ ucfirst(str_replace('_',' ', $status)) }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-gray-900 font-semibold">
                                ${{ number_format($order->total, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors text-xs font-semibold" onclick="showOrderDetails({{ $order->id }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Ver detalles
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                No hay pedidos registrados
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>
</div>

<!-- Modal para detalles del pedido -->
<div id="orderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Detalles del Pedido</h3>
            <div id="orderDetails" class="mt-2 text-sm text-gray-700">
                <div class="flex justify-center items-center h-24" id="orderLoading">
                    <span class="text-gray-400">Cargando...</span>
                </div>
            </div>
            <div class="mt-4">
                <button onclick="closeOrderModal()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalles del contacto -->
<div id="contactModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Datos del Contacto</h3>
            <div id="contactDetails" class="mt-2 text-sm text-gray-700">
                <div class="flex justify-center items-center h-24" id="contactLoading">
                    <span class="text-gray-400">Cargando...</span>
                </div>
            </div>
            <div class="mt-4">
                <button onclick="closeContactModal()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showOrderDetails(orderId) {
    document.getElementById('orderModal').classList.remove('hidden');
    const detailsDiv = document.getElementById('orderDetails');
    detailsDiv.innerHTML = '<div class="flex justify-center items-center h-24" id="orderLoading"><span class="text-gray-400">Cargando...</span></div>';
    fetch(`/admin/orders/${orderId}/details`)
        .then(res => res.json())
        .then(order => {
            let html = '';
            html += `<div class='mb-2'><b>Cliente:</b> ${order.contact?.name ?? 'Cliente'}<br>`;
            html += `<b>Teléfono:</b> ${order.contact?.phone_number ?? 'Sin teléfono'}<br>`;
            html += `<b>Estado:</b> <span class='px-2 py-1 rounded bg-gray-100'>${order.status}</span><br>`;
            html += `<b>Total:</b> $${parseFloat(order.total).toFixed(2)}<br>`;
            html += `<b>Fecha:</b> ${new Date(order.created_at).toLocaleString('es-ES')}<br></div>`;
            if(order.items && order.items.length > 0) {
                html += `<table class='w-full text-xs mb-2'><thead><tr><th class='text-left'>Producto</th><th>Cant.</th><th>Precio</th></tr></thead><tbody>`;
                order.items.forEach(item => {
                    html += `<tr><td>${item.name}</td><td class='text-center'>${item.quantity}</td><td class='text-right'>$${parseFloat(item.price).toFixed(2)}</td></tr>`;
                });
                html += `</tbody></table>`;
            } else {
                html += `<div class='text-gray-400'>Sin productos</div>`;
            }
            detailsDiv.innerHTML = html;
        })
        .catch(() => {
            detailsDiv.innerHTML = '<div class="text-red-500">No se pudo cargar el detalle.</div>';
        });
}

function closeOrderModal() {
    document.getElementById('orderModal').classList.add('hidden');
}

function changeOrderStatus(orderId, newStatus) {
    fetch(`/admin/orders/${orderId}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            // Actualizar badge visualmente
            const badge = document.getElementById('badge-' + orderId);
            badge.textContent = newStatus;
            badge.className = 'px-3 py-1 rounded-full text-sm ';
            if(newStatus === 'completed') badge.className += 'bg-green-100 text-green-800';
            else if(newStatus === 'pending') badge.className += 'bg-yellow-100 text-yellow-800';
            else if(newStatus === 'cancelled') badge.className += 'bg-red-100 text-red-800';
            else if(newStatus === 'confirmed') badge.className += 'bg-blue-100 text-blue-800';
            else if(newStatus === 'paid') badge.className += 'bg-indigo-100 text-indigo-800';
            else badge.className += 'bg-gray-100 text-gray-800';
        } else {
            alert('No se pudo actualizar el estado.');
        }
    })
    .catch(() => alert('Error al actualizar el estado.'));
}

function showContactModal(contactId) {
    document.getElementById('contactModal').classList.remove('hidden');
    const detailsDiv = document.getElementById('contactDetails');
    detailsDiv.innerHTML = '<div class="flex justify-center items-center h-24" id="contactLoading"><span class="text-gray-400">Cargando...</span></div>';
    fetch(`/admin/contacts/${contactId}`)
        .then(res => res.json())
        .then(contact => {
            let html = '';
            html += `<div class='mb-2'><b>Nombre:</b> ${contact.name ?? 'Sin nombre'}<br>`;
            html += `<b>Teléfono:</b> ${contact.phone_number ? contact.phone_number + ` <button class='ml-1 text-xs text-blue-500' onclick=\\"navigator.clipboard.writeText('${contact.phone_number}')\\">Copiar 📋</button>` : 'Sin teléfono'}<br>`;
            html += `<b>Email:</b> ${contact.email ?? 'Sin email'}<br>`;
            html += `<b>Fecha de registro:</b> ${contact.created_at ? new Date(contact.created_at).toLocaleString('es-ES') : 'Sin fecha'}<br>`;
            if(contact.metadata) {
                html += `<b>Metadata:</b> <pre class='bg-gray-100 p-2 rounded text-xs'>${JSON.stringify(contact.metadata, null, 2)}</pre>`;
            }
            html += `</div>`;
            detailsDiv.innerHTML = html;
        })
        .catch(() => {
            detailsDiv.innerHTML = '<div class="text-red-500">No se pudo cargar el contacto.</div>';
        });
}

function closeContactModal() {
    document.getElementById('contactModal').classList.add('hidden');
}
</script>
@endsection
