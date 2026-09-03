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
        'catatan_nama',
        'jenis_pelanggan',
        'sub_kategori',
        'no_urut_lokal',
        'dusun',
        'no_rt',
        'no_rw',
        'alamat',
        'rt_id',
        'tarif_id',
        'no_hp',
        'angka_meter_awal',
        'status_setup',
        'status',
        'urutan_rumah',
    ];

    public function tarif(): BelongsTo
    {
        return $this->belongsTo(Tarif::class, 'tarif_id');
    }

    public static function formatAlamat(?string $dusun, ?string $no_rt, ?string $no_rw): string
    {
        $dusunClean = trim((string) $dusun);
        if (!empty($dusunClean)) {
            $prefix = !preg_match('/^dusun\s+/i', $dusunClean) ? 'Dusun ' : '';
            $fullDusun = $prefix . $dusunClean;
        } else {
            $fullDusun = '';
        }

        $rtDigits = preg_replace('/[^0-9]/', '', (string)$no_rt);
        $rwDigits = preg_replace('/[^0-9]/', '', (string)$no_rw);

        $rtClean = !empty($rtDigits) ? str_pad($rtDigits, 2, '0', STR_PAD_LEFT) : trim((string)$no_rt);
        $rwClean = !empty($rwDigits) ? str_pad($rwDigits, 2, '0', STR_PAD_LEFT) : trim((string)$no_rw);

        $parts = [];
        if (!empty($fullDusun)) {
            $parts[] = $fullDusun;
        }
        if (!empty($rtClean) && !empty($rwClean)) {
            $parts[] = "RT {$rtClean} / RW {$rwClean}";
        } elseif (!empty($rtClean)) {
            $parts[] = "RT {$rtClean}";
        } elseif (!empty($rwClean)) {
            $parts[] = "RW {$rwClean}";
        }

        return implode(', ', $parts);
    }

    protected static function booted()
    {
        static::saving(function ($pelanggan) {
            if (!empty($pelanggan->dusun) || !empty($pelanggan->no_rt) || !empty($pelanggan->no_rw)) {
                $pelanggan->alamat = static::formatAlamat($pelanggan->dusun, $pelanggan->no_rt, $pelanggan->no_rw);
            }
        });
    }

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
