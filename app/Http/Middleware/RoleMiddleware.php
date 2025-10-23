<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        // Vérifie si l'utilisateur connecté عندو نفس الـ role المطلوب
        if (! $request->user() || $request->user()->role !== $role) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return $next($request);
    }
}
