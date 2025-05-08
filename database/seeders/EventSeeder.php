<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
  public function run(): void
  {
    // Get promotor users
    $promotors = User::where('user_type', 'promotor')->get();

    // Get all categories
    $categories = Category::all();

    // Create sample events
    $events = [
      [
        'title' => 'Summer Music Festival 2024',
        'slug' => 'summer-music-festival-2024',
        'description' => 'Join us for the biggest music festival of the summer featuring top artists from around the world.',
        'is_ai_generated' => false,
        'poster_image' => 'events/summer-festival.jpg',
        'promotor_id' => $promotors->first()->id,
        'category_id' => $categories->where('slug', 'music')->first()->id,
        'location_name' => 'Central Park Amphitheater',
        'address' => 'Central Park, New York, NY 10022',
        'latitude' => 40.7829,
        'longitude' => -73.9654,
        'start_date' => now()->addDays(30),
        'end_date' => now()->addDays(32),
        'registration_start' => now(),
        'registration_end' => now()->addDays(25),
        'is_free' => false,
        'price' => 150.00,
        'max_attendees' => 5000,
        'is_published' => true,
        'is_featured' => true,
        'is_approved' => true,
        'views_count' => 0
      ],
      [
        'title' => 'Tech Conference 2024',
        'slug' => 'tech-conference-2024',
        'description' => 'Annual technology conference featuring the latest innovations and industry leaders.',
        'is_ai_generated' => false,
        'poster_image' => 'events/tech-conference.jpg',
        'promotor_id' => $promotors->last()->id,
        'category_id' => $categories->where('slug', 'technology')->first()->id,
        'location_name' => 'Convention Center',
        'address' => '747 Howard St, San Francisco, CA 94103',
        'latitude' => 37.7833,
        'longitude' => -122.4167,
        'start_date' => now()->addDays(45),
        'end_date' => now()->addDays(47),
        'registration_start' => now(),
        'registration_end' => now()->addDays(40),
        'is_free' => false,
        'price' => 299.99,
        'max_attendees' => 2000,
        'is_published' => true,
        'is_featured' => true,
        'is_approved' => true,
        'views_count' => 0
      ],
      [
        'title' => 'Food & Wine Festival',
        'slug' => 'food-wine-festival-2024',
        'description' => 'Experience the finest cuisines and wines from top chefs and wineries.',
        'is_ai_generated' => false,
        'poster_image' => 'events/food-festival.jpg',
        'promotor_id' => $promotors->first()->id,
        'category_id' => $categories->where('slug', 'food')->first()->id,
        'location_name' => 'City Square',
        'address' => '201 E Randolph St, Chicago, IL 60602',
        'latitude' => 41.8781,
        'longitude' => -87.6298,
        'start_date' => now()->addDays(60),
        'end_date' => now()->addDays(62),
        'registration_start' => now(),
        'registration_end' => now()->addDays(55),
        'is_free' => false,
        'price' => 75.00,
        'max_attendees' => 3000,
        'is_published' => true,
        'is_featured' => false,
        'is_approved' => true,
        'views_count' => 0
      ]
    ];

    foreach ($events as $event) {
      Event::create($event);
    }
  }
}
