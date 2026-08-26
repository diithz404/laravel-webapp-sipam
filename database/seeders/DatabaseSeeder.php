<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tarif;
use App\Models\TarifTier;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Super Admin User for production setup
        $admin = User::create([
            'name' => 'Administrator HIPPAM',
            'email' => 'admin@hippam.id',
            'phone' => '081234567890',
            'role' => 'admin',
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);

        // 2. Create Master Skema Tarif Dasar HIPPAM
        $tarif = Tarif::create([
            'nama_skema' => 'Tarif Standar HIPPAM TIRTO MAKMUR',
            'tarif_standar' => 350.00,
            'batas_kuota_standar' => 20,
            'tarif_progresif' => 400.00,
            'biaya_admin' => 2000.00,
            'tanggal_berlaku' => now()->startOfYear()->toDateString(),
            'is_active' => true,
            'created_by' => $admin->id,
            'keterangan' => 'Skema tarif dasar mengacu pada ketetapan musyawarah HIPPAM TIRTO MAKMUR',
        ]);

        TarifTier::create([
            'tarif_id' => $tarif->id,
            'urutan' => 1,
            'batas_bawah' => 0,
            'batas_atas' => 20,
            'harga_per_m3' => 350.00,
        ]);

        TarifTier::create([
            'tarif_id' => $tarif->id,
            'urutan' => 2,
            'batas_bawah' => 20,
            'batas_atas' => null,
            'harga_per_m3' => 400.00,
        ]);

        // 3. Create Current Active Period (e.g. Agustus 2026)
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $bulan = (int) date('n');
        $tahun = (int) date('Y');
        $namaPeriode = ($namaBulan[$bulan] ?? date('F')) . ' ' . $tahun;

        \App\Models\PeriodeTagihan::create([
            'bulan' => $bulan,
            'tahun' => $tahun,
            'nama_periode' => $namaPeriode,
            'status' => 'aktif',
            'jatuh_tempo' => date('Y-m-25'),
        ]);
    }
}
