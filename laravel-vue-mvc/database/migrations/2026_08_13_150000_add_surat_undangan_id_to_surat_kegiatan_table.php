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
        Schema::table('surat_kegiatan', function (Blueprint $table) {
            $table->foreignId('surat_undangan_id')
                ->nullable()
                ->after('id')
                ->constrained('surat_undangans')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_kegiatan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('surat_undangan_id');
        });
    }
};