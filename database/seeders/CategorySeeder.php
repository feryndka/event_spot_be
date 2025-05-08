<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
  public function run(): void
  {
    $categories = [
      [
        'name' => 'Music',
        'slug' => 'music',
        'description' => 'Music concerts and festivals',
        'icon' => 'music-note'
      ],
      [
        'name' => 'Business',
        'slug' => 'business',
        'description' => 'Business conferences and networking',
        'icon' => 'briefcase'
      ],
      [
        'name' => 'Technology',
        'slug' => 'technology',
        'description' => 'Tech meetups and conferences',
        'icon' => 'laptop'
      ],
      [
        'name' => 'Art',
        'slug' => 'art',
        'description' => 'Art exhibitions and workshops',
        'icon' => 'palette'
      ],
      [
        'name' => 'Sports',
        'slug' => 'sports',
        'description' => 'Sports events and tournaments',
        'icon' => 'football'
      ],
      [
        'name' => 'Food',
        'slug' => 'food',
        'description' => 'Food festivals and culinary events',
        'icon' => 'utensils'
      ],
      [
        'name' => 'Education',
        'slug' => 'education',
        'description' => 'Educational workshops and seminars',
        'icon' => 'graduation-cap'
      ],
      [
        'name' => 'Health',
        'slug' => 'health',
        'description' => 'Health and wellness events',
        'icon' => 'heart-pulse'
      ]
    ];

    foreach ($categories as $category) {
      Category::create($category);
    }
  }
}
