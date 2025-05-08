<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\CategorySubscription;
use Illuminate\Database\Seeder;

class CategorySubscriptionSeeder extends Seeder
{
  public function run(): void
  {
    // Get regular users
    $users = User::where('user_type', 'user')->get();

    // Get all categories
    $categories = Category::all();
    $availableCategories = $categories->count();

    foreach ($users as $user) {
      // Each user subscribes to 1-2 random categories
      $numSubscriptions = min(rand(1, 2), $availableCategories);

      if ($numSubscriptions > 0) {
        $randomCategories = $categories->random($numSubscriptions);

        foreach ($randomCategories as $category) {
          CategorySubscription::create([
            'user_id' => $user->id,
            'category_id' => $category->id
          ]);
        }
      }
    }
  }
}
