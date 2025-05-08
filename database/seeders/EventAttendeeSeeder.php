<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use App\Models\EventAttendee;
use Illuminate\Database\Seeder;

class EventAttendeeSeeder extends Seeder
{
  public function run(): void
  {
    // Get regular users
    $users = User::where('user_type', 'user')->get();

    // Get all events
    $events = Event::all();

    // Create attendees for each event
    foreach ($events as $event) {
      // Get number of available users
      $availableUsers = $users->count();

      // Determine how many users to add (1 or 2, depending on available users)
      $numUsers = min(rand(1, 2), $availableUsers);

      // Add random users as attendees
      $randomUsers = $users->random($numUsers);

      foreach ($randomUsers as $user) {
        EventAttendee::create([
          'event_id' => $event->id,
          'user_id' => $user->id,
          'status' => 'registered',
          'registration_date' => now(),
          'ticket_code' => 'TIX-' . strtoupper(uniqid()),
          'check_in_time' => null
        ]);
      }
    }
  }
}
