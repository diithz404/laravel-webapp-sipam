<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pelanggan extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_rekening',
        'nama',
        'alamat',
        'rt_id',
        'no_hp',
        'angka_meter_awal',
        'status',
        'urutan_rumah',
    ];

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    public function catatanMeters(): HasMany
    {
        return $this->hasMany(CatatanMeter::class, 'pelanggan_id');
    }

    public function catatanMeterTerbaru(): HasOne
    {
        return $this->hasOne(CatatanMeter::class, 'pelanggan_id')->latestOfMany();
    }
}
