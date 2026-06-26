<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LogSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('log')->insert([
            [
                'user_id' => 1,
                'log_method' => 'GET',
                'log_url' => '/api/province',
                'log_ip' => '127.0.0.1',
                'log_request' => json_encode(['page' => 1]),
                'log_response' => json_encode(['code' => 200, 'message' => 'OK']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'log_method' => 'POST',
                'log_url' => '/api/service-request',
                'log_ip' => '127.0.0.1',
                'log_request' => json_encode(['title' => 'Create request']),
                'log_response' => json_encode(['code' => 201, 'message' => 'Created']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'log_method' => 'PATCH',
                'log_url' => '/api/service-request/1/status',
                'log_ip' => '127.0.0.1',
                'log_request' => json_encode(['status' => 'approved']),
                'log_response' => json_encode(['code' => 200, 'message' => 'Updated']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'log_method' => 'DELETE',
                'log_url' => '/api/city/1',
                'log_ip' => '127.0.0.1',
                'log_request' => json_encode([]),
                'log_response' => json_encode(['code' => 200, 'message' => 'Deleted']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'log_method' => 'GET',
                'log_url' => '/api/service-request/1/history',
                'log_ip' => '127.0.0.1',
                'log_request' => json_encode([]),
                'log_response' => json_encode(['code' => 200, 'message' => 'OK']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
