<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $allowed = array_map('trim', $roles);
        // support comma-separated single argument
        if (count($allowed) === 1 && str_contains($allowed[0], ',')) {
            $allowed = array_map('trim', explode(',', $allowed[0]));
        }

        $userRole = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;

        if (! in_array($userRole, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }
}
