<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            ['name' => 'Administrator', 'email' => 'test@example.com', 'password' => Hash::make('test123'), 'role' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Muhammad Said', 'email' => 'user@example.com', 'password' => Hash::make('test123'), 'role' => 'user', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
