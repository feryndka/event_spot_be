<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventTag;
use Illuminate\Database\Seeder;

class EventTagSeeder extends Seeder
{
  public function run(): void
  {
    // Create some common tags
    $tags = [
      ['name' => 'Music', 'slug' => 'music'],
      ['name' => 'Conference', 'slug' => 'conference'],
      ['name' => 'Workshop', 'slug' => 'workshop'],
      ['name' => 'Networking', 'slug' => 'networking'],
      ['name' => 'Food', 'slug' => 'food'],
      ['name' => 'Art', 'slug' => 'art'],
      ['name' => 'Technology', 'slug' => 'technology'],
      ['name' => 'Business', 'slug' => 'business'],
      ['name' => 'Education', 'slug' => 'education'],
      ['name' => 'Entertainment', 'slug' => 'entertainment']
    ];

    foreach ($tags as $tag) {
      EventTag::create($tag);
    }

    // Attach random tags to events
    $events = Event::all();
    $allTags = EventTag::all();

    foreach ($events as $event) {
      // Attach 2-4 random tags to each event
      $numTags = rand(2, 4);
      $randomTags = $allTags->random($numTags);

      foreach ($randomTags as $tag) {
        $event->tags()->attach($tag->id);
      }
    }
  }
}
