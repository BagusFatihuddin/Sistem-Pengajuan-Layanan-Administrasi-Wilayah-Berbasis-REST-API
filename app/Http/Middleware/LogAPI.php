<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAPI
{
    public function handle(Request $request, Closure $next): Response
    {
        try {

            return $next($request);

        } catch (\Throwable $e) {

            return response()->json([
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
}