<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Event;
use App\Models\Category;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
  // User Management
  public function users(Request $request)
  {
    $users = User::with('promotorDetails')
      ->when($request->search, function ($query, $search) {
        $query->where('name', 'like', "%{$search}%")
          ->orWhere('email', 'like', "%{$search}%");
      })
      ->when($request->user_type, function ($query, $type) {
        $query->where('user_type', $type);
      })
      ->paginate(10);

    return response()->json($users);
  }

  public function verifyUser(Request $request, User $user)
  {
    $user->update(['is_verified' => true]);
    return response()->json(['message' => 'User verified successfully']);
  }

  public function blockUser(Request $request, User $user)
  {
    $user->update(['is_active' => false]);
    return response()->json(['message' => 'User blocked successfully']);
  }

  public function unblockUser(Request $request, User $user)
  {
    $user->update(['is_active' => true]);
    return response()->json(['message' => 'User unblocked successfully']);
  }

  // Event Management
  public function events(Request $request)
  {
    $events = Event::with(['promotor', 'category'])
      ->when($request->search, function ($query, $search) {
        $query->where('title', 'like', "%{$search}%");
      })
      ->when($request->status, function ($query, $status) {
        $query->where('status', $status);
      })
      ->paginate(10);

    return response()->json($events);
  }

  public function approveEvent(Request $request, Event $event)
  {
    $event->update(['status' => 'approved']);
    return response()->json(['message' => 'Event approved successfully']);
  }

  public function rejectEvent(Request $request, Event $event)
  {
    $validator = Validator::make($request->all(), [
      'reason' => 'required|string|max:500'
    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

    $event->update([
      'status' => 'rejected',
      'rejection_reason' => $request->reason
    ]);

    return response()->json(['message' => 'Event rejected successfully']);
  }

  public function deleteEvent(Request $request, Event $event)
  {
    $event->delete();
    return response()->json(['message' => 'Event deleted successfully']);
  }

  // Category Management
  public function categories(Request $request)
  {
    $categories = Category::withCount('events')
      ->when($request->search, function ($query, $search) {
        $query->where('name', 'like', "%{$search}%");
      })
      ->paginate(10);

    return response()->json($categories);
  }

  public function createCategory(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255|unique:categories',
      'description' => 'required|string',
      'icon' => 'nullable|string',
    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

    $category = Category::create($request->all());
    return response()->json($category, 201);
  }

  public function updateCategory(Request $request, Category $category)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
      'description' => 'required|string',
      'icon' => 'nullable|string',
    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

    $category->update($request->all());
    return response()->json($category);
  }

  public function deleteCategory(Request $request, Category $category)
  {
    if ($category->events()->count() > 0) {
      return response()->json([
        'message' => 'Cannot delete category with associated events'
      ], 400);
    }

    $category->delete();
    return response()->json(['message' => 'Category deleted successfully']);
  }

  // Comment Moderation
  public function reportedComments()
  {
    $comments = Comment::with(['user', 'event'])
      ->where('is_reported', true)
      ->orderBy('created_at', 'desc')
      ->paginate(10);

    return response()->json($comments);
  }

  public function approveComment(Comment $comment)
  {
    $comment->update([
      'is_approved' => true,
      'is_reported' => false
    ]);

    return response()->json(['message' => 'Comment approved successfully']);
  }

  public function deleteComment(Comment $comment)
  {
    $comment->delete();
    return response()->json(['message' => 'Comment deleted successfully']);
  }

  // Dashboard Statistics
  public function statistics()
  {
    $stats = [
      'total_users' => User::count(),
      'total_promotors' => User::where('user_type', 'promotor')->count(),
      'total_events' => Event::count(),
      'active_events' => Event::where('status', 'active')->count(),
      'total_categories' => Category::count(),
      'reported_comments' => Comment::where('is_reported', true)->count(),
      'users_this_month' => User::whereMonth('created_at', now()->month)->count(),
      'events_this_month' => Event::whereMonth('created_at', now()->month)->count(),
    ];

    return response()->json($stats);
  }
}
