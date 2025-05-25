<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Resources\Api\EventResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BookmarkController extends Controller
{
  /**
   * Get all bookmarked events for the authenticated user
   */
  public function index(Request $request)
  {
    try {
      $user = $request->user();

      $bookmarks = Bookmark::where('user_id', $user->id)
        ->with(['event' => function ($query) {
          $query->with(['promotor', 'category', 'images', 'tags']);
        }])
        ->latest()
        ->paginate(10);

      return response()->json([
        'status' => 'success',
        'data' => EventResource::collection($bookmarks->pluck('event'))
      ]);
    } catch (\Exception $e) {
      Log::error('Error in bookmark index: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to load bookmarked events'
      ], 500);
    }
  }

  /**
   * Bookmark an event
   */
  public function store(Request $request, Event $event)
  {
    try {
      $user = $request->user();

      // Check if already bookmarked
      $existingBookmark = Bookmark::where('user_id', $user->id)
        ->where('event_id', $event->id)
        ->first();

      if ($existingBookmark) {
        return response()->json([
          'status' => 'error',
          'message' => 'Event already bookmarked'
        ], 400);
      }

      // Create bookmark
      $bookmark = Bookmark::create([
        'user_id' => $user->id,
        'event_id' => $event->id
      ]);

      return response()->json([
        'status' => 'success',
        'message' => 'Event bookmarked successfully',
        'data' => new EventResource($event->load(['promotor', 'category', 'images', 'tags']))
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to bookmark event',
        'debug' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }

  /**
   * Remove bookmark from an event
   */
  public function destroy(Request $request, Event $event)
  {
    try {
      $user = $request->user();

      $bookmark = Bookmark::where('user_id', $user->id)
        ->where('event_id', $event->id)
        ->first();

      if (!$bookmark) {
        return response()->json([
          'status' => 'error',
          'message' => 'Event is not bookmarked'
        ], 404);
      }

      $bookmark->delete();

      return response()->json([
        'status' => 'success',
        'message' => 'Bookmark removed successfully'
      ]);
    } catch (\Exception $e) {
      Log::error('Error in bookmark destroy: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to remove bookmark'
      ], 500);
    }
  }

  /**
   * Check if an event is bookmarked by the user
   */
  public function check(Request $request, Event $event)
  {
    try {
      $user = $request->user();

      $isBookmarked = Bookmark::where('user_id', $user->id)
        ->where('event_id', $event->id)
        ->exists();

      return response()->json([
        'status' => 'success',
        'data' => [
          'is_bookmarked' => $isBookmarked
        ]
      ]);
    } catch (\Exception $e) {
      Log::error('Error in bookmark check: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to check bookmark status'
      ], 500);
    }
  }
}
