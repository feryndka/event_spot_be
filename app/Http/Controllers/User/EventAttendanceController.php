<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventAttendee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\Api\EventResource;
use App\Http\Resources\Api\EventAttendeeResource;

class EventAttendanceController extends Controller
{
  public function upcomingEvents(Request $request)
  {
    try {
      $user = $request->user();

      $events = Event::whereHas('attendees', function ($query) use ($user) {
        $query->where('user_id', $user->id)
          ->whereIn('status', ['registered', 'pending_payment']);
      })
        ->where('start_date', '>', now())
        ->with(['promotor', 'category', 'images', 'tags'])
        ->latest()
        ->paginate(10);

      return EventResource::collection($events);
    } catch (\Exception $e) {
      Log::error('Error in upcoming events: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to load upcoming events',
        'debug' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }

  public function eventHistory(Request $request)
  {
    try {
      $user = $request->user();

      $events = Event::whereHas('attendees', function ($query) use ($user) {
        $query->where('user_id', $user->id)
          ->whereIn('status', ['attended', 'cancelled']);
      })
        ->with(['promotor', 'category', 'images', 'tags', 'attendees' => function ($query) use ($user) {
          $query->where('user_id', $user->id);
        }])
        ->latest()
        ->paginate(10);

      return EventResource::collection($events);
    } catch (\Exception $e) {
      Log::error('Error in event history: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to load event history',
        'debug' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }

  public function attendanceDetails(Event $event)
  {
    try {
      $user = request()->user();

      $attendance = EventAttendee::where('event_id', $event->id)
        ->where('user_id', $user->id)
        ->first();

      if (!$attendance) {
        return response()->json([
          'status' => 'error',
          'message' => 'You are not registered for this event'
        ], 404);
      }

      return new EventAttendeeResource($attendance);
    } catch (\Exception $e) {
      Log::error('Error in attendance details: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to load attendance details',
        'debug' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }

  public function register(Request $request, Event $event)
  {
    try {
      // Check if event is still open for registration
      if (now() > $event->registration_end) {
        return response()->json([
          'status' => 'error',
          'message' => 'Event registration is closed'
        ], 422);
      }

      // Check if user is already registered
      $existingRegistration = EventAttendee::where('event_id', $event->id)
        ->where('user_id', $request->user()->id)
        ->first();

      if ($existingRegistration) {
        return response()->json([
          'status' => 'error',
          'message' => 'You are already registered for this event'
        ], 422);
      }

      // Check if event is full
      if ($event->max_attendees && $event->attendees()->count() >= $event->max_attendees) {
        return response()->json([
          'status' => 'error',
          'message' => 'Event is full'
        ], 422);
      }

      // Create registration
      $attendance = EventAttendee::create([
        'event_id' => $event->id,
        'user_id' => $request->user()->id,
        'status' => 'registered',
        'ticket_code' => 'TIX-' . strtoupper(uniqid())
      ]);

      return new EventAttendeeResource($attendance);
    } catch (\Exception $e) {
      Log::error('Error in event registration: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to register for event',
        'debug' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }

  public function cancelRegistration(Event $event)
  {
    try {
      $user = request()->user();

      $attendance = EventAttendee::where('event_id', $event->id)
        ->where('user_id', $user->id)
        ->where('status', 'registered')
        ->first();

      if (!$attendance) {
        return response()->json([
          'status' => 'error',
          'message' => 'You are not registered for this event'
        ], 404);
      }

      // Check if cancellation is allowed (e.g., before event starts)
      if (now() > $event->start_date) {
        return response()->json([
          'status' => 'error',
          'message' => 'Cannot cancel registration after event has started'
        ], 422);
      }

      $attendance->update(['status' => 'cancelled']);

      return response()->json([
        'status' => 'success',
        'message' => 'Registration cancelled successfully'
      ]);
    } catch (\Exception $e) {
      Log::error('Error in cancel registration: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to cancel registration',
        'debug' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }
}
