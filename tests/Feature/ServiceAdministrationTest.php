<?php

namespace Tests\Feature;

use App\Models\CityModel;
use App\Models\DistrictModel;
use App\Models\ProvinceModel; // Add this
use App\Models\ServiceRequestHistoryModel;
use App\Models\ServiceRequestModel;
use App\Models\ServiceTypeModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ServiceAdministrationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private string $token;

    private DistrictModel $district;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'QA User',
            'email' => 'qa@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin', // Ensure the user is an admin for these tests
        ]);

        $this->token = JWTAuth::fromUser($this->user);

        // Ensure province exists for foreign key constraint
        $province = ProvinceModel::create([
            'province_code' => 'QA-PROV',
            'province_name' => 'QA Province',
        ]);

        $city = CityModel::create([
            'province_id' => $province->province_id,
            'city_code' => 'QA-CITY',
            'city_name' => 'QA City',
        ]);

        $this->district = DistrictModel::create([
            'city_id' => $city->city_id,
            'district_code' => 'QA-DIST',
            'district_name' => 'QA District',
        ]);
    }

    public function test_success_flow_login_create_service_type_create_request_update_status_and_get_history(): void
    {
        $this->postJson('/api/login', [
            'email' => 'qa@example.com',
            'password' => 'password123',
        ])->assertStatus(200)
            ->assertJsonPath('message', 'Login successful')
            ->assertJsonStructure(['data' => ['type', 'token', 'expires']]);

        $serviceTypeId = $this->authorizedJson('POST', '/api/service-type', [
            'service_code' => 'SKD',
            'service_name' => 'Surat Keterangan Domisili',
            'description' => 'Layanan surat domisili',
            'is_active' => true,
        ])->assertStatus(200)
            ->assertJsonPath('message', 'Create Service Type Success')
            ->json('data.service_type_id');

        $serviceRequestId = $this->authorizedJson('POST', '/api/service-request', [
            'service_type_id' => $serviceTypeId,
            'district_id' => $this->district->district_id,
            'applicant_name' => 'Budi Santoso',
            'applicant_nik' => '5201010101010001',
            'applicant_phone' => '081234567890',
            'applicant_address' => 'Jl. Merdeka No. 10',
            'purpose' => 'Keperluan administrasi kampus',
        ])->assertStatus(200)
            ->assertJsonPath('message', 'Create Service Request Success')
            ->assertJsonPath('data.status', 'pending')
            ->json('data.service_request_id');

        $this->assertDatabaseHas('service_request_history', [
            'service_request_id' => $serviceRequestId,
            'previous_status' => null,
            'new_status' => 'pending',
        ]);

        $this->authorizedJson('PATCH', "/api/service-request/{$serviceRequestId}/status", [
            'status' => 'processed',
            'notes' => 'Pengajuan sedang diproses',
        ])->assertStatus(200)
            ->assertJsonPath('message', 'Update Service Request Status Success')
            ->assertJsonPath('data.status', 'processed');

        $this->authorizedJson('GET', "/api/service-request/{$serviceRequestId}/history")
            ->assertStatus(200)
            ->assertJsonPath('message', 'Get Service Request History Success')
            ->assertJsonCount(2, 'data');
    }

    public function test_failed_flow_validation_unauthorized_duplicate_invalid_fk_status_flow_final_edit_and_not_found(): void
    {
        $this->getJson('/api/service-type')->assertStatus(401);

        $this->withHeader('Authorization', 'Bearer invalid-token')
            ->getJson('/api/service-type')
            ->assertStatus(401);

        $this->authorizedJson('POST', '/api/service-type', [
            'service_code' => 'SKD',
            'service_name' => 'Surat Keterangan Domisili',
        ])->assertStatus(200);

        $this->authorizedJson('POST', '/api/service-type', [
            'service_code' => 'SKD',
            'service_name' => 'Duplikat',
        ])->assertStatus(400)
            ->assertJsonPath('message', 'Bad Request');

        $this->authorizedJson('POST', '/api/service-request', [
            'service_type_id' => 9999,
            'district_id' => $this->district->district_id,
            'applicant_name' => 'Budi Santoso',
            'applicant_nik' => '5201010101010001',
            'applicant_phone' => '081234567890',
            'applicant_address' => 'Jl. Merdeka No. 10',
            'purpose' => 'Keperluan administrasi kampus',
        ])->assertStatus(400)
            ->assertJsonPath('message', 'Bad Request');

        $this->authorizedJson('POST', '/api/service-request', [
            'service_type_id' => 1,
            'district_id' => $this->district->district_id,
            'applicant_name' => 'Budi Santoso',
            'applicant_nik' => '123',
            'applicant_phone' => '081234567890',
            'applicant_address' => 'Jl. Merdeka No. 10',
            'purpose' => 'Keperluan administrasi kampus',
        ])->assertStatus(400)
            ->assertJsonPath('message', 'Bad Request');

        $request = $this->createServiceRequest();

        $this->authorizedJson('PATCH', "/api/service-request/{$request->service_request_id}/status", [
            'status' => 'approved',
        ])->assertStatus(400)
            ->assertJsonPath('message', 'Business Rule Violation');

        $this->authorizedJson('PATCH', "/api/service-request/{$request->service_request_id}/status", [
            'status' => 'invalid',
        ])->assertStatus(400)
            ->assertJsonPath('message', 'Bad Request');

        $this->authorizedJson('PATCH', "/api/service-request/{$request->service_request_id}/status", [
            'status' => 'processed',
        ])->assertStatus(200);

        $this->authorizedJson('PATCH', "/api/service-request/{$request->service_request_id}/status", [
            'status' => 'approved',
        ])->assertStatus(200);

        $this->authorizedJson('PATCH', "/api/service-request/{$request->service_request_id}", [
            'purpose' => 'Tidak boleh diedit',
        ])->assertStatus(400)
            ->assertJsonPath('message', 'Business Rule Violation');

        $this->authorizedJson('DELETE', "/api/service-request/{$request->service_request_id}")
            ->assertStatus(400)
            ->assertJsonPath('message', 'Business Rule Violation');

        $this->authorizedJson('GET', '/api/service-request/9999')
            ->assertStatus(404)
            ->assertJsonPath('message', 'Service Request Not Found');
    }

    public function test_my_service_request_only_returns_logged_in_user_requests(): void
    {
        $otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
            'password' => Hash::make('password123'),
        ]);

        $ownRequest = $this->createServiceRequest();

        $serviceType = ServiceTypeModel::first();

        ServiceRequestModel::create([
            'request_number' => 'SRV-OTHER-001',
            'user_id' => $otherUser->id,
            'service_type_id' => $serviceType->service_type_id,
            'district_id' => $this->district->district_id,
            'applicant_name' => 'Other User',
            'applicant_nik' => '5201010101010002',
            'applicant_phone' => '081234567891',
            'applicant_address' => 'Jl. Lain No. 1',
            'purpose' => 'Request user lain',
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $this->authorizedJson('GET', '/api/my-service-request')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.service_request_id', $ownRequest->service_request_id);
    }

    private function authorizedJson(string $method, string $uri, array $data = [])
    {
        return $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->json($method, $uri, $data);
    }

    private function createServiceRequest(): ServiceRequestModel
    {
        $serviceType = ServiceTypeModel::first() ?? ServiceTypeModel::create([
            'service_code' => 'SKD',
            'service_name' => 'Surat Keterangan Domisili',
        ]);

        $request = ServiceRequestModel::create([
            'request_number' => 'SRV-QA-' . random_int(1000, 9999),
            'user_id' => $this->user->id,
            'service_type_id' => $serviceType->service_type_id,
            'district_id' => $this->district->district_id,
            'applicant_name' => 'Budi Santoso',
            'applicant_nik' => '5201010101010001',
            'applicant_phone' => '081234567890',
            'applicant_address' => 'Jl. Merdeka No. 10',
            'purpose' => 'Keperluan administrasi kampus',
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        ServiceRequestHistoryModel::create([
            'service_request_id' => $request->service_request_id,
            'user_id' => $this->user->id,
            'previous_status' => null,
            'new_status' => 'pending',
            'notes' => 'Pengajuan dibuat',
            'changed_at' => now(),
        ]);

        return $request;
    }
}