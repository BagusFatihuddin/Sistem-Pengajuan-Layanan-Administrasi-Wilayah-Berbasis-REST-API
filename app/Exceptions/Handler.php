<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\LogModel;
use App\Helpers\ApiFormatter;
use Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        // Handle JWT Token Expired
        if ($exception instanceof TokenExpiredException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'code' => 401,
                    'message' => 'Unauthorized',
                    'data' => 'Token has expired'
                ], 401);
            }
        }

        // Handle JWT Token Invalid
        if ($exception instanceof TokenInvalidException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'code' => 401,
                    'message' => 'Unauthorized',
                    'data' => 'Token is invalid'
                ], 401);
            }
        }

        // Handle JWT general exception
        if ($exception instanceof JWTException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'code' => 401,
                    'message' => 'Unauthorized',
                    'data' => 'Token not found or invalid'
                ], 401);
            }
        }

        // Handle Authentication Exception (untuk API)
        // ALWAYS return JSON 401 untuk /api routes, tidak ada redirect
        if ($exception instanceof AuthenticationException) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'code' => 401,
                    'message' => 'Unauthorized',
                    'data' => 'Unauthenticated'
                ], 401);
            }
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {

            $user = null;

            try {
                $user = JWTAuth::parseToken()->authenticate();
            } catch (\Exception $e) {
                $user = null;
            }

            $filteredRequest = ApiFormatter::filterSensitiveData(
                $request->all()
            );

            LogModel::create([
                'user_id' => $user ? $user->id : null,
                'log_method' => $request->method(),
                'log_url' => $request->fullUrl(),
                'log_ip' => $request->ip(),
                'log_request' => json_encode($filteredRequest),
                'log_response' => json_encode([
                    'code' => 404,
                    'message' => 'Not Found',
                    'data' => 'Route not found.'
                ]),
            ]);

            return response()->json([
                'code' => 404,
                'message' => 'Not Found',
                'data' => 'Route not found.'
            ], 404);
        }

        return parent::render($request, $exception);
    }
}