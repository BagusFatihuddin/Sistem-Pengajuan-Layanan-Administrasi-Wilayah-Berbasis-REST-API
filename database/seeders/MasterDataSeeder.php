<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $provinceIds = DB::table('province')->insertGetId(['province_code' => 'NTB', 'province_name' => 'Nusa Tenggara Barat']);
        $provinceBaliId = DB::table('province')->insertGetId(['province_code' => 'BALI', 'province_name' => 'Bali']);

        $cityIds = [
            'Mataram' => DB::table('city')->insertGetId(['province_id' => $provinceIds, 'city_code' => 'MATARAM', 'city_name' => 'Mataram']),
            'Lombok Barat' => DB::table('city')->insertGetId(['province_id' => $provinceIds, 'city_code' => 'LOBAR', 'city_name' => 'Lombok Barat']),
            'Denpasar' => DB::table('city')->insertGetId(['province_id' => $provinceBaliId, 'city_code' => 'DENPASAR', 'city_name' => 'Denpasar']),
            'Badung' => DB::table('city')->insertGetId(['province_id' => $provinceBaliId, 'city_code' => 'BADUNG', 'city_name' => 'Badung']),
        ];

        DB::table('district')->insert([
            ['city_id' => $cityIds['Mataram'], 'district_code' => 'SELAPARANG', 'district_name' => 'Selaparang'],
            ['city_id' => $cityIds['Mataram'], 'district_code' => 'CAKRANEGARA', 'district_name' => 'Cakranegara'],
            ['city_id' => $cityIds['Lombok Barat'], 'district_code' => 'GUNUNGSARI', 'district_name' => 'Gunungsari'],
            ['city_id' => $cityIds['Denpasar'], 'district_code' => 'DENBAR', 'district_name' => 'Denpasar Barat'],
            ['city_id' => $cityIds['Badung'], 'district_code' => 'KUTA', 'district_name' => 'Kuta'],
            ['city_id' => $cityIds['Badung'], 'district_code' => 'ABIANSEMAL', 'district_name' => 'Abiansemal'],
        ]);

        DB::table('service_type')->insert([
            ['service_code' => 'SKD', 'service_name' => 'Surat Keterangan Domisili', 'description' => 'Layanan surat domisili', 'is_active' => true],
            ['service_code' => 'SKU', 'service_name' => 'Surat Keterangan Usaha', 'description' => 'Layanan surat usaha', 'is_active' => true],
            ['service_code' => 'SP', 'service_name' => 'Surat Pengantar', 'description' => 'Layanan surat pengantar', 'is_active' => true],
            ['service_code' => 'SKTM', 'service_name' => 'Surat Keterangan Tidak Mampu', 'description' => 'Layanan surat tidak mampu', 'is_active' => true],
            ['service_code' => 'SKCK-P', 'service_name' => 'Surat Pengantar SKCK', 'description' => 'Layanan surat skck', 'is_active' => true],
        ]);
    }
}
