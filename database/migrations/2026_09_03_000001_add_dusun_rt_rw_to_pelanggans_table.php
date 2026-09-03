<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggans', function (Blueprint $table) {
            if (!Schema::hasColumn('pelanggans', 'dusun')) {
                $table->string('dusun')->nullable()->after('nama');
            }
            if (!Schema::hasColumn('pelanggans', 'no_rt')) {
                $table->string('no_rt', 10)->nullable()->after('dusun');
            }
            if (!Schema::hasColumn('pelanggans', 'no_rw')) {
                $table->string('no_rw', 10)->nullable()->after('no_rt');
            }
            if (Schema::hasColumn('pelanggans', 'rt')) {
                $table->dropColumn('rt');
            }
            if (Schema::hasColumn('pelanggans', 'rw')) {
                $table->dropColumn('rw');
            }
        });

        // Migrate existing records
        $pelanggans = DB::table('pelanggans')->get();
        foreach ($pelanggans as $p) {
            $alamat = $p->alamat ?? '';
            $dusun = $p->dusun ?? null;
            $no_rt = $p->no_rt ?? null;
            $no_rw = $p->no_rw ?? null;

            if (empty($dusun) && preg_match('/(?:Dusun\s+)?([^,]+?)(?=,\s*RT|\s+RT|$)/i', $alamat, $m)) {
                $val = trim($m[1]);
                if (!preg_match('/^RT\b/i', $val)) {
                    $dusun = $val;
                }
            }
            if (empty($no_rt) && preg_match('/RT\s*[:\.]?\s*(\d+)/i', $alamat, $m)) {
                $no_rt = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            }
            if (empty($no_rw) && preg_match('/RW\s*[:\.]?\s*(\d+)/i', $alamat, $m)) {
                $no_rw = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            }

            // If dusun was not found in alamat, check associated RT
            if (empty($dusun) && !empty($p->rt_id)) {
                $rtObj = DB::table('rts')->where('id', $p->rt_id)->first();
                if ($rtObj && !empty($rtObj->wilayah)) {
                    $dusun = preg_replace('/^Dusun\s+/i', '', trim($rtObj->wilayah));
                }
            }

            DB::table('pelanggans')->where('id', $p->id)->update([
                'dusun' => $dusun,
                'no_rt' => $no_rt,
                'no_rw' => $no_rw,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('pelanggans', function (Blueprint $table) {
            $table->dropColumn(['dusun', 'no_rt', 'no_rw']);
        });
    }
};
