<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follower;
use Illuminate\Http\Request;
use App\Http\Resources\Api\FollowerResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FollowerController extends Controller
{
  /**
   * Follow a promotor
   */
  public function store(Request $request, User $promotor)
  {
    try {
      // Verify promotor role
      if ($promotor->user_type !== 'promotor') {
        return response()->json([
          'status' => 'error',
          'message' => 'User is not a promotor'
        ], 422);
      }

      // Check if already following
      $existingFollow = Follower::where('user_id', $request->user()->id)
        ->where('promotor_id', $promotor->id)
        ->first();

      if ($existingFollow) {
        return response()->json([
          'status' => 'error',
          'message' => 'Already following this promotor'
        ], 422);
      }

      // Create follow relationship
      $follower = Follower::create([
        'user_id' => $request->user()->id,
        'promotor_id' => $promotor->id
      ]);

      return new FollowerResource($follower);
    } catch (\Exception $e) {
      Log::error('Error in follow promotor: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to follow promotor'
      ], 500);
    }
  }

  /**
   * Unfollow a promotor
   */
  public function destroy(Request $request, User $promotor)
  {
    try {
      $follower = Follower::where('user_id', $request->user()->id)
        ->where('promotor_id', $promotor->id)
        ->first();

      if (!$follower) {
        return response()->json([
          'status' => 'error',
          'message' => 'Not following this promotor'
        ], 404);
      }

      $follower->delete();

      return response()->json([
        'status' => 'success',
        'message' => 'Successfully unfollowed promotor'
      ]);
    } catch (\Exception $e) {
      Log::error('Error in unfollow promotor: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to unfollow promotor'
      ], 500);
    }
  }
}
