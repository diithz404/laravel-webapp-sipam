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
     * Membuat 13 akun petugas placeholder (RT 20-34 kecuali RT 23 & 25).
     */
    public function run(): void
    {
        $this->command->info("👤 Memulai seeding akun Petugas RT Dusun Bendrong...");

        // RT kosong yang tidak dibuatkan akun petugas
        $rtKosong = [23, 25];
        $rtRange = range(20, 34);

        $createdCount = 0;
        $createdEmails = [];

        DB::beginTransaction();

        try {
            foreach ($rtRange as $rtNum) {
                // Skip RT kosong
                if (in_array($rtNum, $rtKosong)) {
                    continue;
                }

                $kodeRt = 'RT ' . str_pad($rtNum, 2, '0', STR_PAD_LEFT);
                $rt = Rt::where('kode_rt', $kodeRt)->first();

                if (!$rt) {
                    $rt = Rt::create([
                        'kode_rt' => $kodeRt,
                        'nama_rt' => "RT {$rtNum} / RW 05",
                        'wilayah' => 'Dusun Bendrong',
                    ]);
                }

                $email = "petugas{$rtNum}@hippam.local";
                $name = "Petugas RT {$rtNum}";

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

                // Sinkronisasi juga ke pivot table rt_petugas
                $user->rts()->sync([$rt->id]);

                $createdCount++;
                $createdEmails[] = $email . " -> RT " . $rtNum;
            }

            DB::commit();

            $this->command->newLine();
            $this->command->info("═══════════════════════════════════════════════");
            $this->command->info("  📊 RINGKASAN SEEDING PETUGAS RT");
            $this->command->info("═══════════════════════════════════════════════");
            $this->command->info("  Total akun petugas dibuat/diupdate: {$createdCount}");
            $this->command->info("  RT tanpa akun (kosong)            : RT " . implode(', RT ', $rtKosong));
            $this->command->newLine();
            $this->command->info("  Daftar akun petugas:");
            foreach ($createdEmails as $entry) {
                $this->command->info("    - {$entry} (password: password)");
            }
            $this->command->newLine();
            $this->command->info("✅ PetugasSeeder selesai!");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ Seeding gagal: " . $e->getMessage());
            throw $e;
        }
    }
}
