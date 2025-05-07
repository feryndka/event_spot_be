<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
  public function run(): void
  {
    $promotors = User::where('user_type', 'promotor')->get();
    $categories = Category::all();

    $events = [
      [
        'title' => 'Summer Music Festival 2024',
        'slug' => 'summer-music-festival-2024',
        'description' => 'Join us for the biggest music festival of the summer featuring top artists from around the world.',
        'location_name' => 'Central Park',
        'address' => 'Jl. Letjen S. Parman Kav. 28, Jakarta Barat',
        'latitude' => -6.177367,
        'longitude' => 106.790146,
        'start_date' => Carbon::now()->addMonths(2),
        'end_date' => Carbon::now()->addMonths(2)->addDays(2),
        'registration_start' => Carbon::now(),
        'registration_end' => Carbon::now()->addMonths(2),
        'is_free' => false,
        'price' => 500000,
        'max_attendees' => 5000,
        'promotor_id' => $promotors->first()->id,
        'category_id' => $categories->where('name', 'Music')->first()->id,
        'is_published' => true,
        'poster_image' => 'events/summer-fest.jpg',
      ],
      [
        'title' => 'Tech Conference 2024',
        'slug' => 'tech-conference-2024',
        'description' => 'Annual technology conference featuring the latest innovations and industry leaders.',
        'location_name' => 'Convention Center',
        'address' => 'Jl. Jend. Gatot Subroto Kav. 99, Jakarta Selatan',
        'latitude' => -6.227367,
        'longitude' => 106.810146,
        'start_date' => Carbon::now()->addMonths(3),
        'end_date' => Carbon::now()->addMonths(3)->addDays(1),
        'registration_start' => Carbon::now(),
        'registration_end' => Carbon::now()->addMonths(3),
        'is_free' => false,
        'price' => 750000,
        'max_attendees' => 1000,
        'promotor_id' => $promotors->last()->id,
        'category_id' => $categories->where('name', 'Technology')->first()->id,
        'is_published' => true,
        'poster_image' => 'events/tech-conf.jpg',
      ],
      [
        'title' => 'Food & Wine Festival',
        'slug' => 'food-wine-festival',
        'description' => 'Experience the finest cuisines and wines from top chefs and wineries.',
        'location_name' => 'Grand Hotel',
        'address' => 'Jl. M.H. Thamrin No. 1, Jakarta Pusat',
        'latitude' => -6.187367,
        'longitude' => 106.820146,
        'start_date' => Carbon::now()->addMonths(1),
        'end_date' => Carbon::now()->addMonths(1)->addDays(1),
        'registration_start' => Carbon::now(),
        'registration_end' => Carbon::now()->addMonths(1),
        'is_free' => false,
        'price' => 300000,
        'max_attendees' => 800,
        'promotor_id' => $promotors->first()->id,
        'category_id' => $categories->where('name', 'Food & Drink')->first()->id,
        'is_published' => true,
        'poster_image' => 'events/food-fest.jpg',
      ],
    ];

    foreach ($events as $event) {
      Event::create($event);
    }
  }
}
