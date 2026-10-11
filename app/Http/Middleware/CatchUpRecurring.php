<?php

namespace App\Http\Middleware;

use App\Services\RecurringPoster;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Writes down any recurring entries that came due since the last visit, so
 * the app doesn't depend on a cron job being set up on the server.
 */
class CatchUpRecurring
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && ($user = $request->user())) {
            RecurringPoster::catchUp($user);
        }

        return $next($request);
    }
}
