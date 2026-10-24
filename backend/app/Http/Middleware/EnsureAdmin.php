<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user('api')?->role !== 'admin') {
            throw new AuthorizationException('Esta acción requiere privilegios de administrador.');
        }

        return $next($request);
    }
}
