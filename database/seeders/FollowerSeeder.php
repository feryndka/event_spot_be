<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Follower;
use Illuminate\Database\Seeder;

class FollowerSeeder extends Seeder
{
  public function run(): void
  {
    // Get regular users
    $regularUsers = User::where('user_type', 'user')->get();

    // Get promotor users
    $promotors = User::where('user_type', 'promotor')->get();

    foreach ($regularUsers as $user) {
      // Each user follows 1 random promotor
      if ($promotors->count() > 0) {
        $promotor = $promotors->random();

        Follower::create([
          'user_id' => $user->id,
          'promotor_id' => $promotor->id
        ]);
      }
    }
  }
}
