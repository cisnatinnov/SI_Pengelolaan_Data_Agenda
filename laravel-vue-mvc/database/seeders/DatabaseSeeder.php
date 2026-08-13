<?php

namespace Database\Seeders;

use App\Models\Kegiatan;
use App\Models\KendaliSuratMasuk;
use App\Models\SuratKegiatan;
use App\Models\SuratUndangan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Empty all tables.
        Schema::disableForeignKeyConstraints();
        SuratKegiatan::truncate();
        SuratUndangan::truncate();
        KendaliSuratMasuk::truncate();
        Kegiatan::truncate();
        User::truncate();
        DB::table('sessions')->truncate();
        Schema::enableForeignKeyConstraints();

        // Create one user per role.
        $users = [
            ['name' => 'Admin', 'email' => 'admin@example.com', 'role' => 'admin'],
            ['name' => 'Staff', 'email' => 'staff@example.com', 'role' => 'staff'],
            ['name' => 'Asisten Daerah', 'email' => 'asisten@example.com', 'role' => 'asisten_daerah'],
            ['name' => 'OPD', 'email' => 'opd@example.com', 'role' => 'opd'],
        ];

        foreach ($users as $user) {
            User::create([
                ...$user,
                'password' => Hash::make('password'),
            ]);
        }
    }
}
