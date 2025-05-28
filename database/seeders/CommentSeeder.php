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
      [
        'content' => "Great event! The organization was perfect and the speakers were amazing.",
        'rating' => 5
      ],
      [
        'content' => "Really enjoyed the networking opportunities. Will definitely attend again!",
        'rating' => 5
      ],
      [
        'content' => "The venue was excellent and the food was delicious.",
        'rating' => 4
      ],
      [
        'content' => "Some technical issues during the presentation, but overall a good experience.",
        'rating' => 3
      ],
      [
        'content' => "The event exceeded my expectations. Very well organized!",
        'rating' => 5
      ],
      [
        'content' => "Good content but could improve on time management.",
        'rating' => 3
      ],
      [
        'content' => "Amazing atmosphere and great people to meet.",
        'rating' => 4
      ],
      [
        'content' => "The workshops were very informative and interactive.",
        'rating' => 4
      ],
      [
        'content' => "Would have liked more networking time, but still a valuable experience.",
        'rating' => 3
      ],
      [
        'content' => "Perfect event for learning and connecting with industry professionals.",
        'rating' => 5
      ]
    ];

    foreach ($events as $event) {
      // Get number of available users
      $availableUsers = $users->count();

      // Determine how many comments to add (2-5, depending on available users)
      $numComments = min(rand(2, 5), $availableUsers);

      // Add random comments
      $randomUsers = $users->random($numComments);
      $randomComments = collect($comments)->random($numComments);

      foreach ($randomUsers as $index => $user) {
        $comment = Comment::create([
          'event_id' => $event->id,
          'user_id' => $user->id,
          'content' => $randomComments[$index]['content'],
          'is_approved' => true,
          'parent_id' => null,
          'created_at' => now()->subDays(rand(1, 30))
        ]);

        // Add 0-2 replies to some comments
        if (rand(0, 1)) {
          $numReplies = rand(0, 2);
          $replyUsers = $users->random($numReplies);

          foreach ($replyUsers as $replyUser) {
            Comment::create([
              'event_id' => $event->id,
              'user_id' => $replyUser->id,
              'content' => "Thanks for sharing your experience! " . collect([
                "I had a similar experience.",
                "Looking forward to the next event!",
                "Great to hear that!",
                "I agree with your points.",
                "Thanks for the detailed feedback."
              ])->random(),
              'is_approved' => true,
              'parent_id' => $comment->id,
              'created_at' => $comment->created_at->addHours(rand(1, 24))
            ]);
          }
        }
      }
    }
  }
}
