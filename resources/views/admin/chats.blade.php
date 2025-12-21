@extends('admin.layouts.app')

@section('header', 'Chats de Clientes')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-lg shadow overflow-hidden">
    <div class="divide-y divide-gray-200">
        @forelse($contacts as $contact)
            <a href="{{ route('admin.chat', $contact->id) }}" class="block hover:bg-green-50 transition-colors">
                <div class="flex items-center px-4 py-3">
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-full bg-green-200 flex items-center justify-center text-lg font-bold text-green-700">
                            {{ strtoupper(substr($contact->name ?? 'C', 0, 1)) }}
                        </div>
                    </div>
                    <!-- Chat Info -->
                    <div class="flex-1 min-w-0 ml-4">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-gray-900 text-base truncate">{{ $contact->name ?? 'Cliente' }}</span>
                            <span class="text-xs text-gray-400 ml-2 whitespace-nowrap">{{ $contact->last_message_at ? \Carbon\Carbon::parse($contact->last_message_at)->format('H:i') : '' }}</span>
                        </div>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-sm text-gray-500 truncate">{{ $contact->last_message ? $contact->last_message : $contact->phone_number }}</span>
                            @if($contact->unread_count ?? false)
                                <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-500 text-white">{{ $contact->unread_count }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="py-12 text-center text-gray-400">
                <svg class="mx-auto h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <h3 class="mt-2 text-base font-medium text-gray-900">No hay chats disponibles</h3>
                <p class="mt-1 text-sm text-gray-500">No se encontraron conversaciones activas.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
