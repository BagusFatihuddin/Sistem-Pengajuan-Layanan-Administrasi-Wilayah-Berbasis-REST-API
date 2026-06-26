<?php

namespace App\Http\Middleware;

use App\Helpers\ApiFormatter;
use App\Models\LogModel;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LogAPI
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);

            $this->storeLog($request, $response);

            return $response;

        } catch (Throwable $e) {
            if ($e instanceof NotFoundHttpException) {
                throw $e;
            }

            $this->storeLog($request, null, $e);

            throw $e;
        }
    }

    protected function storeLog(Request $request, ?Response $response, ?Throwable $exception = null): void
    {
        try {
            $user = Auth::guard('api')->user();

            LogModel::create([
                'user_id' => $user ? $user->id : null,
                'log_method' => $request->method(),
                'log_url' => $request->fullUrl(),
                'log_ip' => $request->ip(),
                'log_request' => json_encode(ApiFormatter::filterSensitiveData($request->all())),
                'log_response' => json_encode($exception ? [
                    'code' => 500,
                    'message' => $exception->getMessage(),
                    'exception' => get_class($exception),
                ] : $this->serializeResponse($response)),
            ]);
        } catch (Throwable $ignored) {
            // Jangan gagalkan request utama kalau pencatatan log bermasalah.
        }
    }

    protected function serializeResponse(?Response $response): array|string|null
    {
        if (!$response) {
            return null;
        }

        $content = $response->getContent();
        $decoded = json_decode($content, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $content;
    }
}