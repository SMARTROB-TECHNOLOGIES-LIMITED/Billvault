<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            // Redirect for API requests
            return route('api.login');
        } else {
            // Redirect for web requests
            $route = route('login');
            // Check if the route contains "admin"
            if (str_contains($request->route()->getName(), 'admin')) {
                $route = $request->expectsJson() ? route('login') : route('admin.login');
            }
            return $route;
        }
    }
}
