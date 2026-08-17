<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() === null) {
            return redirect()->route('dashboard');
        }

        abort_unless($request->user()?->isAdmin(), 403);

        return $next($request);
    }
}
