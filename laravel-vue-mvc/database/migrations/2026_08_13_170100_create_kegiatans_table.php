<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kegiatans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kegiatan');
            $table->string('tempat_kegiatan');
            $table->dateTime('tanggal_kegiatan');
            $table->text('uraian_kegiatan');
            $table->string('realisasi_pelaksanaan'); // terlaksana | tidak
            $table->text('keterangan')->nullable();
            $table->string('status'); // pelaksanaan | laporan
            $table->string('nama_penyusun')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatans');
    }
};