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
    $users = User::where('user_type', 'user')->get();
    $events = Event::all();

    $comments = [
      'Great event! Looking forward to it.',
      'The lineup looks amazing!',
      'Can\'t wait to attend this event.',
      'This is going to be epic!',
      'Perfect timing for this event.',
      'The venue is perfect for this kind of event.',
      'Hope to see more events like this!',
      'The price is very reasonable.',
      'Will there be food vendors?',
      'Is there parking available?',
    ];

    foreach ($events as $event) {
      // Create 3-5 random comments for each event
      $numComments = rand(3, 5);
      for ($i = 0; $i < $numComments; $i++) {
        Comment::create([
          'event_id' => $event->id,
          'user_id' => $users->random()->id,
          'content' => $comments[array_rand($comments)],
          'created_at' => now()->subDays(rand(1, 30)),
        ]);
      }
    }
  }
}
