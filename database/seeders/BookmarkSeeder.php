<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use App\Models\Bookmark;
use Illuminate\Database\Seeder;

class BookmarkSeeder extends Seeder
{
  public function run(): void
  {
    $users = User::where('user_type', 'user')->get();
    $events = Event::all();

    foreach ($users as $user) {
      // Each user bookmarks 1-3 random events
      $numBookmarks = rand(1, 3);
      $randomEvents = $events->random($numBookmarks);

      foreach ($randomEvents as $event) {
        Bookmark::create([
          'user_id' => $user->id,
          'event_id' => $event->id,
        ]);
      }
    }
  }
}
