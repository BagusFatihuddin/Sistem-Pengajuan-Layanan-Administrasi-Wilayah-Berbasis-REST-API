<?php

namespace App\Http\Middleware;

use App\Helpers\ApiFormatter;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User; // Import the User model

class AdminOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = Auth::guard('api')->user();

        if (!$user || !$user->isAdmin()) {
            return ApiFormatter::createJson(403, 'Forbidden', ['You do not have administrative access.']);
        }

        return $next($request);
    }
}
