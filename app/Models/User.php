<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'status',
        'is_active',
        'rt_id',
        'password',
        'password_changed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function rt()
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    public function isDefaultPassword(): bool
    {
        return is_null($this->password_changed_at);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPetugas(): bool
    {
        return $this->role === 'petugas';
    }

    public function rts(): BelongsToMany
    {
        return $this->belongsToMany(Rt::class, 'rt_petugas', 'user_id', 'rt_id')->withTimestamps();
    }

    public function catatanMeters(): HasMany
    {
        return $this->hasMany(CatatanMeter::class, 'input_by');
    }

    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'dicatat_oleh');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }
}
