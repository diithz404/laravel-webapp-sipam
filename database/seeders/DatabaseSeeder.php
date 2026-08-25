<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Rt;
use App\Models\Pelanggan;
use App\Models\Tarif;
use App\Models\TarifTier;
use App\Models\PeriodeTagihan;
use App\Models\CatatanMeter;
use App\Models\Pembayaran;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Users: 1 Super Admin & 1 Petugas RT
        $admin = User::create([
            'name' => 'Haji Sugianto (Ketua HIPPAM)',
            'email' => 'admin@hippam.id',
            'phone' => '081234567890',
            'role' => 'admin',
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);

        $petugas = User::create([
            'name' => 'Pak Saiful (Petugas RT 01)',
            'email' => 'petugas@hippam.id',
            'phone' => '081234567891',
            'role' => 'petugas',
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);

        // 2. Create RT: RT 01 (Dusun Argosari)
        $rt1 = Rt::create([
            'kode_rt' => 'RT-01',
            'nama_rt' => 'RT 01 / RW 01',
            'wilayah' => 'Dusun Argosari',
            'keterangan' => 'Wilayah Dusun Argosari RT 01 / RW 01, Desa Argosari',
        ]);
        $rt1->petugas()->attach($petugas->id);

        // 3. Create Master Tarif HIPPAM Tirto Makmur
        $tarif = Tarif::create([
            'nama_skema' => 'Tarif HIPPAM Tirto Makmur 2026',
            'tarif_standar' => 350.00,
            'batas_kuota_standar' => 20,
            'tarif_progresif' => 400.00,
            'biaya_admin' => 2000.00,
            'tanggal_berlaku' => '2026-01-01',
            'is_active' => true,
            'created_by' => $admin->id,
            'keterangan' => 'Sesuai ketetapan Musyawarah Desa Argosari Tahun 2026',
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

        // 4. Create Periods: Juli 2026 (Ditutup - baseline meter lalu) & Agustus 2026 (Aktif - belum diinput)
        $periodeJuli = PeriodeTagihan::create([
            'bulan' => 7,
            'tahun' => 2026,
            'nama_periode' => 'Juli 2026',
            'status' => 'ditutup',
            'jatuh_tempo' => '2026-07-25',
            'tanggal_ditutup' => '2026-07-31 23:59:59',
            'closed_by' => $admin->id,
        ]);

        $periodeAgustus = PeriodeTagihan::create([
            'bulan' => 8,
            'tahun' => 2026,
            'nama_periode' => 'Agustus 2026',
            'status' => 'aktif',
            'jatuh_tempo' => '2026-08-25',
        ]);

        // 5. Create Exactly 10 Warga (Customers) in RT 01 (Dusun Argosari)
        $wargaList = [
            ['no' => 'TM-01-001', 'nama' => 'Saiful Anam', 'alamat' => 'Dusun Argosari, RT 01 / RW 01, No. 04', 'meter_awal' => 580, 'meter_juli' => 595, 'hp' => '081234567801'],
            ['no' => 'TM-01-002', 'nama' => 'H. Supardi', 'alamat' => 'Dusun Argosari, RT 01 / RW 01, No. 08', 'meter_awal' => 395, 'meter_juli' => 410, 'hp' => '081234567802'],
            ['no' => 'TM-01-003', 'nama' => 'Siti Khotimah', 'alamat' => 'Dusun Argosari, RT 01 / RW 01, No. 12', 'meter_awal' => 265, 'meter_juli' => 280, 'hp' => '081234567803'],
            ['no' => 'TM-01-004', 'nama' => 'Budi Santoso', 'alamat' => 'Dusun Argosari, RT 01 / RW 01, No. 15', 'meter_awal' => 830, 'meter_juli' => 850, 'hp' => '081234567804'],
            ['no' => 'TM-01-005', 'nama' => 'Nurul Hidayati', 'alamat' => 'Dusun Argosari, RT 01 / RW 01, No. 20', 'meter_awal' => 105, 'meter_juli' => 120, 'hp' => '081234567805'],
            ['no' => 'TM-01-006', 'nama' => 'Dwi Cahyono', 'alamat' => 'Dusun Argosari, RT 01 / RW 01, No. 23', 'meter_awal' => 320, 'meter_juli' => 340, 'hp' => '081234567806'],
            ['no' => 'TM-01-007', 'nama' => 'Sunaryo', 'alamat' => 'Dusun Argosari, RT 01 / RW 01, No. 27', 'meter_awal' => 490, 'meter_juli' => 510, 'hp' => '081234567807'],
            ['no' => 'TM-01-008', 'nama' => 'Agus Priyanto', 'alamat' => 'Dusun Argosari, RT 01 / RW 01, No. 31', 'meter_awal' => 600, 'meter_juli' => 620, 'hp' => '081234567808'],
            ['no' => 'TM-01-009', 'nama' => 'Sri Wahyuni', 'alamat' => 'Dusun Argosari, RT 01 / RW 01, No. 35', 'meter_awal' => 290, 'meter_juli' => 310, 'hp' => '081234567809'],
            ['no' => 'TM-01-010', 'nama' => 'M. Mansyur', 'alamat' => 'Dusun Argosari, RT 01 / RW 01, No. 39', 'meter_awal' => 520, 'meter_juli' => 540, 'hp' => '081234567810'],
        ];

        foreach ($wargaList as $index => $item) {
            $pelanggan = Pelanggan::create([
                'no_rekening' => $item['no'],
                'nama' => $item['nama'],
                'alamat' => $item['alamat'],
                'rt_id' => $rt1->id,
                'no_hp' => $item['hp'],
                'angka_meter_awal' => $item['meter_awal'],
                'status' => 'aktif',
                'urutan_rumah' => $index + 1,
            ]);

            // Catatan Juli (Bulan Lalu - Selesai & Lunas sebagai dasar meter lalu)
            $catatanJuli = CatatanMeter::create([
                'pelanggan_id' => $pelanggan->id,
                'periode_id' => $periodeJuli->id,
                'angka_lalu' => $item['meter_awal'],
                'angka_ini' => $item['meter_juli'],
                'pemakaian' => $item['meter_juli'] - $item['meter_awal'],
                'pemakaian_standar' => min(20, $item['meter_juli'] - $item['meter_awal']),
                'pemakaian_progresif' => max(0, ($item['meter_juli'] - $item['meter_awal']) - 20),
                'tarif_id' => $tarif->id,
                'snapshot_tarif_standar' => 350,
                'snapshot_tarif_progresif' => 400,
                'snapshot_kuota_standar' => 20,
                'snapshot_biaya_admin' => 2000,
                'biaya_pemakaian' => (min(20, $item['meter_juli'] - $item['meter_awal']) * 350),
                'biaya_admin' => 2000,
                'tunggakan_lalu' => 0,
                'total_tagihan' => (min(20, $item['meter_juli'] - $item['meter_awal']) * 350) + 2000,
                'status_meter' => 'terkunci',
                'status_bayar' => 'lunas',
                'total_dibayar' => (min(20, $item['meter_juli'] - $item['meter_awal']) * 350) + 2000,
                'sisa_tagihan' => 0,
                'input_by' => $petugas->id,
                'input_at' => '2026-07-20 09:30:00',
            ]);

            // Catatan Periode Berjalan (Agustus 2026) -> KONDISI BELUM DIINPUT SEMUA METERAN
            CatatanMeter::create([
                'pelanggan_id' => $pelanggan->id,
                'periode_id' => $periodeAgustus->id,
                'angka_lalu' => $item['meter_juli'], // Otomatis tersambung dari bulan lalu
                'angka_ini' => null,                 // Belum diinput
                'pemakaian' => 0,
                'biaya_admin' => $tarif->biaya_admin,
                'tunggakan_lalu' => 0,
                'total_tagihan' => $tarif->biaya_admin,
                'sisa_tagihan' => $tarif->biaya_admin,
                'status_meter' => 'draft',
                'status_bayar' => 'belum_bayar',
                'total_dibayar' => 0,
                'input_by' => null,
                'input_at' => null,
            ]);
        }

        // 6. Activity Logs
        ActivityLog::create([
            'user_id' => $admin->id,
            'aksi' => 'SYSTEM_INIT',
            'deskripsi' => 'Inisialisasi sistem HIPPAM Tirto Makmur dengan 1 Petugas RT dan 10 Warga',
            'ip_address' => '127.0.0.1',
        ]);
    }
}
