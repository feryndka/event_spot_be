<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventAttendee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventAttendeeController extends Controller
{
  public function store(Request $request, Event $event)
  {
    // Check if event is still open for registration
    if (!$event->isRegistrationOpen()) {
      return response()->json([
        'message' => 'Event registration is closed'
      ], 400);
    }

    // Check if user has already registered
    if ($event->attendees()->where('user_id', Auth::id())->exists()) {
      return response()->json([
        'message' => 'You have already registered for this event'
      ], 400);
    }

    // Check if event is full
    if ($event->isFull()) {
      return response()->json([
        'message' => 'Event is full'
      ], 400);
    }

    $attendee = $event->attendees()->create([
      'user_id' => Auth::id(),
      'registration_date' => now(),
      'status' => 'registered',
      'ticket_code' => $this->generateTicketCode()
    ]);

    return response()->json($attendee, 201);
  }

  private function generateTicketCode()
  {
    return strtoupper(substr(md5(uniqid()), 0, 8));
  }
}
