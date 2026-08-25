<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Support\Facades\Cache;

class Tarif extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_skema',
        'tarif_standar',
        'batas_kuota_standar',
        'tarif_progresif',
        'biaya_admin',
        'tanggal_berlaku',
        'is_active',
        'created_by',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tarif_standar' => 'float',
            'batas_kuota_standar' => 'integer',
            'tarif_progresif' => 'float',
            'biaya_admin' => 'float',
            'tanggal_berlaku' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function tiers(): HasMany
    {
        return $this->hasMany(TarifTier::class, 'tarif_id')->orderBy('urutan');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function getActiveTarif(): ?self
    {
        $id = Cache::remember('active_tarif_id', 86400, function () {
            return self::where('is_active', true)->latest('tanggal_berlaku')->value('id');
        });

        return $id ? self::find($id) : null;
    }

    public static function clearCache(): void
    {
        Cache::forget('active_tarif_id');
        Cache::forget('active_tarif');
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
    }
}
