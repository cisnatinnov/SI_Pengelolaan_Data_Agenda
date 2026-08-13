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
        Schema::create('surat_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->dateTime('tanggal');
            $table->string('nomor_surat');
            $table->string('asal_surat');
            $table->string('perihal');
            $table->string('kepada');
            $table->string('pembawa_surat');
            $table->string('tandatangan_penerima')->nullable();
            $table->string('tandatangan_dituju')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_kegiatan');
    }
};