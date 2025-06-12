<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $totalUsers = 10000000; // 10 million
        $chunkSize = 1000;
        $chunks = $totalUsers / $chunkSize;

        $this->command->info("Starting to create {$totalUsers} users in {$chunks} chunks of {$chunkSize}...");

        collect(range(1, $chunks))->each(function ($chunk) use ($chunkSize) {
            User::factory($chunkSize)->create();
        });

        $this->command->info("Database seeding completed! Created {$totalUsers} users.");
    }
}
