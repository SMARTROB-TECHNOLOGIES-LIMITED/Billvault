<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

use function App\getSettingsSMTP;
use function App\retErrorSetting;

class SmtpProvider
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $smtp = getSettingsSMTP();

            // Check if any setting is equal to "error"
            if ($smtp == "error") {
                return response()->json(retErrorSetting());
            }

            // Set the configuration dynamically
            Config::set('mail', $smtp);
            // ... set other mail configuration settings
        } catch (\Exception $e) {
            // Log the error or handle it as needed
            // You can customize the response based on your application's needs
            $errorResponse = retErrorSetting();
            return response()->json($errorResponse, 500); // You might want to use a more appropriate HTTP status code
        }
        return $next($request);
    }
}
