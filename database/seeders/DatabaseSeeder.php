<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            EventSeeder::class,
            EventAttendeeSeeder::class,
            PaymentSeeder::class,
            CommentSeeder::class,
            BookmarkSeeder::class,
            FollowerSeeder::class,
            CategorySubscriptionSeeder::class,
        ]);
    }
}
