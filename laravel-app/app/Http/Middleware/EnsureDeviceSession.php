<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeviceSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('device_id')) {
            return redirect()->route('login.form')->withErrors(['device_id' => 'Session device tidak valid.']);
        }

        return $next($request);
    }
}
