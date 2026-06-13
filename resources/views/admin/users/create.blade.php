@extends('admin.layouts.app')

@section('header', 'Nuevo usuario')

@section('content')
<div class="bg-white shadow-sm rounded-lg max-w-lg mx-auto p-6">
    <h2 class="text-lg font-semibold mb-4">Crear usuario administrador</h2>
    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
            <input type="text" name="username" value="{{ old('username') }}" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
            <select name="role_id" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
            <input type="password" name="password" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña</label>
            <input type="password" name="password_confirmation" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
        </div>
        <div class="flex gap-2 pt-2">
            <button type="submit" class="btn btn-success">Crear usuario</button>
            <a href="{{ route('admin.roles.index', ['tab' => 'users']) }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
