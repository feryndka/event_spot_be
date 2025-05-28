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

    $statuses = [
      'pending_payment',
      'registered',
      'attended',
      'cancelled'
    ];

    // Create attendees for each event
    foreach ($events as $event) {
      // Get number of available users
      $availableUsers = $users->count();

      // Determine how many users to add (2-4, depending on available users)
      $numUsers = min(rand(2, 4), $availableUsers);

      // Add random users as attendees
      $randomUsers = $users->random($numUsers);

      foreach ($randomUsers as $user) {
        $status = $statuses[array_rand($statuses)];
        $registrationDate = now()->subDays(rand(1, 30));

        $attendee = EventAttendee::create([
          'event_id' => $event->id,
          'user_id' => $user->id,
          'status' => $status,
          'registration_date' => $registrationDate,
          'ticket_code' => $status === 'registered' || $status === 'attended' ? 'TIX-' . strtoupper(uniqid()) : null,
          'check_in_time' => $status === 'attended' ? $registrationDate->addDays(rand(1, 5)) : null
        ]);
      }
    }
  }
}
