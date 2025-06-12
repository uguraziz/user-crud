<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // $totalUsers = 10000000; // 10 million
        $totalUsers = 1000;
        $chunkSize = 100;
        $chunks = $totalUsers / $chunkSize;

        $this->command->info("Starting to create {$totalUsers} users in {$chunks} chunks of {$chunkSize}...");

        collect(range(1, $chunks))->each(function ($chunk) use ($chunkSize) {
            User::factory($chunkSize)->create();
        });

        $this->command->info("Database seeding completed! Created {$totalUsers} users.");
    }
}
