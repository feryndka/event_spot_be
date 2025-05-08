<?php

namespace App\Http\Controllers;

use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserPreferenceController extends Controller
{
  public function show(Request $request)
  {
    $preferences = $request->user()->preferences;

    if (!$preferences) {
      $preferences = $request->user()->preferences()->create([
        'notification_settings' => [
          'email_notifications' => true,
          'push_notifications' => true,
          'event_reminders' => true,
        ],
        'privacy_settings' => [
          'profile_visibility' => 'public',
          'show_email' => false,
          'show_phone' => false,
        ],
        'theme_settings' => [
          'theme' => 'light',
          'font_size' => 'medium',
        ],
        'language_settings' => [
          'language' => 'en',
          'date_format' => 'Y-m-d',
          'time_format' => '24h',
        ],
      ]);
    }

    return response()->json($preferences);
  }

  public function update(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'notification_settings' => 'sometimes|array',
      'privacy_settings' => 'sometimes|array',
      'theme_settings' => 'sometimes|array',
      'language_settings' => 'sometimes|array',
    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

    $preferences = $request->user()->preferences;

    if (!$preferences) {
      $preferences = $request->user()->preferences()->create($request->all());
    } else {
      $preferences->update($request->all());
    }

    return response()->json($preferences);
  }
}
