<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rt;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BendrongPelangganSeeder extends Seeder
{
    /**
     * Seed data pelanggan HIPPAM Dusun Bendrong dari file prd/pelanggan.json
     *
     * Data bersumber dari HIMPAM_BENDRONG.xlsx:
     * - 307 pelanggan dari 13 RT aktif (RT 20-34, kecuali RT 23 & RT 25 kosong)
     * - 4 pelanggan non_rumah_tangga
     * - 6 pelanggan dengan nama ganda
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
            $this->command->error("File prd/pelanggan.json kosong atau format JSON invalid!");
            return;
        }

        $this->command->info("📥 Memulai import data pelanggan Dusun Bendrong...");
        $this->command->info("   Total baris JSON: " . count($data));

        // ====================================================================
        // 1. Buat RT 20-34 (15 RT), termasuk RT 23 & RT 25 yang masih kosong
        // ====================================================================
        $rtKosong = [23, 25]; // RT tanpa data pelanggan
        $rtRange = range(20, 34);
        $rtMap = []; // rt_number => rt_id

        foreach ($rtRange as $rtNum) {
            $kodeRt = 'RT ' . str_pad($rtNum, 2, '0', STR_PAD_LEFT);
            $namaRt = "RT {$rtNum} / RW 05"; // Dusun Bendrong = RW 05 (asumsi)

            $rt = Rt::firstOrCreate(
                ['kode_rt' => $kodeRt],
                [
                    'nama_rt' => $namaRt,
                    'wilayah' => 'Dusun Bendrong',
                    'keterangan' => in_array($rtNum, $rtKosong)
                        ? 'Belum ada data pelanggan — perlu follow-up ke petugas RT'
                        : null,
                ]
            );

            $rtMap[$rtNum] = $rt->id;
        }

        $this->command->info("✅ " . count($rtRange) . " RT berhasil dibuat/diverifikasi (RT 20-34)");
        $this->command->info("   RT kosong (belum ada data): RT " . implode(', RT ', $rtKosong));

        // ====================================================================
        // 2. Import pelanggan dari JSON
        // ====================================================================
        $countTotal = 0;
        $countNonRumahTangga = 0;
        $countNamaGanda = 0;
        $countSkipped = 0;
        $rtProcessed = [];

        DB::beginTransaction();

        try {
            foreach ($data as $row) {
                $rtNum = (int) $row['rt'];
                $noUrut = (int) $row['no_urut'];
                $nama = trim($row['nama']);
                $catatan = $row['catatan'] ?? null;

                // Skip jika RT tidak ada di mapping
                if (!isset($rtMap[$rtNum])) {
                    $countSkipped++;
                    continue;
                }

                // Generate nomor rekening: BDR-{RT}-{no_urut_3digit}
                $noRekening = sprintf('BDR-%d-%03d', $rtNum, $noUrut);

                // Tentukan catatan_nama untuk nama ganda
                $catatanNama = null;
                if ($catatan === 'nama_ganda_atau_keterangan') {
                    $catatanNama = 'Kemungkinan nama alternatif/pasangan — perlu verifikasi admin';
                    $countNamaGanda++;
                }

                // Flag non_rumah_tangga (diisi di keterangan saja, skema tidak punya kolom jenis)
                $isNonRumahTangga = ($catatan === 'non_rumah_tangga');
                if ($isNonRumahTangga) {
                    $countNonRumahTangga++;
                }

                // Cek apakah pelanggan sudah ada (berdasarkan no_rekening)
                $existing = Pelanggan::where('no_rekening', $noRekening)->first();
                if ($existing) {
                    $countSkipped++;
                    continue;
                }

                // Buat pelanggan baru
                Pelanggan::create([
                    'no_rekening'       => $noRekening,
                    'nama'              => $nama,
                    'dusun'             => 'Bendrong',
                    'no_rt'             => (string) $rtNum,
                    'no_rw'             => '05',
                    'alamat'            => Pelanggan::formatAlamat('Bendrong', (string) $rtNum, '05'),
                    'rt_id'             => $rtMap[$rtNum],
                    'no_hp'             => null,
                    'angka_meter_awal'  => 0,
                    'status'            => 'aktif',
                    'urutan_rumah'      => $noUrut,
                ]);

                $countTotal++;
                $rtProcessed[$rtNum] = ($rtProcessed[$rtNum] ?? 0) + 1;
            }

            DB::commit();

            // ====================================================================
            // 3. Ringkasan hasil import
            // ====================================================================
            $this->command->newLine();
            $this->command->info("═══════════════════════════════════════════════");
            $this->command->info("  📊 RINGKASAN IMPORT DATA PELANGGAN BENDRONG");
            $this->command->info("═══════════════════════════════════════════════");
            $this->command->info("  Total pelanggan berhasil diimport : {$countTotal}");
            $this->command->info("  Non-rumah tangga (kandang/SD/KUD) : {$countNonRumahTangga}");
            $this->command->info("  Nama ganda (perlu verifikasi)    : {$countNamaGanda}");
            $this->command->info("  Dilewati (sudah ada)             : {$countSkipped}");
            $this->command->info("  RT diproses                      : " . count($rtProcessed));
            $this->command->newLine();

            $this->command->info("  Distribusi per RT:");
            ksort($rtProcessed);
            foreach ($rtProcessed as $rt => $count) {
                $this->command->info("    RT {$rt}: {$count} pelanggan");
            }

            $this->command->newLine();
            $this->command->info("✅ Import selesai! Total pelanggan di database: " . Pelanggan::count());

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ Import gagal: " . $e->getMessage());
            throw $e;
        }
    }
}
