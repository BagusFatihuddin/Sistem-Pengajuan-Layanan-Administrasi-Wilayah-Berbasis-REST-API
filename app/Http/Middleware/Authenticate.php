<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Auth\AuthenticationException;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * For API requests, return null (handled by exception handler).
     * For web requests, redirect to login page.
     */
    protected function redirectTo($request)
    {
        if ($request->expectsJson()) {
            return null;
        }

        // Untuk web request, redirect ke login page jika ada
        if (auth()->check()) {
            return route('dashboard');
        }

        return null;
    }

    /**
     * Handle unauthenticated user.
     * Override parent to prevent RouteNotFoundException for API.
     */
    protected function unauthenticated($request, array $guards)
    {
        // Untuk API/JSON request, throw exception dengan guard info
        // Exception handler akan convert ke JSON 401
        throw new AuthenticationException('Unauthenticated.', $guards);
    }
}