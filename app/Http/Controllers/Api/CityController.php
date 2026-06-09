<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CityModel;
use App\Models\ProvinceModel;

use App\Helpers\ApiFormatter;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $city = CityModel::orderBy('city_id', 'ASC')->get();

        $response = ApiFormatter::createJson(200, 'Get Data Success', $city);
        return response()->json($response);
    }

    public function byProvince($province_id)
    {
        $city = CityModel::where('province_id', $province_id)
            ->orderBy('city_id', 'ASC')
            ->get();

        $response = ApiFormatter::createJson(200, 'Get Data Success', $city);
        return response()->json($response);
    }

    public function create(Request $request)
    {
        try {
            $params = $request->all();

            $validator = Validator::make($params,
                [
                    'province_id' => 'required',
                    'code' => 'required|max:10',
                    'name' => 'required',
                ]
            );

            if ($validator->fails()) {
                $response = ApiFormatter::createJson(
                    400,
                    'Bad Request',
                    $validator->errors()->all()
                );

                return response()->json($response);
            }

            $province = ProvinceModel::find($params['province_id']);

            if(is_null($province)){
                return ApiFormatter::createJson(
                    404,
                    'Province Not Found'
                );
            }

            $city = [
                'province_id' => $params['province_id'],
                'city_code' => $params['code'],
                'city_name' => $params['name'],
            ];

            $data = CityModel::create($city);

            $createdCity = CityModel::find($data->city_id);

            $response = ApiFormatter::createJson(
                200,
                'Create City Success',
                $createdCity
            );

            return response()->json($response);

        } catch (\Exception $e) {

            $response = ApiFormatter::createJson(
                500,
                'Internal Server Error',
                $e->getMessage()
            );

            return response()->json($response);
        }
    }

    public function detail($id)
    {
        try {

            $city = CityModel::find($id);

            if(is_null($city)){
                return ApiFormatter::createJson(
                    404,
                    'City Not Found'
                );
            }

            $response = ApiFormatter::createJson(
                200,
                'Get Detail City Success',
                $city
            );

            return response()->json($response);

        } catch (\Exception $e) {

            $response = ApiFormatter::createJson(
                500,
                'Internal Server Error',
                $e->getMessage()
            );

            return response()->json($response);
        }
    }

    public function update(Request $request, $id)
    {
        try {

            $params = $request->all();

            $preCity = CityModel::find($id);

            if(is_null($preCity)){
                return ApiFormatter::createJson(
                    404,
                    'Data Not Found'
                );
            }

            $city = [
                'province_id' => $params['province_id'],
                'city_code' => $params['code'],
                'city_name' => $params['name'],
            ];

            $preCity->update($city);

            $updatedCity = $preCity->fresh();

            $response = ApiFormatter::createJson(
                200,
                'Update City Success',
                $updatedCity
            );

            return response()->json($response);

        } catch (\Exception $e) {

            $response = ApiFormatter::createJson(
                500,
                'Internal Server Error',
                $e->getMessage()
            );

            return response()->json($response);
        }
    }

    public function delete($id)
    {
        try {

            $city = CityModel::find($id);

            if(is_null($city)){
                return ApiFormatter::createJson(
                    404,
                    'Data Not Found'
                );
            }

            $city->delete();

            $response = ApiFormatter::createJson(
                200,
                'Delete City Success'
            );

            return response()->json($response);

        } catch (\Exception $e) {

            $response = ApiFormatter::createJson(
                500,
                'Internal Server Error',
                $e->getMessage()
            );

            return response()->json($response);
        }
    }
}