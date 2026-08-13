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
        Schema::rename('surat_undangans', 'surat');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('surat', 'surat_undangans');
    }
};
