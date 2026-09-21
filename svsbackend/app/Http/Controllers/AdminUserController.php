<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    public const ROLES = [
        User::ROLE_SUPER_ADMIN,
        User::ROLE_ADMIN,
        User::ROLE_PRINCIPAL,
        User::ROLE_TEACHER,
        User::ROLE_STUDENT,
        User::ROLE_PARENT,
        User::ROLE_STAFF,
    ];

    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(fn ($users) => $users
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%"));
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->string('role')->toString()))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => self::ROLES,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', ['roles' => self::ROLES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['password'] = $request->string('password')->toString();

        $user = User::create($data);
        $this->syncUserRole($user, $data['role']);
        $this->log('created', 'users', "User {$user->name} was created.");

        return to_route('admin.users.index')->with('status', 'User created successfully.');
    }

    public function show(User $user): View
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => self::ROLES,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);

        if ($request->filled('password')) {
            $data['password'] = $request->string('password')->toString();
        } else {
            unset($data['password']);
        }

        $user->update($data);
        $this->syncUserRole($user, $user->role);
        $this->log('updated', 'users', "User {$user->name} was updated.");

        return to_route('admin.users.index')->with('status', 'User updated successfully.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        abort_if(auth()->id() === $user->id, 422, 'You cannot deactivate your own account.');

        $user->update(['status' => $user->isActive() ? 'inactive' : 'active']);
        $this->log('updated', 'users', "User {$user->name} status was changed.");

        return back()->with('status', 'User status updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if(auth()->id() === $user->id, 422, 'You cannot delete your own account.');

        $user->delete();
        $this->log('deleted', 'users', "User {$user->name} was deleted.");

        return to_route('admin.users.index')->with('status', 'User deleted successfully.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in(self::ROLES)],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    private function syncUserRole(User $user, string $roleName): void
    {
        $role = Role::firstOrCreate(
            ['name' => $roleName],
            ['label' => Str::title(str_replace('_', ' ', $roleName))]
        );

        $user->roles()->sync([$role->id]);
    }

    private function log(string $action, string $module, string $description): void
    {
        ActivityLog::create(['user_id' => auth()->id(), 'action' => $action, 'module' => $module, 'description' => $description]);
    }
}