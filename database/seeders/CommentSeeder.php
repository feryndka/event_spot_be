<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
  public function run(): void
  {
    // Get regular users
    $users = User::where('user_type', 'user')->get();

    // Get all events
    $events = Event::all();

    $comments = [
      "Looking forward to this event!",
      "Great lineup of speakers!",
      "Can't wait to attend!",
      "This is going to be amazing!",
      "Perfect venue choice!",
      "The schedule looks well organized.",
      "Hope to meet new people there!",
      "The price is reasonable for what's offered.",
      "Will there be food available?",
      "Is parking included in the ticket price?"
    ];

    foreach ($events as $event) {
      // Get number of available users
      $availableUsers = $users->count();

      // Determine how many comments to add (1 or 2, depending on available users)
      $numComments = min(rand(1, 2), $availableUsers);

      // Add random comments
      $randomUsers = $users->random($numComments);
      $randomComments = collect($comments)->random($numComments);

      foreach ($randomUsers as $index => $user) {
        Comment::create([
          'event_id' => $event->id,
          'user_id' => $user->id,
          'content' => $randomComments[$index],
          'is_approved' => true,
          'parent_id' => null,
          'created_at' => now()->subDays(rand(1, 10))
        ]);
      }
    }
  }
}
