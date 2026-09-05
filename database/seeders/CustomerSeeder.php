<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rt;
use App\Models\Pelanggan;
use App\Models\Tarif;
use App\Models\TarifTier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerSeeder extends Seeder
{
    /**
     * Seed data master RT, Tarif, dan Pelanggan Desa Argosari (3 Dusun: Pateguhan, Gentong, Bendrong)
     * Total: 34 RT (31 terisi, 3 kosong), 648 baris data asli.
     */
    public function run(): void
    {
        $jsonPath = base_path('prd/pelanggan.json');

        if (!file_exists($jsonPath)) {
            $this->command->error("File prd/pelanggan.json tidak ditemukan!");
            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);

        if (empty($data)) {
            $this->command->error("File prd/pelanggan.json kosong atau format JSON tidak valid!");
            return;
        }

        $this->command->info("=========================================================");
        $this->command->info("📥 Memulai Import Data Pelanggan Desa Argosari (3 Dusun)");
        $this->command->info("=========================================================");

        // ====================================================================
        // 1. Buat Master Tarif Classes (Standar & Non-Rumah-Tangga)
        // ====================================================================
        $adminUser = User::where('role', 'admin')->first();
        $adminId = $adminUser?->id;

        $tarifStandar = Tarif::firstOrCreate(
            ['nama_skema' => 'Tarif Standar HIPPAM TIRTO MAKMUR'],
            [
                'tarif_standar' => 350.00,
                'batas_kuota_standar' => 20,
                'tarif_progresif' => 400.00,
                'biaya_admin' => 2000.00,
                'tanggal_berlaku' => now()->startOfYear()->toDateString(),
                'is_active' => true,
                'created_by' => $adminId,
                'keterangan' => 'Skema tarif dasar rumah tangga mengacu ketetapan HIPPAM TIRTO MAKMUR',
            ]
        );

        $tarifMap = [
            'standar' => $tarifStandar->id,
        ];

        // Buat kelas tarif non rumah tangga placeholder
        $nonRtClasses = [
            'Fasilitas Ibadah' => [
                'nama' => 'Non Rumah Tangga - Fasilitas Ibadah',
                'standar' => 350.00,
                'progresif' => 400.00,
                'admin' => 2000.00,
                'ket' => 'Tarif khusus fasilitas ibadah (Masjid / Musholla / Langgar)',
            ],
            'Peternakan/Penampungan' => [
                'nama' => 'Non Rumah Tangga - Peternakan/Penampungan',
                'standar' => 350.00,
                'progresif' => 400.00,
                'admin' => 2000.00,
                'ket' => 'Tarif khusus peternakan / kandang / penampung air',
            ],
            'Fasilitas Pendidikan' => [
                'nama' => 'Non Rumah Tangga - Fasilitas Pendidikan',
                'standar' => 350.00,
                'progresif' => 400.00,
                'admin' => 2000.00,
                'ket' => 'Tarif khusus fasilitas pendidikan (Sekolah SD / TPQ / TK)',
            ],
            'Koperasi/Usaha' => [
                'nama' => 'Non Rumah Tangga - Koperasi/Usaha',
                'standar' => 350.00,
                'progresif' => 400.00,
                'admin' => 2000.00,
                'ket' => 'Tarif khusus koperasi / unit usaha (KUD / Usaha)',
            ],
        ];

        foreach ($nonRtClasses as $subKat => $tData) {
            $tObj = Tarif::firstOrCreate(
                ['nama_skema' => $tData['nama']],
                [
                    'tarif_standar' => $tData['standar'],
                    'batas_kuota_standar' => 20,
                    'tarif_progresif' => $tData['progresif'],
                    'biaya_admin' => $tData['admin'],
                    'tanggal_berlaku' => now()->startOfYear()->toDateString(),
                    'is_active' => true,
                    'created_by' => $adminId,
                    'keterangan' => $tData['ket'],
                ]
            );
            $tarifMap[$subKat] = $tObj->id;
        }

        // ====================================================================
        // 2. Buat Master RT 01 s/d RT 34 Desa-Wide (3 Dusun) - 100% Lengkap
        // ====================================================================
        $rtMap = []; // rt_number => rt_id

        for ($rtNum = 1; $rtNum <= 34; $rtNum++) {
            $kodeRt = 'RT ' . str_pad($rtNum, 2, '0', STR_PAD_LEFT);

            // Tentukan dusun berdasarkan rentang RT desa
            if ($rtNum >= 1 && $rtNum <= 12) {
                $dusun = 'Pateguhan';
                $wilayah = 'Dusun Pateguhan';
            } elseif ($rtNum >= 13 && $rtNum <= 19) {
                $dusun = 'Gentong';
                $wilayah = 'Dusun Gentong';
            } else {
                $dusun = 'Bendrong';
                $wilayah = 'Dusun Bendrong';
            }

            $namaRt = "RT " . str_pad($rtNum, 2, '0', STR_PAD_LEFT);

            $rt = Rt::updateOrCreate(
                ['kode_rt' => $kodeRt],
                [
                    'nomor_rt' => $rtNum,
                    'nama_rt' => $namaRt,
                    'dusun' => $dusun,
                    'wilayah' => $wilayah,
                    'status_data' => 'lengkap',
                    'keterangan' => "Wilayah {$wilayah}",
                ]
            );

            $rtMap[$rtNum] = $rt->id;
        }

        $this->command->info("✅ Seluruh 34 RT berhasil disiapkan (Pateguhan RT 01-12, Gentong RT 13-19, Bendrong RT 20-34) dengan status LENGKAP.");

        // ====================================================================
        // 3. Import Data Pelanggan dari JSON
        // ====================================================================
        $countTotal = 0;
        $countNonRumahTangga = 0;
        $countNamaGanda = 0;
        $countSubKategori = [];
        $dusunCount = ['Pateguhan' => 0, 'Gentong' => 0, 'Bendrong' => 0];

        DB::beginTransaction();

        try {
            foreach ($data as $row) {
                // Skip baris header / invalid
                if (
                    !isset($row['rt']) || 
                    $row['no_urut'] === 'No' || 
                    $row['nama'] === 'Nama' || 
                    empty(trim($row['nama']))
                ) {
                    continue;
                }

                $rtNum = (int) $row['rt'];
                $noUrut = (int) $row['no_urut'];
                $nama = trim($row['nama']);
                $dusun = trim($row['dusun'] ?? '');
                $catatan = $row['catatan'] ?? null;
                $subKategori = $row['sub_kategori'] ?? null;

                if (!isset($rtMap[$rtNum])) {
                    continue;
                }

                // Format nomor pelanggan desa-wide: HPM-{RT 2 digit}-{no_urut 3 digit}
                $noRekening = sprintf('HPM-%02d-%03d', $rtNum, $noUrut);

                // Catatan nama untuk nama ganda
                $catatanNama = null;
                if ($catatan === 'nama_ganda_atau_keterangan') {
                    $catatanNama = 'Kemungkinan nama alternatif/pasangan — perlu verifikasi admin';
                    $countNamaGanda++;
                }

                // Tentukan jenis pelanggan & sub-kategori
                $isNonRt = ($catatan === 'non_rumah_tangga');
                $jenisPelanggan = $isNonRt ? 'non_rumah_tangga' : 'rumah_tangga';
                
                if ($isNonRt) {
                    $countNonRumahTangga++;
                    if ($subKategori) {
                        $countSubKategori[$subKategori] = ($countSubKategori[$subKategori] ?? 0) + 1;
                    }
                }

                $tarifId = ($isNonRt && $subKategori && isset($tarifMap[$subKategori]))
                    ? $tarifMap[$subKategori]
                    : $tarifMap['standar'];

                $rtId = $rtMap[$rtNum];
                $noRtStr = str_pad($rtNum, 2, '0', STR_PAD_LEFT);
                $alamat = "Dusun {$dusun}, RT {$noRtStr}";

                Pelanggan::updateOrCreate(
                    ['no_rekening' => $noRekening],
                    [
                        'nama' => $nama,
                        'catatan_nama' => $catatanNama,
                        'jenis_pelanggan' => $jenisPelanggan,
                        'sub_kategori' => $subKategori,
                        'no_urut_lokal' => $noUrut,
                        'dusun' => $dusun,
                        'no_rt' => $noRtStr,
                        'no_rw' => null,
                        'alamat' => $alamat,
                        'rt_id' => $rtId,
                        'tarif_id' => $tarifId,
                        'no_hp' => null,
                        'angka_meter_awal' => null,
                        'status_setup' => 'belum_lengkap',
                        'status' => 'aktif',
                        'urutan_rumah' => $noUrut,
                    ]
                );

                $countTotal++;
                if (isset($dusunCount[$dusun])) {
                    $dusunCount[$dusun]++;
                }
            }

            DB::commit();

            $this->command->info("=========================================================");
            $this->command->info("🎉 Import Selesai! Ringkasan Data Desa:");
            $this->command->info("   - Total Pelanggan Berhasil Diimpor : {$countTotal}");
            $this->command->info("   - Dusun Pateguhan (RT 01-12)       : {$dusunCount['Pateguhan']} warga");
            $this->command->info("   - Dusun Gentong   (RT 13-19)       : {$dusunCount['Gentong']} warga");
            $this->command->info("   - Dusun Bendrong  (RT 20-34)       : {$dusunCount['Bendrong']} warga");
            $this->command->info("   - Non Rumah Tangga                 : {$countNonRumahTangga} (Ibadah, Peternakan, Pendidikan, Koperasi)");
            foreach ($countSubKategori as $sk => $c) {
                $this->command->info("     * {$sk}: {$c}");
            }
            $this->command->info("   - Nama Ganda / Perlu Verifikasi    : {$countNamaGanda} warga");
            $this->command->info("   - Status Setup Awal                : belum_lengkap ({$countTotal} warga)");
            $this->command->info("=========================================================");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ Gagal mengimpor data pelanggan: " . $e->getMessage());
            Log::error("CustomerSeeder error: " . $e->getMessage());
        }
    }
}
