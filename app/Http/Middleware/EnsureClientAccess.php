<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->session()->has('client_person_id'), 403);

        return $next($request);
    }
}
