<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom pada tabel rts
        Schema::table('rts', function (Blueprint $table) {
            if (!Schema::hasColumn('rts', 'nomor_rt')) {
                $table->unsignedTinyInteger('nomor_rt')->nullable()->after('id');
            }
            if (!Schema::hasColumn('rts', 'dusun')) {
                $table->string('dusun')->nullable()->after('nama_rt');
            }
            if (!Schema::hasColumn('rts', 'status_data')) {
                $table->string('status_data')->default('lengkap')->after('dusun'); // 'lengkap', 'belum_ada_data'
            }
        });

        // 2. Tambah kolom pada tabel pelanggans
        Schema::table('pelanggans', function (Blueprint $table) {
            if (!Schema::hasColumn('pelanggans', 'catatan_nama')) {
                $table->string('catatan_nama')->nullable()->after('nama');
            }
            if (!Schema::hasColumn('pelanggans', 'jenis_pelanggan')) {
                $table->string('jenis_pelanggan')->default('rumah_tangga')->after('catatan_nama'); // 'rumah_tangga', 'non_rumah_tangga'
            }
            if (!Schema::hasColumn('pelanggans', 'sub_kategori')) {
                $table->string('sub_kategori')->nullable()->after('jenis_pelanggan');
            }
            if (!Schema::hasColumn('pelanggans', 'no_urut_lokal')) {
                $table->unsignedInteger('no_urut_lokal')->nullable()->after('sub_kategori');
            }
            if (!Schema::hasColumn('pelanggans', 'status_setup')) {
                $table->string('status_setup')->default('belum_lengkap')->after('angka_meter_awal'); // 'belum_lengkap', 'lengkap'
            }
            if (!Schema::hasColumn('pelanggans', 'tarif_id')) {
                $table->foreignId('tarif_id')->nullable()->after('rt_id')->constrained('tarifs')->onDelete('set null');
            }
        });

        // 3. Tambah kolom pada tabel users jika belum ada
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'rt_id')) {
                $table->foreignId('rt_id')->nullable()->after('role')->constrained('rts')->onDelete('set null');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('status');
            }
            if (!Schema::hasColumn('users', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable()->after('password');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'rt_id')) {
                $table->dropForeign(['rt_id']);
                $table->dropColumn('rt_id');
            }
            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('users', 'password_changed_at')) {
                $table->dropColumn('password_changed_at');
            }
        });

        Schema::table('pelanggans', function (Blueprint $table) {
            if (Schema::hasColumn('pelanggans', 'tarif_id')) {
                $table->dropForeign(['tarif_id']);
                $table->dropColumn('tarif_id');
            }
            if (Schema::hasColumn('pelanggans', 'catatan_nama')) {
                $table->dropColumn('catatan_nama');
            }
            if (Schema::hasColumn('pelanggans', 'jenis_pelanggan')) {
                $table->dropColumn('jenis_pelanggan');
            }
            if (Schema::hasColumn('pelanggans', 'sub_kategori')) {
                $table->dropColumn('sub_kategori');
            }
            if (Schema::hasColumn('pelanggans', 'no_urut_lokal')) {
                $table->dropColumn('no_urut_lokal');
            }
            if (Schema::hasColumn('pelanggans', 'status_setup')) {
                $table->dropColumn('status_setup');
            }
        });

        Schema::table('rts', function (Blueprint $table) {
            if (Schema::hasColumn('rts', 'nomor_rt')) {
                $table->dropColumn('nomor_rt');
            }
            if (Schema::hasColumn('rts', 'dusun')) {
                $table->dropColumn('dusun');
            }
            if (Schema::hasColumn('rts', 'status_data')) {
                $table->dropColumn('status_data');
            }
        });
    }
};
