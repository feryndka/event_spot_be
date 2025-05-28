<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\PromotorDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
  public function run(): void
  {
    // Create admin user
    User::create([
      'name' => 'Admin',
      'email' => 'admin@eventspot.com',
      'password' => Hash::make('admin123'),
      'phone_number' => '081234567890',
      'user_type' => 'admin',
      'is_verified' => true,
    ]);

    // Create regular users
    User::create([
      'name' => 'John Doe',
      'email' => 'john@gmail.com',
      'password' => Hash::make('password123'),
      'phone_number' => '081234567890',
      'user_type' => 'user',
    ]);

    User::create([
      'name' => 'Jane Smith',
      'email' => 'jane@gmail.com',
      'password' => Hash::make('password123'),
      'phone_number' => '081234567891',
      'user_type' => 'user',
    ]);

    // Create promotor users
    $promotor1 = User::create([
      'name' => 'Event Organizer',
      'email' => 'organizer@gmail.com',
      'password' => Hash::make('password123'),
      'phone_number' => '081234567892',
      'user_type' => 'promotor',
    ]);

    PromotorDetail::create([
      'user_id' => $promotor1->id,
      'company_name' => 'Event Pro Organizer',
      'description' => 'Professional event organizer with 5 years of experience',
      'website' => 'https://eventpro.com',
      'social_media' => json_encode([
        'instagram' => '@eventpro',
        'twitter' => '@eventpro',
        'facebook' => 'eventproorganizer'
      ]),
      'verification_status' => 'verified',
    ]);

    $promotor2 = User::create([
      'name' => 'Party Planner',
      'email' => 'planner@example.com',
      'password' => Hash::make('password123'),
      'phone_number' => '081234567893',
      'user_type' => 'promotor',
    ]);

    PromotorDetail::create([
      'user_id' => $promotor2->id,
      'company_name' => 'Party Time Events',
      'description' => 'Your trusted partner for memorable events',
      'website' => 'https://partytime.com',
      'social_media' => json_encode([
        'instagram' => '@partytime',
        'twitter' => '@partytime',
        'facebook' => 'partytimeevents'
      ]),
      'verification_status' => 'verified',
    ]);
  }
}
