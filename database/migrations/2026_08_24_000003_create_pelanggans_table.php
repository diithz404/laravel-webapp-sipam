<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggans', function (Blueprint $table) {
            $table->id();
            $table->string('no_rekening')->unique()->index(); // mis. 'TM-01-001'
            $table->string('nama');
            $table->string('alamat');
            $table->foreignId('rt_id')->constrained('rts')->onDelete('restrict');
            $table->string('no_hp')->nullable();
            $table->integer('angka_meter_awal')->nullable();
            $table->string('status')->default('aktif'); // 'aktif', 'nonaktif'
            $table->integer('urutan_rumah')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggans');
    }
};
