<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';
    public const ADMIN = '/admin/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) { 
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->json([
                    'message' => 'Rate limit exceeded. Please wait before retrying.',
                    'status' => 429,
                    'retry_after_seconds' => 60,
                ], 429);
            });
        });

        $this->routes(function () {
            Route::middleware(['api','smtpprovider'])
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware(['web','smtpprovider'])
                ->prefix('admin')
                ->namespace($this->namespace)
                ->name('admin')
                ->group(base_path('routes/admin.php'));

            Route::middleware(['web','smtpprovider'])
                ->group(base_path('routes/web.php'));
        });
    }
}
