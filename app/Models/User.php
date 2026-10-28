<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function dokter(): HasOne
    {
        return $this->hasOne(Dokter::class);
    }

    public function verifiedSips(): HasMany
    {
        return $this->hasMany(SipRecord::class, 'verified_by');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdminSdm(): bool
    {
        return $this->role === 'admin_sdm';
    }

    public function isDokter(): bool
    {
        return $this->role === 'dokter';
    }

    public function hasAdminAccess(): bool
    {
        return in_array($this->role, ['super_admin', 'admin_sdm']);
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super Admin (IT/Manajemen)',
            'admin_sdm' => 'Admin SDM (Kepegawaian)',
            'dokter' => 'Dokter (Staf Medis)',
            default => ucfirst($this->role),
        };
    }
}
