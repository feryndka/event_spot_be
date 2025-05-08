<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
  public function handle(Request $request, Closure $next): Response
  {
    if ($request->user() && $request->user()->user_type === 'admin') {
      return $next($request);
    }

    return response()->json([
      'message' => 'Unauthorized. Admin access required.',
      'status' => 'error'
    ], 403);
  }
}
