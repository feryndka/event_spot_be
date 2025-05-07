<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
  public function run(): void
  {
    $categories = [
      [
        'name' => 'Music',
        'description' => 'Concerts, music festivals, and live performances',
        'icon' => 'music',
        'slug' => 'music',
      ],
      [
        'name' => 'Sports',
        'description' => 'Sports events, tournaments, and competitions',
        'icon' => 'sports',
        'slug' => 'sports',
      ],
      [
        'name' => 'Arts & Culture',
        'description' => 'Art exhibitions, cultural festivals, and theater shows',
        'icon' => 'art',
        'slug' => 'arts-culture',
      ],
      [
        'name' => 'Food & Drink',
        'description' => 'Food festivals, wine tastings, and culinary events',
        'icon' => 'food',
        'slug' => 'food-drink',
      ],
      [
        'name' => 'Business',
        'description' => 'Conferences, seminars, and networking events',
        'icon' => 'business',
        'slug' => 'business',
      ],
      [
        'name' => 'Technology',
        'description' => 'Tech conferences, hackathons, and workshops',
        'icon' => 'tech',
        'slug' => 'technology',
      ],
    ];

    foreach ($categories as $category) {
      Category::create($category);
    }
  }
}
