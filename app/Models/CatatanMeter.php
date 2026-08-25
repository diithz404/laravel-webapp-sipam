<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatatanMeter extends Model
{
    use HasFactory;

    protected $fillable = [
        'pelanggan_id',
        'periode_id',
        'angka_lalu',
        'angka_ini',
        'pemakaian',
        'pemakaian_standar',
        'pemakaian_progresif',
        'tarif_id',
        'snapshot_tarif_standar',
        'snapshot_tarif_progresif',
        'snapshot_kuota_standar',
        'snapshot_biaya_admin',
        'biaya_pemakaian',
        'biaya_admin',
        'tunggakan_lalu',
        'total_tagihan',
        'status_meter',
        'status_bayar',
        'total_dibayar',
        'sisa_tagihan',
        'input_by',
        'input_at',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'angka_lalu' => 'integer',
            'angka_ini' => 'integer',
            'pemakaian' => 'integer',
            'pemakaian_standar' => 'integer',
            'pemakaian_progresif' => 'integer',
            'snapshot_tarif_standar' => 'float',
            'snapshot_tarif_progresif' => 'float',
            'snapshot_kuota_standar' => 'integer',
            'snapshot_biaya_admin' => 'float',
            'biaya_pemakaian' => 'float',
            'biaya_admin' => 'float',
            'tunggakan_lalu' => 'float',
            'total_tagihan' => 'float',
            'total_dibayar' => 'float',
            'sisa_tagihan' => 'float',
            'input_at' => 'datetime',
        ];
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeTagihan::class, 'periode_id');
    }

    public function tarif(): BelongsTo
    {
        return $this->belongsTo(Tarif::class, 'tarif_id');
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'input_by');
    }

    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'catatan_meter_id')->latest('tanggal_bayar');
    }

    /**
     * Hitung kalkulasi tagihan berdasarkan angka_ini dan tarif snapshot
     */
    public function recalculateBilling(?Tarif $tarif = null): void
    {
        if ($this->angka_ini === null) {
            return;
        }

        $tarif = $tarif ?? Tarif::getActiveTarif() ?? new Tarif([
            'tarif_standar' => 350,
            'batas_kuota_standar' => 20,
            'tarif_progresif' => 400,
            'biaya_admin' => 2000,
        ]);

        $this->snapshot_tarif_standar = $tarif->tarif_standar;
        $this->snapshot_tarif_progresif = $tarif->tarif_progresif;
        $this->snapshot_kuota_standar = $tarif->batas_kuota_standar;
        $this->snapshot_biaya_admin = $tarif->biaya_admin;
        $this->tarif_id = $tarif->id;

        $pemakaian = max(0, $this->angka_ini - $this->angka_lalu);
        $this->pemakaian = $pemakaian;

        $standar = min($pemakaian, $tarif->batas_kuota_standar);
        $progresif = max(0, $pemakaian - $tarif->batas_kuota_standar);

        $this->pemakaian_standar = $standar;
        $this->pemakaian_progresif = $progresif;

        $biayaPemakaian = ($standar * $tarif->tarif_standar) + ($progresif * $tarif->tarif_progresif);
        $this->biaya_pemakaian = $biayaPemakaian;
        $this->biaya_admin = $tarif->biaya_admin;

        $this->total_tagihan = $biayaPemakaian + $tarif->biaya_admin + ($this->tunggakan_lalu ?? 0);
        $this->sisa_tagihan = max(0, $this->total_tagihan - ($this->total_dibayar ?? 0));

        if ($this->total_dibayar >= $this->total_tagihan && $this->total_tagihan > 0) {
            $this->status_bayar = 'lunas';
        } elseif ($this->total_dibayar > 0) {
            $this->status_bayar = 'sebagian';
        } else {
            $this->status_bayar = 'belum_bayar';
        }
    }
}
