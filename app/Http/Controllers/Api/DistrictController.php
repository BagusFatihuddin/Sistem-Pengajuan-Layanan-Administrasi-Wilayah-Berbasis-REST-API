<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DistrictModel;
use App\Models\CityModel;

use App\Helpers\ApiFormatter;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DistrictController extends Controller
{
    public function index(Request $request)
    {
        $district = DistrictModel::orderBy('district_id', 'ASC')->get();

        $response = ApiFormatter::createJson(
            200,
            'Get Data Success',
            $district
        );

        return response()->json($response);
    }

    public function byCity($city_id)
    {
        $district = DistrictModel::where('city_id', $city_id)
            ->orderBy('district_id', 'ASC')
            ->get();

        $response = ApiFormatter::createJson(
            200,
            'Get Data Success',
            $district
        );

        return response()->json($response);
    }

    public function create(Request $request)
    {
        try {

            $params = $request->all();

            $validator = Validator::make($params,
                [
                    'city_id' => 'required',
                    'code' => 'required|max:10',
                    'name' => 'required',
                ]
            );

            if ($validator->fails()) {

                return ApiFormatter::createJson(
                    400,
                    'Bad Request',
                    $validator->errors()->all()
                );
            }

            $city = CityModel::find($params['city_id']);

            if(is_null($city)){
                return ApiFormatter::createJson(
                    404,
                    'City Not Found'
                );
            }

            $district = [
                'city_id' => $params['city_id'],
                'district_code' => $params['code'],
                'district_name' => $params['name'],
            ];

            $data = DistrictModel::create($district);

            $createdDistrict = DistrictModel::find($data->district_id);

            return ApiFormatter::createJson(
                200,
                'Create District Success',
                $createdDistrict
            );

        } catch (\Exception $e) {

            return ApiFormatter::createJson(
                500,
                'Internal Server Error',
                $e->getMessage()
            );
        }
    }

    public function detail($id)
    {
        try {

            $district = DistrictModel::find($id);

            if(is_null($district)){
                return ApiFormatter::createJson(
                    404,
                    'District Not Found'
                );
            }

            return ApiFormatter::createJson(
                200,
                'Get Detail District Success',
                $district
            );

        } catch (\Exception $e) {

            return ApiFormatter::createJson(
                500,
                'Internal Server Error',
                $e->getMessage()
            );
        }
    }

    public function update(Request $request, $id)
    {
        try {

            $params = $request->all();

            $preDistrict = DistrictModel::find($id);

            if(is_null($preDistrict)){
                return ApiFormatter::createJson(
                    404,
                    'Data Not Found'
                );
            }

            $district = [
                'city_id' => $params['city_id'],
                'district_code' => $params['code'],
                'district_name' => $params['name'],
            ];

            $preDistrict->update($district);

            $updatedDistrict = $preDistrict->fresh();

            return ApiFormatter::createJson(
                200,
                'Update District Success',
                $updatedDistrict
            );

        } catch (\Exception $e) {

            return ApiFormatter::createJson(
                500,
                'Internal Server Error',
                $e->getMessage()
            );
        }
    }

    public function delete($id)
    {
        try {

            $district = DistrictModel::find($id);

            if(is_null($district)){
                return ApiFormatter::createJson(
                    404,
                    'Data Not Found'
                );
            }

            $district->delete();

            return ApiFormatter::createJson(
                200,
                'Delete District Success'
            );

        } catch (\Exception $e) {

            return ApiFormatter::createJson(
                500,
                'Internal Server Error',
                $e->getMessage()
            );
        }
    }
}