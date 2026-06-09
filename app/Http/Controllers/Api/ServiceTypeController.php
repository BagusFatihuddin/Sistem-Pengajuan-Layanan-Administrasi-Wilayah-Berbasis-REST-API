<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiFormatter;
use App\Http\Controllers\Controller;
use App\Models\ServiceTypeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ServiceTypeController extends Controller
{
    public function index(Request $request)
    {
        $serviceType = ServiceTypeModel::orderBy('service_type_id', 'ASC')->get();

        return ApiFormatter::createJson(200, 'Get Data Success', $serviceType);
    }

    public function create(Request $request)
    {
        try {
            $params = $request->all();

            $validator = Validator::make($params, [
                'service_code' => 'required|max:20|unique:service_type,service_code',
                'service_name' => 'required|max:100',
                'description' => 'nullable',
                'is_active' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return ApiFormatter::createJson(400, 'Bad Request', $validator->errors()->all());
            }

            $serviceType = [
                'service_code' => $params['service_code'],
                'service_name' => $params['service_name'],
                'description' => $params['description'] ?? null,
                'is_active' => $params['is_active'] ?? true,
            ];

            $data = ServiceTypeModel::create($serviceType);
            $createdServiceType = ServiceTypeModel::find($data->service_type_id);

            return ApiFormatter::createJson(200, 'Create Service Type Success', $createdServiceType);
        } catch (\Exception $e) {
            return ApiFormatter::createJson(500, 'Internal Server Error', $e->getMessage());
        }
    }

    public function detail($id)
    {
        try {
            $serviceType = ServiceTypeModel::find($id);

            if (is_null($serviceType)) {
                return ApiFormatter::createJson(404, 'Service Type Not Found');
            }

            return ApiFormatter::createJson(200, 'Get Detail Service Type Success', $serviceType);
        } catch (\Exception $e) {
            return ApiFormatter::createJson(500, 'Internal Server Error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $params = $request->all();

            $preServiceType = ServiceTypeModel::find($id);
            if (is_null($preServiceType)) {
                return ApiFormatter::createJson(404, 'Data Not Found');
            }

            $validator = Validator::make($params, [
                'service_code' => [
                    'required',
                    'max:20',
                    Rule::unique('service_type', 'service_code')->ignore($id, 'service_type_id'),
                ],
                'service_name' => 'required|max:100',
                'description' => 'nullable',
                'is_active' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return ApiFormatter::createJson(400, 'Bad Request', $validator->errors()->all());
            }

            $serviceType = [
                'service_code' => $params['service_code'],
                'service_name' => $params['service_name'],
                'description' => $params['description'] ?? null,
                'is_active' => $params['is_active'] ?? $preServiceType->is_active,
            ];

            $preServiceType->update($serviceType);
            $updatedServiceType = $preServiceType->fresh();

            return ApiFormatter::createJson(200, 'Update Service Type Success', $updatedServiceType);
        } catch (\Exception $e) {
            return ApiFormatter::createJson(500, 'Internal Server Error', $e->getMessage());
        }
    }

    public function patch(Request $request, $id)
    {
        try {
            $params = $request->all();

            $preServiceType = ServiceTypeModel::find($id);
            if (is_null($preServiceType)) {
                return ApiFormatter::createJson(404, 'Data Not Found');
            }

            $validator = Validator::make($params, [
                'service_code' => [
                    'sometimes',
                    'required',
                    'max:20',
                    Rule::unique('service_type', 'service_code')->ignore($id, 'service_type_id'),
                ],
                'service_name' => 'sometimes|required|max:100',
                'description' => 'nullable',
                'is_active' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return ApiFormatter::createJson(400, 'Bad Request', $validator->errors()->all());
            }

            $serviceType = [];

            foreach (['service_code', 'service_name', 'description', 'is_active'] as $field) {
                if (array_key_exists($field, $params)) {
                    $serviceType[$field] = $params[$field];
                }
            }

            if (empty($serviceType)) {
                return ApiFormatter::createJson(400, 'Bad Request', ['No data to update']);
            }

            $preServiceType->update($serviceType);
            $updatedServiceType = $preServiceType->fresh();

            return ApiFormatter::createJson(200, 'Update Service Type Success', $updatedServiceType);
        } catch (\Exception $e) {
            return ApiFormatter::createJson(500, 'Internal Server Error', $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $serviceType = ServiceTypeModel::find($id);

            if (is_null($serviceType)) {
                return ApiFormatter::createJson(404, 'Data Not Found');
            }

            $serviceType->delete();

            return ApiFormatter::createJson(200, 'Delete Service Type Success');
        } catch (\Exception $e) {
            return ApiFormatter::createJson(500, 'Internal Server Error', $e->getMessage());
        }
    }
}
