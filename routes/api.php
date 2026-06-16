<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\ServiceTypeController;
use App\Http\Controllers\Api\ServiceRequestController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ======================
// AUTH JWT
// ======================

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {

    // JWT
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/refresh', [AuthController::class, 'refresh']);
    Route::get('/logout', [AuthController::class, 'logout']);

    // Province (diproteksi token)
    Route::prefix('/province')->group(function () {
        Route::get('',          [ProvinceController::class, 'index']);
        Route::post('',         [ProvinceController::class, 'create']);
        Route::get('/{id}',     [ProvinceController::class, 'detail']);
        Route::put('/{id}',     [ProvinceController::class, 'update']);
        Route::patch('/{id}',   [ProvinceController::class, 'patch']);
        Route::delete('/{id}',  [ProvinceController::class, 'delete']);
    });


    // CITY
    Route::prefix('/city')->group(function () {
        Route::get('', [CityController::class, 'index']);
        Route::get('/province/{province_id}', [CityController::class, 'byProvince']);
        Route::post('', [CityController::class, 'create']);
        Route::get('/{id}', [CityController::class, 'detail']);
        Route::put('/{id}', [CityController::class, 'update']);
        Route::delete('/{id}', [CityController::class, 'delete']);
    });

    // DISTRICT
    Route::prefix('/district')->group(function () {
        Route::get('', [DistrictController::class, 'index']);
        Route::get('/city/{city_id}', [DistrictController::class, 'byCity']);
        Route::post('', [DistrictController::class, 'create']);
        Route::get('/{id}', [DistrictController::class, 'detail']);
        Route::put('/{id}', [DistrictController::class, 'update']);
        Route::delete('/{id}', [DistrictController::class, 'delete']);
    });

    // SERVICE TYPE
    Route::prefix('/service-type')->group(function () {
        Route::get('', [ServiceTypeController::class, 'index']);
        Route::post('', [ServiceTypeController::class, 'create']);
        Route::get('/{id}', [ServiceTypeController::class, 'detail']);
        Route::put('/{id}', [ServiceTypeController::class, 'update']);
        Route::patch('/{id}', [ServiceTypeController::class, 'patch']);
        Route::delete('/{id}', [ServiceTypeController::class, 'delete']);
    });

    // SERVICE REQUEST
    Route::get('/my-service-request', [ServiceRequestController::class, 'myServiceRequest']);

    Route::prefix('/service-request')->group(function () {
        Route::get('', [ServiceRequestController::class, 'index']);
        Route::get('/status/{status}', [ServiceRequestController::class, 'byStatus']);
        Route::get('/user/{user_id}', [ServiceRequestController::class, 'byUser']);
        Route::get('/district/{district_id}', [ServiceRequestController::class, 'byDistrict']);
        Route::post('', [ServiceRequestController::class, 'create']);
        Route::get('/{id}', [ServiceRequestController::class, 'detail']);
        Route::put('/{id}', [ServiceRequestController::class, 'update']);
        Route::patch('/{id}', [ServiceRequestController::class, 'patch']);
        Route::delete('/{id}', [ServiceRequestController::class, 'delete']);
        Route::patch('/{id}/status', [ServiceRequestController::class, 'updateStatus']);
        Route::get('/{id}/history', [ServiceRequestController::class, 'history']);
    });
});
