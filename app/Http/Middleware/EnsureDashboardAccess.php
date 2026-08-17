<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDashboardAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() === null && ! $request->session()->has('client_person_id')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
