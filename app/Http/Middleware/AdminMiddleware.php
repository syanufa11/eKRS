<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        if (! auth()->check()) {
            return redirect('/login');
        }

        if (! session()->has('is_admin')) {
            abort(403);
        }

        return $next($request);
    }
}
