<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventImage;
use Illuminate\Database\Seeder;

class EventImageSeeder extends Seeder
{
  public function run(): void
  {
    $events = Event::all();

    foreach ($events as $event) {
      // Create primary image
      EventImage::create([
        'event_id' => $event->id,
        'image_path' => 'events/' . $event->slug . '/poster.jpg',
        'is_primary' => true
      ]);

      // Create 2-3 additional images
      $numImages = rand(2, 3);
      for ($i = 1; $i <= $numImages; $i++) {
        EventImage::create([
          'event_id' => $event->id,
          'image_path' => 'events/' . $event->slug . '/gallery-' . $i . '.jpg',
          'is_primary' => false
        ]);
      }
    }
  }
}
