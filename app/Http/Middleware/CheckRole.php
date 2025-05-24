<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @param  string  $role
   * @return mixed
   */
  public function handle(Request $request, Closure $next, string $role): Response
  {
    if (!$request->user()) {
      return response()->json([
        'status' => 'error',
        'message' => 'Unauthorized. Please login.'
      ], 401);
    }

    if ($request->user()->user_type !== $role) {
      return response()->json([
        'status' => 'error',
        'message' => 'Unauthorized. Insufficient permissions.'
      ], 403);
    }

    return $next($request);
  }
}
