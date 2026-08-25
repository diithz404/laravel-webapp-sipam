<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Support\Facades\Cache;

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
        $id = Cache::remember('active_periode_id', 86400, function () {
            return self::where('status', 'aktif')->latest('id')->value('id');
        });

        if ($id) {
            $found = self::find($id);
            if ($found) return $found;
        }

        // Check if there is an active period without cache
        $active = self::where('status', 'aktif')->latest('id')->first();
        if ($active) {
            self::clearCache();
            return $active;
        }

        // If no period exists at all in the database, automatically initialize current month's active period
        if (self::count() === 0) {
            $namaBulan = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $bulan = (int) date('n');
            $tahun = (int) date('Y');
            $namaPeriode = ($namaBulan[$bulan] ?? date('F')) . ' ' . $tahun;

            $newPeriode = self::create([
                'bulan' => $bulan,
                'tahun' => $tahun,
                'nama_periode' => $namaPeriode,
                'status' => 'aktif',
                'jatuh_tempo' => date('Y-m-25'),
            ]);

            self::clearCache();
            return $newPeriode;
        }

        return self::latest('id')->first();
    }

    public static function clearCache(): void
    {
        Cache::forget('active_periode_id');
        Cache::forget('active_periode');
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
    }
}
