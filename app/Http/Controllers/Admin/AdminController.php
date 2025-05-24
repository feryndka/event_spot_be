<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Resources\Api\Admin\VerificationResource;
use Illuminate\Support\Facades\Log;
use App\Models\Event;
use App\Models\EventTag;

class AdminController extends Controller
{
  // User Management
  public function users(Request $request)
  {
    try {
      $users = User::with('promotorDetail')
        ->when($request->search, function ($query, $search) {
          $query->where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%");
        })
        ->when($request->user_type, function ($query, $type) {
          $query->where('user_type', $type);
        })
        ->paginate(10);

      return VerificationResource::collection($users);
    } catch (\Exception $e) {
      Log::error('Error in users list: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to load users'
      ], 500);
    }
  }

  public function verifyUser(User $user)
  {
    try {
      $user->update(['is_verified' => true]);
      return new VerificationResource($user);
    } catch (\Exception $e) {
      Log::error('Error in verifying user: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to verify user'
      ], 500);
    }
  }

  public function blockUser(User $user)
  {
    try {
      $user->update(['is_active' => false]);
      return new VerificationResource($user);
    } catch (\Exception $e) {
      Log::error('Error in blocking user: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to block user'
      ], 500);
    }
  }

  public function unblockUser(User $user)
  {
    try {
      $user->update(['is_active' => true]);
      return new VerificationResource($user);
    } catch (\Exception $e) {
      Log::error('Error in unblocking user: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to unblock user'
      ], 500);
    }
  }

  // Dashboard Statistics
  public function dashboard()
  {
    try {
      $totalUsers = User::count();
      $totalEvents = Event::count();
      $totalTags = EventTag::count();
      $totalCategories = Category::count();
      $recentEvents = Event::with(['promotor', 'category'])
        ->latest()
        ->take(5)
        ->get();

      return response()->json([
        'status' => 'success',
        'data' => [
          'total_users' => $totalUsers,
          'total_events' => $totalEvents,
          'total_tags' => $totalTags,
          'total_categories' => $totalCategories,
          'recent_events' => $recentEvents
        ]
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to fetch dashboard data'
      ], 500);
    }
  }
}
