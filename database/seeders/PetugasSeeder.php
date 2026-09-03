<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Rt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class PetugasSeeder extends Seeder
{
    /**
     * Run the database seeds according to PRD §3.2.
     * Membuat 31 akun petugas RT aktif untuk Desa Argosari (Pateguhan, Gentong, Bendrong).
     * RT 15, 23, 25 tidak dibuatkan akun karena status_data = belum_ada_data.
     */
    public function run(): void
    {
        $this->command->info("👤 Memulai seeding akun Petugas RT Desa Argosari (3 Dusun)...");

        // RT kosong yang tidak dibuatkan akun petugas
        $rtKosong = [15, 23, 25];
        $rtRange = range(1, 34);

        $createdCount = 0;
        $dusunSummary = ['Pateguhan' => [], 'Gentong' => [], 'Bendrong' => []];

        DB::beginTransaction();

        try {
            // Pastikan Admin Utama sudah ada
            User::updateOrCreate(
                ['email' => 'admin@hippam.id'],
                [
                    'name' => 'Administrator HIPPAM',
                    'phone' => '081234567890',
                    'role' => 'admin',
                    'status' => 'active',
                    'is_active' => true,
                    'rt_id' => null,
                    'password' => Hash::make('password'),
                ]
            );

            foreach ($rtRange as $rtNum) {
                // Skip RT kosong (15, 23, 25)
                if (in_array($rtNum, $rtKosong)) {
                    continue;
                }

                $kodeRt = 'RT ' . str_pad($rtNum, 2, '0', STR_PAD_LEFT);
                $rt = Rt::where('kode_rt', $kodeRt)->orWhere('nomor_rt', $rtNum)->first();

                if (!$rt) {
                    if ($rtNum >= 1 && $rtNum <= 12) {
                        $dusun = 'Pateguhan';
                    } elseif ($rtNum >= 13 && $rtNum <= 19) {
                        $dusun = 'Gentong';
                    } else {
                        $dusun = 'Bendrong';
                    }

                    $rt = Rt::create([
                        'nomor_rt' => $rtNum,
                        'kode_rt' => $kodeRt,
                        'nama_rt' => "RT " . str_pad($rtNum, 2, '0', STR_PAD_LEFT),
                        'dusun' => $dusun,
                        'wilayah' => "Dusun {$dusun}",
                        'status_data' => 'lengkap',
                    ]);
                }

                // Format email: petugas{rt}@hippam.local (tanpa leading zero di email, misal: petugas1, petugas13, petugas20)
                $email = "petugas{$rtNum}@hippam.local";
                $name = "Petugas RT " . str_pad($rtNum, 2, '0', STR_PAD_LEFT);

                // Update or create user petugas
                $user = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'role' => 'petugas',
                        'rt_id' => $rt->id,
                        'phone' => null,
                        'status' => 'active',
                        'is_active' => true,
                        'password' => Hash::make('password'),
                        'password_changed_at' => null, // flag: belum ganti password default
                    ]
                );

                // Sinkronisasi pivot table rt_petugas
                $user->rts()->sync([$rt->id]);

                $createdCount++;
                $dusunName = $rt->dusun ?? ($rtNum <= 12 ? 'Pateguhan' : ($rtNum <= 19 ? 'Gentong' : 'Bendrong'));
                $dusunSummary[$dusunName][] = "{$email} ➔ RT " . str_pad($rtNum, 2, '0', STR_PAD_LEFT);
            }

            DB::commit();

            $this->command->newLine();
            $this->command->info("═══════════════════════════════════════════════════════════════");
            $this->command->info("  📊 RINGKASAN SEEDING PETUGAS RT DESA (3 DUSUN)");
            $this->command->info("═══════════════════════════════════════════════════════════════");
            $this->command->info("  Total akun petugas dibuat/diupdate : {$createdCount} akun");
            $this->command->info("  RT tanpa akun (belum ada data)     : RT " . implode(', RT ', $rtKosong));
            $this->command->newLine();

            foreach ($dusunSummary as $dusun => $list) {
                $this->command->info("  Dusun {$dusun} (" . count($list) . " Petugas):");
                foreach ($list as $entry) {
                    $this->command->info("    - {$entry}");
                }
                $this->command->newLine();
            }

            $this->command->info("  Password default: 'password' (di-hash)");
            $this->command->info("✅ PetugasSeeder desa-wide selesai!");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ Seeding gagal: " . $e->getMessage());
            throw $e;
        }
    }
}
