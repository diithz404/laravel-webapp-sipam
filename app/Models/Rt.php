<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rt extends Model
{
    use HasFactory;

    protected $table = 'rts';

    protected $fillable = [
        'kode_rt',
        'nama_rt',
        'wilayah',
        'keterangan',
    ];

    public function petugas(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'rt_petugas', 'rt_id', 'user_id')->withTimestamps();
    }

    public function pelanggans(): HasMany
    {
        return $this->hasMany(Pelanggan::class, 'rt_id')->orderBy('urutan_rumah');
    }
}
