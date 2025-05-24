<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserMiddleware
{
  public function handle(Request $request, Closure $next)
  {
    if ($request->user()->user_type !== 'user') {
      return response()->json([
        'status' => 'error',
        'message' => 'Only users can access this endpoint'
      ], 403);
    }

    return $next($request);
  }
}
