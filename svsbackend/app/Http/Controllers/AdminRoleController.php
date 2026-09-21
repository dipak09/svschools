<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminRoleController extends Controller
{
    public function index(): View
    {
        return view('admin.roles', [
            'roles' => Role::with('permissions')->orderBy('label')->get(),
            'permissions' => Permission::orderBy('group')->orderBy('label')->get()->groupBy('group'),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'alpha_dash', 'unique:roles,name'], 'label' => ['required', 'string', 'max:100'], 'permission_ids' => ['array'], 'permission_ids.*' => ['exists:permissions,id']]);
        $role = Role::create(['name' => strtolower($data['name']), 'label' => $data['label']]);
        $role->permissions()->sync($data['permission_ids'] ?? []);
        $this->log('created', 'roles', "Role {$role->label} was created.");

        return back()->with('status', 'Role created successfully.');
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate(['label' => ['required', 'string', 'max:100'], 'permission_ids' => ['array'], 'permission_ids.*' => ['exists:permissions,id']]);
        $role->update(['label' => $data['label']]);
        $role->permissions()->sync($data['permission_ids'] ?? []);
        $this->log('updated', 'roles', "Role {$role->label} was updated.");

        return back()->with('status', 'Role permissions updated.');
    }

    public function assignUser(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['role_id' => ['required', 'exists:roles,id']]);
        $role = Role::findOrFail($data['role_id']);
        $user->update(['role' => $role->name]);
        $user->roles()->sync([$role->id]);
        $this->log('updated', 'roles', "Role {$role->label} assigned to {$user->name}.");

        return back()->with('status', 'User role assigned.');
    }

    private function log(string $action, string $module, string $description): void
    {
        \App\Models\ActivityLog::create(['user_id' => auth()->id(), 'action' => $action, 'module' => $module, 'description' => $description]);
    }
}