<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarifs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_skema')->default('Tarif Standar HIPPAM');
            $table->decimal('tarif_standar', 10, 2)->default(350); // Rp350 / m3
            $table->integer('batas_kuota_standar')->default(20); // 20 m3
            $table->decimal('tarif_progresif', 10, 2)->default(400); // Rp400 / m3
            $table->decimal('biaya_admin', 10, 2)->default(2000); // Rp2.000
            $table->date('tanggal_berlaku');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('tarif_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarif_id')->constrained('tarifs')->onDelete('cascade');
            $table->integer('urutan')->default(1);
            $table->integer('batas_bawah')->default(0); // mis. 0
            $table->integer('batas_atas')->nullable(); // mis. 20, null jika tak terhingga
            $table->decimal('harga_per_m3', 10, 2); // mis. 350
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarif_tiers');
        Schema::dropIfExists('tarifs');
    }
};
