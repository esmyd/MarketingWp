@extends('admin.layouts.app')

@section('header', 'Gestión de Productos')

@section('content')
<div class="min-h-screen bg-gray-100">
    <!-- Notificaciones -->
    <div id="notification" class="fixed top-4 right-4 z-50 hidden">
        <div class="rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg id="notification-icon" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p id="notification-message" class="text-sm font-medium"></p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button onclick="hideNotification()" class="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2">
                            <span class="sr-only">Cerrar</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="py-10">
        <header>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="md:flex md:items-center md:justify-between">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                            Productos
                        </h2>
                    </div>
                    <div class="mt-4 flex md:mt-0 md:ml-4">
                        <button type="button" onclick="openCreateModal()" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Nuevo Producto
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filtros y búsqueda -->
            <div class="mt-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex-1 min-w-0">
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" id="search" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md" placeholder="Buscar productos...">
                    </div>
                </div>
                <div class="mt-4 sm:mt-0 sm:ml-4 flex space-x-4">
                    <select id="category-filter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">Todas las categorías</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->title }}">{{ $category->title }}</option>
                        @endforeach
                    </select>
                    <select id="status-filter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">Todos los estados</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>
            </div>

            <!-- Lista de productos -->
            <div class="mt-8 flex flex-col">
                <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">SKU</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Producto</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Categoría</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Precio</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Estado</th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                            <span class="sr-only">Acciones</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach($products as $product)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                            {{ $product->sku }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 flex-shrink-0">
                                                    <span class="text-2xl">{{ $product->icon }}</span>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                                    <div class="text-gray-500">{{ Str::limit($product->description, 50) }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            @if(is_object($product->category))
                                                {{ $product->category->title }}
                                            @elseif(is_string($product->category))
                                                {{ $product->category }}
                                            @else
                                                Sin categoría
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <div class="flex flex-col">
                                                <span class="font-medium text-gray-900">${{ number_format($product->price, 2) }}</span>
                                                @if($product->promo_price)
                                                    <span class="text-red-600">${{ number_format($product->promo_price, 2) }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <div class="flex justify-end space-x-3">
                                                <button onclick="openEditModal({{ $product->id }})" class="text-indigo-600 hover:text-indigo-900">
                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                                <button onclick="deleteProduct({{ $product->id }})" class="text-red-600 hover:text-red-900">
                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal para crear/editar producto -->
<div id="productModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <form id="productForm" method="POST">
                @csrf
                <div id="methodField"></div>
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modalTitle">
                        Nuevo Producto
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            Complete los detalles del producto.
                        </p>
                    </div>
                </div>
                <div class="mt-6 space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="sku" class="block text-sm font-medium text-gray-700">SKU</label>
                            <div class="mt-1">
                                <input type="text" name="sku" id="sku" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                            </div>
                        </div>
                        <div>
                            <label for="menu_item_id" class="block text-sm font-medium text-gray-700">Categoría</label>
                            <div class="mt-1">
                                <select name="menu_item_id" id="menu_item_id" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                                    <option value="">Seleccione una categoría</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                        <div class="mt-1">
                            <input type="text" name="name" id="name" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                        </div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Descripción</label>
                        <div class="mt-1">
                            <textarea id="description" name="description" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                        </div>
                    </div>

                    <div>
                        <label for="benefits" class="block text-sm font-medium text-gray-700">Beneficios</label>
                        <div class="mt-1">
                            <textarea id="benefits" name="benefits" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                        </div>
                    </div>

                    <div>
                        <label for="nutritional_info" class="block text-sm font-medium text-gray-700">Información Nutricional</label>
                        <div class="mt-1">
                            <textarea id="nutritional_info" name="nutritional_info" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700">Precio</label>
                            <div class="mt-1">
                                <input type="number" step="0.01" name="price" id="price" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                            </div>
                        </div>
                        <div>
                            <label for="promo_price" class="block text-sm font-medium text-gray-700">Precio Promocional</label>
                            <div class="mt-1">
                                <input type="number" step="0.01" name="promo_price" id="promo_price" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="icon" class="block text-sm font-medium text-gray-700">Icono</label>
                        <div class="mt-1">
                            <input type="text" name="icon" id="icon" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Ej: 🛍️">
                        </div>
                    </div>

                    <div class="relative flex items-start">
                        <div class="flex items-center h-5">
                            <input id="is_active" name="is_active" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" checked>
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="is_active" class="font-medium text-gray-700">Activo</label>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm">
                        Guardar
                    </button>
                    <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentProductId = null;

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Nuevo Producto';
    document.getElementById('productForm').reset();
    document.getElementById('productForm').action = '{{ route("admin.products.store") }}';
    document.getElementById('methodField').innerHTML = '';
    document.getElementById('productModal').classList.remove('hidden');
}

function openEditModal(id) {
    currentProductId = id;
    document.getElementById('modalTitle').textContent = 'Editar Producto';
    document.getElementById('productForm').action = `/admin/products/${id}`;
    document.getElementById('methodField').innerHTML = '@method("PUT")';

    fetch(`/admin/products/${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('sku').value = data.sku;
            document.getElementById('name').value = data.name;
            document.getElementById('description').value = data.description || '';
            document.getElementById('menu_item_id').value = data.menu_item_id;
            document.getElementById('price').value = data.price;
            document.getElementById('promo_price').value = data.promo_price || '';
            document.getElementById('benefits').value = data.benefits || '';
            document.getElementById('nutritional_info').value = data.nutritional_info || '';
            document.getElementById('icon').value = data.icon || '';
            document.getElementById('is_active').checked = data.is_active;
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Ha ocurrido un error al cargar los datos del producto', 'error');
        });

    document.getElementById('productModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('productModal').classList.add('hidden');
    currentProductId = null;
}

function deleteProduct(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este producto?')) {
        fetch(`/admin/products/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            showNotification('Producto eliminado correctamente');
            setTimeout(() => window.location.reload(), 1000);
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Ha ocurrido un error al eliminar el producto', 'error');
        });
    }
}

