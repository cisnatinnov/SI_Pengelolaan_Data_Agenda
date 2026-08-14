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
        Schema::rename('surat_kegiatan', 'disposisi');

        Schema::table('disposisi', function (Blueprint $table) {
            $table->dropForeign(['surat_undangan_id']);
            $table->renameColumn('surat_undangan_id', 'surat_id');
        });

        Schema::table('disposisi', function (Blueprint $table) {
            $table->foreign('surat_id')->references('id')->on('surat')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('disposisi', function (Blueprint $table) {
            $table->dropForeign(['surat_id']);
            $table->renameColumn('surat_id', 'surat_undangan_id');
        });

        Schema::table('disposisi', function (Blueprint $table) {
            $table->foreign('surat_undangan_id')->references('id')->on('surat')->nullOnDelete();
        });

        Schema::rename('disposisi', 'surat_kegiatan');
    }
};
