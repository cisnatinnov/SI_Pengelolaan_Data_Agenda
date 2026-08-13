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
        Schema::create('surat_undangans', function (Blueprint $table) {
            $table->id();
            $table->dateTime('tanggal');
            $table->string('nomor_surat');
            $table->string('asal_surat');
            $table->string('perihal');
            $table->string('kepada');
            $table->dateTime('tanggal_pelaksanaan');
            $table->string('tempat_pelaksanaan');
            $table->string('pembawa_surat');
            $table->string('tandatangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_undangans');
    }
};