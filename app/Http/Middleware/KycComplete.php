<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class KycComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (empty(Auth::user()->complete)) {
            return response()->json([
                'status'=> 'false',
                'data' => [
                    'message'=> 'Account setup incomplete'
                ]
            ],403);
        }
        return $next($request);
    }
}
