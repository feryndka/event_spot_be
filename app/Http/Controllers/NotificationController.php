<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
  public function index()
  {
    $notifications = Auth::user()->notifications()
      ->latest()
      ->paginate(20);

    return response()->json($notifications);
  }

  public function markAsRead(Notification $notification)
  {
    $this->authorize('update', $notification);

    $notification->update(['is_read' => true]);

    return response()->json($notification);
  }

  public function markAllAsRead()
  {
    Auth::user()->notifications()
      ->where('is_read', false)
      ->update(['is_read' => true]);

    return response()->json([
      'message' => 'All notifications marked as read'
    ]);
  }

  public function destroy(Notification $notification)
  {
    $this->authorize('delete', $notification);

    $notification->delete();

    return response()->json(null, 204);
  }

  public function unreadCount()
  {
    $count = Auth::user()->notifications()
      ->where('is_read', false)
      ->count();

    return response()->json([
      'count' => $count
    ]);
  }
}
