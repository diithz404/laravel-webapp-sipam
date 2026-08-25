<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_transaksi',
        'catatan_meter_id',
        'jumlah_bayar',
        'tanggal_bayar',
        'metode',
        'dicatat_oleh',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_bayar' => 'float',
            'tanggal_bayar' => 'date',
        ];
    }

    public function catatanMeter(): BelongsTo
    {
        return $this->belongsTo(CatatanMeter::class, 'catatan_meter_id');
    }

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
