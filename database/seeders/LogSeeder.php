<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LogSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('log')->insert([
            ['method' => 'GET', 'url' => '/api/province', 'user_id' => 1, 'created_at' => now()],
            ['method' => 'POST', 'url' => '/api/service-request', 'user_id' => 2, 'created_at' => now()],
            ['method' => 'PATCH', 'url' => '/api/service-request/1/status', 'user_id' => 1, 'created_at' => now()],
            ['method' => 'DELETE', 'url' => '/api/city/1', 'user_id' => 1, 'created_at' => now()],
            ['method' => 'GET', 'url' => '/api/service-request/1/history', 'user_id' => 2, 'created_at' => now()],
        ]);
    }
}
