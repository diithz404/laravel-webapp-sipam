<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodeTagihan extends Model
{
    use HasFactory;

    protected $fillable = [
        'bulan',
        'tahun',
        'nama_periode',
        'status',
        'jatuh_tempo',
        'tanggal_ditutup',
        'closed_by',
    ];

    protected function casts(): array
    {
        return [
            'bulan' => 'integer',
            'tahun' => 'integer',
            'jatuh_tempo' => 'date',
            'tanggal_ditutup' => 'datetime',
        ];
    }

    public function catatanMeters(): HasMany
    {
        return $this->hasMany(CatatanMeter::class, 'periode_id');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public static function getActivePeriode(): ?self
    {
        return self::where('status', 'aktif')->latest('id')->first();
    }
}
