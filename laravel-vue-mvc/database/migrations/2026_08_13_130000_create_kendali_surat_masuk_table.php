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
        Schema::create('kendali_surat_masuk', function (Blueprint $table) {
            $table->id();
            $table->dateTime('tanggal_surat');
            $table->string('perihal');
            $table->string('keterangan')->default('diterima');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kendali_surat_masuk');
    }
};