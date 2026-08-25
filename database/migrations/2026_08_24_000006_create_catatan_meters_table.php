<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catatan_meters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')->constrained('pelanggans')->onDelete('cascade');
            $table->foreignId('periode_id')->constrained('periode_tagihans')->onDelete('cascade');
            
            // Angka meter & pemakaian
            $table->integer('angka_lalu')->default(0);
            $table->integer('angka_ini')->nullable();
            $table->integer('pemakaian')->default(0);
            $table->integer('pemakaian_standar')->default(0);
            $table->integer('pemakaian_progresif')->default(0);

            // Snapshot tarif saat perhitungan
            $table->foreignId('tarif_id')->nullable()->constrained('tarifs')->onDelete('set null');
            $table->decimal('snapshot_tarif_standar', 10, 2)->default(350);
            $table->decimal('snapshot_tarif_progresif', 10, 2)->default(400);
            $table->integer('snapshot_kuota_standar')->default(20);
            $table->decimal('snapshot_biaya_admin', 10, 2)->default(2000);

            // Rincian rupiah
            $table->decimal('biaya_pemakaian', 10, 2)->default(0);
            $table->decimal('biaya_admin', 10, 2)->default(2000);
            $table->decimal('tunggakan_lalu', 10, 2)->default(0);
            $table->decimal('total_tagihan', 10, 2)->default(0);

            // Status transaksi
            $table->string('status_meter')->default('draft'); // 'draft', 'tercatat', 'terkunci'
            $table->string('status_bayar')->default('belum_bayar'); // 'belum_bayar', 'sebagian', 'lunas'
            $table->decimal('total_dibayar', 10, 2)->default(0);
            $table->decimal('sisa_tagihan', 10, 2)->default(0);

            // Audit
            $table->foreignId('input_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('input_at')->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->unique(['pelanggan_id', 'periode_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catatan_meters');
    }
};
