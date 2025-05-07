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
    $users = User::where('user_type', 'user')->get();
    $events = Event::all();

    // Create some attendees for each event
    foreach ($events as $event) {
      foreach ($users as $user) {
        EventAttendee::create([
          'event_id' => $event->id,
          'user_id' => $user->id,
          'status' => 'registered',
          'ticket_code' => 'TICKET-' . strtoupper(substr(md5(rand()), 0, 8)),
        ]);
      }
    }
  }
}
