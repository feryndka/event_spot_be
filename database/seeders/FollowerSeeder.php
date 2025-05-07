<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Follower;
use Illuminate\Database\Seeder;

class FollowerSeeder extends Seeder
{
  public function run(): void
  {
    $promotors = User::where('user_type', 'promotor')->get();
    $users = User::where('user_type', 'user')->get();

    foreach ($promotors as $promotor) {
      // Each promotor gets 1-2 random followers
      $numFollowers = rand(1, 2);
      $randomUsers = $users->random($numFollowers);

      foreach ($randomUsers as $user) {
        Follower::create([
          'user_id' => $user->id,
          'promotor_id' => $promotor->id,
        ]);
      }
    }
  }
}
