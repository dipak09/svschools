<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'status',
        'password',
    ];

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_PRINCIPAL = 'principal';
    public const ROLE_TEACHER = 'teacher';
    public const ROLE_STAFF = 'staff';
    public const ROLE_STUDENT = 'student';
    public const ROLE_PARENT = 'parent';

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function isStaff(): bool
    {
        return $this->hasRole(self::ROLE_STAFF);
    }

    public function isStudent(): bool
    {
        return $this->hasRole(self::ROLE_STUDENT);
    }

    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }

    public function canManageUsers(): bool
    {
        return $this->hasAnyRole([self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN]);
    }

    public function roleDefinition(): ?Role
    {
        return Role::where('name', $this->role)->first();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->hasRole(self::ROLE_SUPER_ADMIN)) {
            return true;
        }

        return $this->roleDefinition()?->permissions()->where('name', $permission)->exists() ?? false;
    }

    public function student(): HasOne { return $this->hasOne(Student::class); }
    public function teacher(): HasOne { return $this->hasOne(Teacher::class); }
    public function parentProfile(): HasOne { return $this->hasOne(ParentModel::class); }
    public function activities(): HasMany { return $this->hasMany(ActivityLog::class); }
    public function roles(): BelongsToMany { return $this->belongsToMany(Role::class); }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
