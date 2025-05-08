<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowerController extends Controller
{
  public function store(Request $request, User $promotor)
  {
    // Check if promotor is verified
    if (!$promotor->isVerifiedPromotor()) {
      return response()->json([
        'message' => 'Cannot follow unverified promotor'
      ], 400);
    }

    // Check if already following
    if ($promotor->followers()->where('user_id', Auth::id())->exists()) {
      return response()->json([
        'message' => 'Already following this promotor'
      ], 400);
    }

    $follower = $promotor->followers()->create([
      'user_id' => Auth::id()
    ]);

    return response()->json($follower, 201);
  }

  public function destroy(User $promotor)
  {
    $follower = $promotor->followers()->where('user_id', Auth::id())->first();

    if (!$follower) {
      return response()->json([
        'message' => 'Not following this promotor'
      ], 404);
    }

    $follower->delete();

    return response()->json(null, 204);
  }
}
