<?php

namespace Database\Seeders;

use App\Models\Disposisi;
use App\Models\Kegiatan;
use App\Models\Pengingat;
use App\Models\Role;
use App\Models\Surat;
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
        Disposisi::truncate();
        Surat::truncate();
        Pengingat::truncate();
        Kegiatan::truncate();
        User::truncate();
        Role::truncate();
        DB::table('sessions')->truncate();
        Schema::enableForeignKeyConstraints();

        // Seed roles.
        $roles = [];
        foreach (['admin' => 'Admin', 'staff' => 'Staff', 'asisten_daerah' => 'Asisten Daerah', 'opd' => 'OPD'] as $slug => $name) {
            $roles[$slug] = Role::create(['name' => $name, 'slug' => $slug])->id;
        }

        // Create one user per role.
        $users = [
            ['name' => 'Admin', 'email' => 'admin@example.com', 'role' => 'admin'],
            ['name' => 'Staff', 'email' => 'staff@example.com', 'role' => 'staff'],
            ['name' => 'Asisten Daerah', 'email' => 'asisten@example.com', 'role' => 'asisten_daerah'],
            ['name' => 'OPD', 'email' => 'opd@example.com', 'role' => 'opd'],
        ];

        foreach ($users as $user) {
            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'role_id' => $roles[$user['role']],
                'password' => Hash::make('Password@123'),
            ]);
        }
    }
}
