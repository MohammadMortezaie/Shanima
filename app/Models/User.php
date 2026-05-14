<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_CLIENT = 'client';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'date_of_birth',
        'password',
        'role',
        'is_premium',
    ];

    /**
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
            'date_of_birth' => 'date',
            'is_premium' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function scopeClients(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_CLIENT);
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isClient(): bool
    {
        return $this->role === self::ROLE_CLIENT;
    }

    public function programItems(): HasMany
    {
        return $this->hasMany(ProgramItem::class);
    }

    public function assignedProgramItems(): HasMany
    {
        return $this->hasMany(ProgramItem::class, 'assigned_by');
    }

    public function programCompletions(): HasMany
    {
        return $this->hasMany(ProgramCompletion::class);
    }
}