function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    const notificationMessage = document.getElementById('notification-message');
    const notificationIcon = document.getElementById('notification-icon');

    notificationMessage.textContent = message;

    if (type === 'success') {
        notification.classList.remove('bg-red-50', 'text-red-800');
        notification.classList.add('bg-green-50', 'text-green-800');
        notificationIcon.classList.remove('text-red-400');
        notificationIcon.classList.add('text-green-400');
    } else {
        notification.classList.remove('bg-green-50', 'text-green-800');
        notification.classList.add('bg-red-50', 'text-red-800');
        notificationIcon.classList.remove('text-green-400');
        notificationIcon.classList.add('text-red-400');
    }

    notification.classList.remove('hidden');
    setTimeout(hideNotification, 5000);
}

function hideNotification() {
    document.getElementById('notification').classList.add('hidden');
}

// Filtrado y búsqueda
document.getElementById('search').addEventListener('input', filterTable);
document.getElementById('category-filter').addEventListener('change', filterTable);
document.getElementById('status-filter').addEventListener('change', filterTable);

function filterTable() {
    const searchTerm = document.getElementById('search').value.toLowerCase();
    const categoryFilter = document.getElementById('category-filter').value;
    const statusFilter = document.getElementById('status-filter').value;
    const rows = document.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        const category = row.querySelector('td:nth-child(3)').textContent;
        const status = row.querySelector('td:nth-child(5)').textContent.trim();

        const matchesSearch = name.includes(searchTerm);
        const matchesCategory = !categoryFilter || category.includes(categoryFilter);
        const matchesStatus = !statusFilter || (statusFilter === '1' && status === 'Activo') || (statusFilter === '0' && status === 'Inactivo');

        row.style.display = matchesSearch && matchesCategory && matchesStatus ? '' : 'none';
    });
}

// Actualizar el manejo del formulario
document.getElementById('productForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const isEdit = currentProductId !== null;
    const url = isEdit ? `/admin/products/${currentProductId}` : '{{ route("admin.products.store") }}';
    const method = isEdit ? 'PUT' : 'POST';

    // Convertir FormData a objeto
    const data = {};
    formData.forEach((value, key) => {
        if (key === 'is_active') {
            data[key] = value === 'on';
        } else {
            data[key] = value;
        }
    });

    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.errors) {
            const errorMessages = Object.values(data.errors).flat();
            showNotification(errorMessages.join('\n'), 'error');
        } else {
            showNotification(isEdit ? 'Producto actualizado correctamente' : 'Producto creado correctamente');
            setTimeout(() => window.location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Ha ocurrido un error al guardar el producto', 'error');
    });
});
</script>
@endpush


