<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Api\EventResource;
use App\Http\Resources\Api\NotificationResource;

class UserDashboardController extends Controller
{
  /**
   * Get user dashboard overview
   */
  public function overview()
  {
    try {
      $user = Auth::user();

      $overview = [
        'total_events_attended' => EventAttendee::where('user_id', $user->id)
          ->where('status', 'attended')
          ->count(),
        'upcoming_events' => EventAttendee::where('user_id', $user->id)
          ->whereHas('event', function ($query) {
            $query->where('start_date', '>', now());
          })
          ->where('status', 'registered')
          ->count(),
        'total_registrations' => EventAttendee::where('user_id', $user->id)->count(),
        'total_notifications' => Notification::where('user_id', $user->id)
          ->where('is_read', false)
          ->count(),
        'recent_activities' => $this->getRecentActivities($user)
      ];

      return response()->json([
        'status' => 'success',
        'data' => $overview
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to get dashboard overview'
      ], 500);
    }
  }

  /**
   * Get user's events
   */
  public function events(Request $request)
  {
    try {
      $user = Auth::user();
      $status = $request->query('status');

      $query = EventAttendee::where('user_id', $user->id)
        ->with(['event.promotor', 'event.category', 'event.images'])
        ->when($status, function ($query) use ($status) {
          $query->where('status', $status);
        });

      $events = $query->latest()->paginate(10);

      return response()->json([
        'status' => 'success',
        'data' => [
          'events' => EventResource::collection($events->pluck('event')),
          'meta' => [
            'current_page' => $events->currentPage(),
            'total' => $events->total(),
            'per_page' => $events->perPage()
          ]
        ]
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to get user events'
      ], 500);
    }
  }

  /**
   * Get user's notifications
   */
  public function notifications(Request $request)
  {
    try {
      $user = Auth::user();
      $isRead = $request->query('is_read');

      $query = Notification::where('user_id', $user->id)
        ->when($isRead !== null, function ($query) use ($isRead) {
          $query->where('is_read', $isRead);
        });

      $notifications = $query->latest()->paginate(15);

      return response()->json([
        'status' => 'success',
        'data' => [
          'notifications' => NotificationResource::collection($notifications),
          'meta' => [
            'current_page' => $notifications->currentPage(),
            'total' => $notifications->total(),
            'per_page' => $notifications->perPage()
          ]
        ]
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to get notifications'
      ], 500);
    }
  }

  /**
   * Get user's recent activities
   */
  private function getRecentActivities(User $user)
  {
    $activities = [];

    // Get recent event registrations
    $recentRegistrations = EventAttendee::where('user_id', $user->id)
      ->with(['event'])
      ->latest()
      ->take(5)
      ->get();

    foreach ($recentRegistrations as $registration) {
      $activities[] = [
        'type' => 'registration',
        'event' => [
          'id' => $registration->event->id,
          'title' => $registration->event->title,
          'start_date' => $registration->event->start_date
        ],
        'status' => $registration->status,
        'created_at' => $registration->created_at
      ];
    }

    // Get recent notifications
    $recentNotifications = Notification::where('user_id', $user->id)
      ->latest()
      ->take(5)
      ->get();

    foreach ($recentNotifications as $notification) {
      $activities[] = [
        'type' => 'notification',
        'title' => $notification->title,
        'content' => $notification->content,
        'is_read' => $notification->is_read,
        'created_at' => $notification->created_at
      ];
    }

    // Sort activities by created_at
    usort($activities, function ($a, $b) {
      return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    return array_slice($activities, 0, 5);
  }
}
