<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $totalUsers = 10_000_000;
        $chunkSize = 1000;
        $chunks = (int) ($totalUsers / $chunkSize);

        $this->command->info("Başladı: {$totalUsers} kullanıcı, {$chunks} parça halinde oluşturulacak.");

        $globalCounter = 1;
        for ($i = 0; $i < $chunks; $i++) {
            $users = User::factory($chunkSize)->make()->map(function ($user) use (&$globalCounter) {
                $userData = [
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => "user{$globalCounter}@example.com",
                    'password' => $user->password,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $globalCounter++;
                return $userData;
            })->toArray();

            User::insert($users);
        }

        $this->command->info("🎉 Toplam {$totalUsers} kullanıcı başarıyla oluşturuldu!");
    }
}
