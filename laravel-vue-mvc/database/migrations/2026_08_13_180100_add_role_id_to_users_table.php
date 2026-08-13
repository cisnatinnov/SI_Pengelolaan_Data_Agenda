<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')
                ->nullable()
                ->after('email')
                ->constrained('roles')
                ->nullOnDelete();
        });

        // Backfill role_id from the existing role string column.
        $roles = DB::table('roles')->get()->keyBy('slug');
        DB::table('users')->get(['id', 'role'])->each(function ($user) use ($roles) {
            if (isset($roles[$user->role])) {
                DB::table('users')->where('id', $user->id)->update([
                    'role_id' => $roles[$user->role]->id,
                ]);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('staff')->after('email');
        });

        // Restore the role string from the roles table.
        $roles = DB::table('roles')->get()->keyBy('id');
        DB::table('users')->get(['id', 'role_id'])->each(function ($user) use ($roles) {
            if (isset($roles[$user->role_id])) {
                DB::table('users')->where('id', $user->id)->update([
                    'role' => $roles[$user->role_id]->slug,
                ]);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }
};