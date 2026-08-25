<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periode_tagihans', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('bulan'); // 1 - 12
            $table->integer('tahun'); // 2026
            $table->string('nama_periode'); // 'Agustus 2026'
            $table->string('status')->default('aktif'); // 'draft', 'aktif', 'ditutup'
            $table->date('jatuh_tempo')->nullable();
            $table->timestamp('tanggal_ditutup')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['bulan', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periode_tagihans');
    }
};
