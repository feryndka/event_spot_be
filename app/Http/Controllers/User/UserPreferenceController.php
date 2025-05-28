<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserPreference;
use App\Http\Resources\Api\UserPreferenceResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserPreferenceController extends Controller
{
  /**
   * Get user preferences
   */
  public function show()
  {
    try {
      $user = Auth::user();
      $preferences = UserPreference::where('user_id', $user->id)->first();

      if (!$preferences) {
        // Create default preferences if none exist
        $preferences = UserPreference::create([
          'user_id' => $user->id,
          'email_notifications' => true,
          'push_notifications' => true,
          'event_reminders' => true,
          'preferred_categories' => [],
          'preferred_locations' => []
        ]);
      }

      return response()->json([
        'status' => 'success',
        'data' => new UserPreferenceResource($preferences)
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to get user preferences'
      ], 500);
    }
  }

  /**
   * Update user preferences
   */
  public function update(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'email_notifications' => 'sometimes|boolean',
        'push_notifications' => 'sometimes|boolean',
        'event_reminders' => 'sometimes|boolean',
        'preferred_categories' => 'sometimes|array',
        'preferred_locations' => 'sometimes|array'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Validation failed',
          'errors' => $validator->errors()
        ], 422);
      }

      $user = Auth::user();
      $preferences = UserPreference::where('user_id', $user->id)->first();

      if (!$preferences) {
        $preferences = new UserPreference();
        $preferences->user_id = $user->id;
      }

      // Update only the fields that are provided
      if ($request->has('email_notifications')) {
        $preferences->email_notifications = $request->email_notifications;
      }

      if ($request->has('push_notifications')) {
        $preferences->push_notifications = $request->push_notifications;
      }

      if ($request->has('event_reminders')) {
        $preferences->event_reminders = $request->event_reminders;
      }

      if ($request->has('preferred_categories')) {
        $preferences->preferred_categories = $request->preferred_categories;
      }

      if ($request->has('preferred_locations')) {
        $preferences->preferred_locations = $request->preferred_locations;
      }

      $preferences->save();

      return response()->json([
        'status' => 'success',
        'message' => 'Preferences updated successfully',
        'data' => new UserPreferenceResource($preferences)
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to update preferences'
      ], 500);
    }
  }
}
