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
    $users = User::where('user_type', 'user')->get();
    $categories = Category::all();

    foreach ($users as $user) {
      // Each user subscribes to 2-3 random categories
      $numSubscriptions = rand(2, 3);
      $randomCategories = $categories->random($numSubscriptions);

      foreach ($randomCategories as $category) {
        CategorySubscription::create([
          'user_id' => $user->id,
          'category_id' => $category->id,
          'created_at' => now()->subDays(rand(1, 30)),
        ]);
      }
    }
  }
}
