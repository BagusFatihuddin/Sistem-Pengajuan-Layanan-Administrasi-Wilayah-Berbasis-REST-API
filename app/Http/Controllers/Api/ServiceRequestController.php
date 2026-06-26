<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiFormatter;
use App\Http\Controllers\Controller;
use App\Models\DistrictModel;
use App\Models\ServiceRequestHistoryModel;
use App\Models\ServiceRequestModel;
use App\Models\ServiceTypeModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ServiceRequestController extends Controller
{
    protected array $allowedTransitions = [
        ServiceRequestModel::STATUS_PENDING => [
            ServiceRequestModel::STATUS_PROCESSED,
            ServiceRequestModel::STATUS_CANCELLED,
        ],
        ServiceRequestModel::STATUS_PROCESSED => [
            ServiceRequestModel::STATUS_APPROVED,
            ServiceRequestModel::STATUS_REJECTED,
        ],
    ];

    public function index(Request $request)
    {
        // This is now protected by admin.only middleware in api.php
        $serviceRequest = ServiceRequestModel::orderBy('service_request_id', 'ASC')->get();

        return ApiFormatter::createJson(200, 'Get Data Success', $serviceRequest);
    }

    public function create(Request $request)
    {
        try {
            $params = $request->all();

            $validator = Validator::make($params, [
                'request_number' => 'nullable|max:30|unique:service_request,request_number',
                'service_type_id' => 'required|exists:service_type,service_type_id',
                'district_id' => 'required|exists:district,district_id',
                'applicant_name' => 'required|max:100',
                'applicant_nik' => 'required|digits:16',
                'applicant_phone' => 'required|max:20',
                'applicant_address' => 'required',
                'purpose' => 'required',
                'notes' => 'nullable',
            ]);

            if ($validator->fails()) {
                return ApiFormatter::createJson(400, 'Bad Request', $validator->errors()->all());
            }

            $serviceType = ServiceTypeModel::find($params['service_type_id']);
            if (is_null($serviceType)) {
                return ApiFormatter::createJson(404, 'Service Type Not Found');
            }

            $district = DistrictModel::find($params['district_id']);
            if (is_null($district)) {
                return ApiFormatter::createJson(404, 'District Not Found');
            }

            $userId = Auth::guard('api')->id();
            $submittedAt = Carbon::now();

            $createdServiceRequest = DB::transaction(function () use ($params, $userId, $submittedAt) {
                $data = ServiceRequestModel::create([
                    'request_number' => $params['request_number'] ?? $this->generateRequestNumber(),
                    'user_id' => $userId,
                    'service_type_id' => $params['service_type_id'],
                    'district_id' => $params['district_id'],
                    'applicant_name' => $params['applicant_name'],
                    'applicant_nik' => $params['applicant_nik'],
                    'applicant_phone' => $params['applicant_phone'],
                    'applicant_address' => $params['applicant_address'],
                    'purpose' => $params['purpose'],
                    'status' => ServiceRequestModel::STATUS_PENDING,
                    'notes' => $params['notes'] ?? null,
                    'submitted_at' => $submittedAt,
                ]);

                $this->createHistory(
                    $data->service_request_id,
                    $userId,
                    null,
                    ServiceRequestModel::STATUS_PENDING,
                    'Pengajuan dibuat'
                );

                return ServiceRequestModel::find($data->service_request_id);
            });

            return ApiFormatter::createJson(200, 'Create Service Request Success', $createdServiceRequest);
        } catch (\Exception $e) {
            return ApiFormatter::createJson(500, 'Internal Server Error', $e->getMessage());
        }
    }

    public function detail($id)
    {
        try {
            $serviceRequest = ServiceRequestModel::find($id);

            if (is_null($serviceRequest)) {
                return ApiFormatter::createJson(404, 'Service Request Not Found');
            }

            /** @var User|null $user */
            $user = Auth::guard('api')->user();

            // Check ownership for non-admin users
            if ($user && !$user->isAdmin() && $serviceRequest->user_id !== $user->id) {
                return ApiFormatter::createJson(404, 'Service Request Not Found'); // Return 404 to avoid disclosing existence
            }

            return ApiFormatter::createJson(200, 'Get Detail Service Request Success', $serviceRequest);
        } catch (\Exception $e) {
            return ApiFormatter::createJson(500, 'Internal Server Error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $params = $request->all();

            $preServiceRequest = ServiceRequestModel::find($id);
            if (is_null($preServiceRequest)) {
                return ApiFormatter::createJson(404, 'Data Not Found');
            }

            /** @var User|null $user */
            $user = Auth::guard('api')->user();

            // Check ownership for non-admin users
            if ($user && !$user->isAdmin() && $preServiceRequest->user_id !== $user->id) {
                return ApiFormatter::createJson(404, 'Data Not Found'); // Return 404 to avoid disclosing existence
            }

            if ($this->isFinalRequest($preServiceRequest)) {
                return ApiFormatter::createJson(400, 'Business Rule Violation', ['Final service request cannot be modified']);
            }

            $validator = Validator::make($params, [
                'request_number' => [
                    'required',
                    'max:30',
                    Rule::unique('service_request', 'request_number')->ignore($id, 'service_request_id'),
                ],
                'service_type_id' => 'required|exists:service_type,service_type_id',
                'district_id' => 'required|exists:district,district_id',
                'applicant_name' => 'required|max:100',
                'applicant_nik' => 'required|digits:16',
                'applicant_phone' => 'required|max:20',
                'applicant_address' => 'required',
                'purpose' => 'required',
                'notes' => 'nullable',
            ]);

            if ($validator->fails()) {
                return ApiFormatter::createJson(400, 'Bad Request', $validator->errors()->all());
            }

            $serviceRequest = [
                'request_number' => $params['request_number'],
                'service_type_id' => $params['service_type_id'],
                'district_id' => $params['district_id'],
                'applicant_name' => $params['applicant_name'],
                'applicant_nik' => $params['applicant_nik'],
                'applicant_phone' => $params['applicant_phone'],
                'applicant_address' => $params['applicant_address'],
                'purpose' => $params['purpose'],
                'notes' => $params['notes'] ?? null,
            ];

            $preServiceRequest->update($serviceRequest);
            $updatedServiceRequest = $preServiceRequest->fresh();

            return ApiFormatter::createJson(200, 'Update Service Request Success', $updatedServiceRequest);
        } catch (\Exception $e) {
            return ApiFormatter::createJson(500, 'Internal Server Error', $e->getMessage());
        }
    }

    public function patch(Request $request, $id)
    {
        try {
            $params = $request->all();

            $preServiceRequest = ServiceRequestModel::find($id);
            if (is_null($preServiceRequest)) {
                return ApiFormatter::createJson(404, 'Data Not Found');
            }

            /** @var User|null $user */
            $user = Auth::guard('api')->user();

            // Check ownership for non-admin users
            if ($user && !$user->isAdmin() && $preServiceRequest->user_id !== $user->id) {
                return ApiFormatter::createJson(404, 'Data Not Found'); // Return 404 to avoid disclosing existence
            }

            if ($this->isFinalRequest($preServiceRequest)) {
                return ApiFormatter::createJson(400, 'Business Rule Violation', ['Final service request cannot be modified']);
            }

            $validator = Validator::make($params, [
                'request_number' => [
                    'sometimes',
                    'required',
                    'max:30',
                    Rule::unique('service_request', 'request_number')->ignore($id, 'service_request_id'),
                ],
                'service_type_id' => 'sometimes|required|exists:service_type,service_type_id',
                'district_id' => 'sometimes|required|exists:district,district_id',
                'applicant_name' => 'sometimes|required|max:100',
                'applicant_nik' => 'sometimes|required|digits:16',
                'applicant_phone' => 'sometimes|required|max:20',
                'applicant_address' => 'sometimes|required',
                'purpose' => 'sometimes|required',
                'notes' => 'nullable',
            ]);

            if ($validator->fails()) {
                return ApiFormatter::createJson(400, 'Bad Request', $validator->errors()->all());
            }

            $serviceRequest = [];

            foreach ([
                'request_number',
                'service_type_id',
                'district_id',
                'applicant_name',
                'applicant_nik',
                'applicant_phone',
                'applicant_address',
                'purpose',
                'notes',
            ] as $field) {
                if (array_key_exists($field, $params)) {
                    $serviceRequest[$field] = $params[$field];
                }
            }

            if (empty($serviceRequest)) {
                return ApiFormatter::createJson(400, 'Bad Request', ['No data to update']);
            }

            $preServiceRequest->update($serviceRequest);
            $updatedServiceRequest = $preServiceRequest->fresh();

            return ApiFormatter::createJson(200, 'Update Service Request Success', $updatedServiceRequest);
        } catch (\Exception $e) {
            return ApiFormatter::createJson(500, 'Internal Server Error', $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $serviceRequest = ServiceRequestModel::find($id);

            if (is_null($serviceRequest)) {
                return ApiFormatter::createJson(404, 'Data Not Found');
            }

            /** @var User|null $user */
            $user = Auth::guard('api')->user();

            // Check ownership for non-admin users
            if ($user && !$user->isAdmin() && $serviceRequest->user_id !== $user->id) {
                return ApiFormatter::createJson(404, 'Data Not Found'); // Return 404 to avoid disclosing existence
            }

            if ($this->isFinalRequest($serviceRequest)) {
                return ApiFormatter::createJson(400, 'Business Rule Violation', ['Final service request cannot be modified']);
            }

            $serviceRequest->delete();

            return ApiFormatter::createJson(200, 'Delete Service Request Success');
        } catch (\Exception $e) {
            return ApiFormatter::createJson(500, 'Internal Server Error', $e->getMessage());
        }
    }

    public function byStatus($status)
    {
        // This is protected by admin.only middleware in api.php
        if (!in_array($status, ServiceRequestModel::validStatuses())) {
            return ApiFormatter::createJson(400, 'Invalid Status', ['Status is not valid']);
        }

        $serviceRequest = ServiceRequestModel::where('status', $status)
            ->orderBy('service_request_id', 'ASC')
            ->get();

        return ApiFormatter::createJson(200, 'Get Data Success', $serviceRequest);
    }

    public function byUser($user_id)
    {
        // This is protected by admin.only middleware in api.php
        $user = User::find($user_id);

        if (is_null($user)) {
            return ApiFormatter::createJson(404, 'User Not Found');
        }

        $serviceRequest = ServiceRequestModel::where('user_id', $user_id)
            ->orderBy('service_request_id', 'ASC')
            ->get();

        return ApiFormatter::createJson(200, 'Get Data Success', $serviceRequest);
    }

    public function byDistrict($district_id)
    {
        // This is protected by admin.only middleware in api.php
        $district = DistrictModel::find($district_id);

        if (is_null($district)) {
            return ApiFormatter::createJson(404, 'District Not Found');
        }

        $serviceRequest = ServiceRequestModel::where('district_id', $district_id)
            ->orderBy('service_request_id', 'ASC')
            ->get();

        return ApiFormatter::createJson(200, 'Get Data Success', $serviceRequest);
    }

    public function myServiceRequest()
    {
        // This already returns requests only for the logged-in user
        $serviceRequest = ServiceRequestModel::where('user_id', Auth::guard('api')->id())
            ->orderBy('service_request_id', 'ASC')
            ->get();

        return ApiFormatter::createJson(200, 'Get Data Success', $serviceRequest);
    }

    public function updateStatus(Request $request, $id)
    {
        // This is protected by admin.only middleware in api.php
        try {
            $params = $request->all();

            $serviceRequest = ServiceRequestModel::find($id);
            if (is_null($serviceRequest)) {
                return ApiFormatter::createJson(404, 'Service Request Not Found');
            }

            $validator = Validator::make($params, [
                'status' => ['required', Rule::in(ServiceRequestModel::validStatuses())],
                'notes' => 'nullable',
            ]);

            if ($validator->fails()) {
                return ApiFormatter::createJson(400, 'Bad Request', $validator->errors()->all());
            }

            if (!$this->canChangeStatus($serviceRequest->status, $params['status'])) {
                return ApiFormatter::createJson(400, 'Business Rule Violation', ['Invalid status transition']);
            }

            $previousStatus = $serviceRequest->status;
            $newStatus = $params['status'];
            $notes = $params['notes'] ?? null;
            $userId = Auth::guard('api')->id();

            $updatedServiceRequest = DB::transaction(function () use ($serviceRequest, $previousStatus, $newStatus, $notes, $userId) {
                $serviceRequest->update([
                    'status' => $newStatus,
                    'notes' => $notes,
                ]);

                $this->createHistory(
                    $serviceRequest->service_request_id,
                    $userId,
                    $previousStatus,
                    $newStatus,
                    $notes
                );

                return $serviceRequest->fresh();
            });

            return ApiFormatter::createJson(200, 'Update Service Request Status Success', $updatedServiceRequest);
        } catch (\Exception $e) {
            return ApiFormatter::createJson(500, 'Internal Server Error', $e->getMessage());
        }
    }

    public function history($id)
    {
        $serviceRequest = ServiceRequestModel::find($id);

        if (is_null($serviceRequest)) {
            return ApiFormatter::createJson(404, 'Service Request Not Found');
        }

        /** @var User|null $user */
        $user = Auth::guard('api')->user();

        // Admin can see all history. Regular user can only see their own request history.
        if ($user && !$user->isAdmin() && $serviceRequest->user_id !== $user->id) {
            return ApiFormatter::createJson(404, 'Service Request Not Found'); // Return 404 to avoid disclosing existence
        }

        $history = ServiceRequestHistoryModel::where('service_request_id', $id)
            ->orderBy('history_id', 'ASC')
            ->get();

        return ApiFormatter::createJson(200, 'Get Service Request History Success', $history);
    }

    protected function generateRequestNumber(): string
    {
        do {
            $requestNumber = 'SRV-' . Carbon::now()->format('YmdHis') . '-' . random_int(100, 999);
        } while (ServiceRequestModel::where('request_number', $requestNumber)->exists());

        return $requestNumber;
    }

    protected function createHistory($serviceRequestId, $userId, $previousStatus, $newStatus, $notes = null): void
    {
        ServiceRequestHistoryModel::create([
            'service_request_id' => $serviceRequestId,
            'user_id' => $userId,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'notes' => $notes,
            'changed_at' => Carbon::now(),
        ]);
    }

    protected function isFinalRequest(ServiceRequestModel $serviceRequest): bool
    {
        return in_array($serviceRequest->status, ServiceRequestModel::finalStatuses());
    }

    protected function canChangeStatus($previousStatus, $newStatus): bool
    {
        return in_array($newStatus, $this->allowedTransitions[$previousStatus] ?? []);
    }
}
