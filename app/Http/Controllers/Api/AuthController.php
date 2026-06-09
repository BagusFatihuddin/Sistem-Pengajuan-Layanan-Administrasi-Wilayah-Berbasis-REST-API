<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $params = $request->all();

            // Validasi input
            $validator = Validator::make($params, [
                'email' => 'required|email',
                'password' => 'required|min:6'
            ], [
                'email.required' => 'Email is required',
                'email.email' => 'Email must be a valid email address',
                'password.required' => 'Password is required',
                'password.min' => 'Password must be at least 6 characters',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'message' => 'Bad Request',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Cari user berdasarkan email
            $user = User::where('email', $params['email'])->first();

            if (!$user) {
                return response()->json([
                    'code' => 404,
                    'message' => 'Account not found'
                ], 404);
            }

            // Verifikasi password
            if (!Hash::check($params['password'], $user->password)) {
                return response()->json([
                    'code' => 401,
                    'message' => 'Password does not match'
                ], 401);
            }

            // Generate token JWT
            if (!$token = JWTAuth::fromUser($user)) {
                return response()->json([
                    'code' => 500,
                    'message' => 'Failed to generate token'
                ], 500);
            }

            // Informasi token
            $currentDateTime = Carbon::now();
            $expirationDateTime = $currentDateTime->addSeconds(JWTAuth::factory()->getTTL() * 60);

            $info = [
                'type' => 'Bearer',
                'token' => $token,
                'expires' => $expirationDateTime->format('Y-m-d H:i:s')
            ];

            return response()->json([
                'code' => 200,
                'message' => 'Login successful',
                'data' => $info
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function me()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $token = JWTAuth::getToken();
        $payload = JWTAuth::getPayload($token);

        $expiration = $payload->get('exp');
        $expiration_time = date('Y-m-d H:i:s', $expiration);

        $data = [
            'name' => $user['name'],
            'email' => $user['email'],
            'exp' => $expiration_time
        ];

        return response()->json([
            'code' => 200,
            'message' => 'Logged in User',
            'data' => $data
        ], 200);
    }

public function refresh()
{
    try {

        $token = JWTAuth::getToken();

        if (!$token) {
            return response()->json([
                'code' => 401,
                'message' => 'Token not provided'
            ], 401);
        }

        $newToken = JWTAuth::refresh($token);

        $currentDateTime = Carbon::now();
        $expirationDateTime = $currentDateTime->addSeconds(
            JWTAuth::factory()->getTTL() * 60
        );

        $info = [
            'type' => 'Bearer',
            'token' => $newToken,
            'expires' => $expirationDateTime->format('Y-m-d H:i:s')
        ];

        return response()->json([
            'code' => 200,
            'message' => 'Successfully refreshed',
            'data' => $info
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'code' => 500,
            'message' => $e->getMessage()
        ], 500);
    }
}

public function logout()
{
    try {

        $token = JWTAuth::getToken();

        if (!$token) {
            return response()->json([
                'code' => 401,
                'message' => 'Token not provided'
            ], 401);
        }

        JWTAuth::invalidate($token);

        return response()->json([
            'code' => 200,
            'message' => 'Successfully logged out'
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'code' => 500,
            'message' => $e->getMessage()
        ], 500);
    }
}
}