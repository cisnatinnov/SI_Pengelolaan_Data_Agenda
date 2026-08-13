<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('kendali_surat_masuk');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('kendali_surat_masuk', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->dateTime('tanggal_surat');
            $table->string('perihal');
            $table->string('keterangan')->default('diterima');
            $table->timestamps();
        });
    }
};
