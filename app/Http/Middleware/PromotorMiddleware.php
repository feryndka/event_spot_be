<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PromotorMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle(Request $request, Closure $next): Response
  {
    if (!$request->user() || $request->user()->user_type !== 'promotor') {
      return response()->json([
        'status' => 'error',
        'message' => 'Unauthorized. Promotor access required.'
      ], 403);
    }

    return $next($request);
  }
}
