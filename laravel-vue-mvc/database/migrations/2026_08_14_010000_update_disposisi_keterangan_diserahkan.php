<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('disposisi')->where('keterangan', 'disahkan')->update(['keterangan' => 'diserahkan']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('disposisi')->where('keterangan', 'diserahkan')->update(['keterangan' => 'disahkan']);
    }
};