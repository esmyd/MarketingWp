<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserAdminController extends Controller
{
    public function create()
    {
        $roles = Role::when(!auth()->user()->isSuperAdmin(), fn ($q) => $q->where('slug', '!=', 'super_admin'))
            ->orderBy('name')
            ->get();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request, PermissionService $permissionService)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:60', 'regex:/^[a-zA-Z0-9._-]+$/', 'unique:users,username'],
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::findOrFail($data['role_id']);

        if (!auth()->user()->isSuperAdmin() && $role->slug === 'super_admin') {
            abort(403);
        }

        $user = User::create([
            'name' => $data['name'],
            'username' => strtolower($data['username']),
            'email' => $data['email'],
            'password' => $data['password'],
            'is_admin' => true,
            'role_id' => $role->id,
            'role' => $role->slug,
        ]);

        $permissionService->forgetUserCache($user);

        return redirect()->route('admin.roles.index', ['tab' => 'users'])->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        $roles = Role::when(!auth()->user()->isSuperAdmin(), fn ($q) => $q->where('slug', '!=', 'super_admin'))
            ->orderBy('name')
            ->get();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user, PermissionService $permissionService)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:60', 'regex:/^[a-zA-Z0-9._-]+$/', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::findOrFail($data['role_id']);

        if (!auth()->user()->isSuperAdmin() && $role->slug === 'super_admin') {
            abort(403);
        }

        $user->name = $data['name'];
        $user->username = strtolower($data['username']);
        $user->email = $data['email'];
        $user->role_id = $role->id;
        $user->role = $role->slug;

        if (!empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();
        $permissionService->forgetUserCache($user);

        return redirect()->route('admin.roles.index', ['tab' => 'users'])->with('success', 'Usuario actualizado.');
    }

    public function updateRole(Request $request, User $user, PermissionService $permissionService)
    {
        $data = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::findOrFail($data['role_id']);

        if (!auth()->user()->isSuperAdmin() && $role->slug === 'super_admin') {
            abort(403);
        }

        $user->update([
            'role_id' => $role->id,
            'role' => $role->slug,
        ]);

        $permissionService->forgetUserCache($user);

        return redirect()
            ->route('admin.roles.index', ['tab' => 'users'])
            ->with('success', "Rol de {$user->name} actualizado.");
    }
}
