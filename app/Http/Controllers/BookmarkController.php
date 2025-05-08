<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Bookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
  public function store(Request $request, Event $event)
  {
    // Check if already bookmarked
    if ($event->bookmarks()->where('user_id', Auth::id())->exists()) {
      return response()->json([
        'message' => 'Event already bookmarked'
      ], 400);
    }

    $bookmark = $event->bookmarks()->create([
      'user_id' => Auth::id()
    ]);

    return response()->json($bookmark, 201);
  }

  public function destroy(Event $event)
  {
    $bookmark = $event->bookmarks()->where('user_id', Auth::id())->first();

    if (!$bookmark) {
      return response()->json([
        'message' => 'Event not bookmarked'
      ], 404);
    }

    $bookmark->delete();

    return response()->json(null, 204);
  }
}
