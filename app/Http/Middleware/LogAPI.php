<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\LogModel;
use App\Helpers\ApiFormatter;
use Throwable;

class LogAPI
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = null;

        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            $user = null;
        }

        $filteredRequest = ApiFormatter::filterSensitiveData(
            $request->all()
        );

        $log = LogModel::create([
            'user_id' => $user ? $user->id : null,
            'log_method' => $request->method(),
            'log_url' => $request->fullUrl(),
            'log_ip' => $request->ip(),
            'log_request' => json_encode($filteredRequest),
        ]);

        try {

            $response = $next($request);

        $log->update([
            'log_response' => substr($response->getContent(), 0, 5000),
        ]);

            return $response;

        } catch (Throwable $e) {

            $errorResponse = [
                'code' => 500,
                'message' => 'Internal Server Error',
                'data' => $e->getMessage()
            ];

        $log->update([
            'log_response' => substr(json_encode($errorResponse), 0, 5000),
        ]);

            return response()->json($errorResponse, 500);
        }
    }
}