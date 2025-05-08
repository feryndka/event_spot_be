<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventRatingController extends Controller
{
  public function index(Event $event)
  {
    $ratings = $event->ratings()
      ->with('user:id,name,profile_picture')
      ->approved()
      ->latest()
      ->paginate(10);

    return response()->json($ratings);
  }

  public function store(Request $request, Event $event)
  {
    $request->validate([
      'rating' => 'required|integer|min:1|max:5',
      'review' => 'nullable|string|max:1000'
    ]);

    $rating = $event->ratings()->create([
      'user_id' => Auth::id(),
      'rating' => $request->rating,
      'review' => $request->review,
      'reviewed_at' => now()
    ]);

    return response()->json($rating, 201);
  }

  public function update(Request $request, Event $event, EventRating $rating)
  {
    $this->authorize('update', $rating);

    $request->validate([
      'rating' => 'required|integer|min:1|max:5',
      'review' => 'nullable|string|max:1000'
    ]);

    $rating->update([
      'rating' => $request->rating,
      'review' => $request->review,
      'reviewed_at' => now()
    ]);

    return response()->json($rating);
  }

  public function destroy(Event $event, EventRating $rating)
  {
    $this->authorize('delete', $rating);

    $rating->delete();

    return response()->json(null, 204);
  }
}
