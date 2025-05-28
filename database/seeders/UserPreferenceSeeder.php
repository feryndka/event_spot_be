<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Seeder;

class UserPreferenceSeeder extends Seeder
{
  public function run(): void
  {
    // Get regular users
    $users = User::where('user_type', 'user')->get();

    foreach ($users as $user) {
      UserPreference::create([
        'user_id' => $user->id,
        'email_notifications' => true,
        'push_notifications' => true,
        'event_reminders' => true,
        'preferred_categories' => json_encode([1, 2, 3]), // Example category IDs
        'preferred_locations' => json_encode([
          'Jakarta',
          'Bandung',
          'Surabaya'
        ])
      ]);
    }
  }
}
