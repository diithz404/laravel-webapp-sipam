<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TarifTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'tarif_id',
        'urutan',
        'batas_bawah',
        'batas_atas',
        'harga_per_m3',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'batas_bawah' => 'integer',
            'batas_atas' => 'integer',
            'harga_per_m3' => 'float',
        ];
    }

    public function tarif(): BelongsTo
    {
        return $this->belongsTo(Tarif::class, 'tarif_id');
    }
}
