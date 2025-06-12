<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $totalUsers = 10_000_000;
        $chunkSize = 1000;
        $chunks = (int) ($totalUsers / $chunkSize);

        $this->command->info("Başladı: {$totalUsers} kullanıcı, {$chunks} parça halinde oluşturulacak.");

        // Performans için foreign key kontrollerini geçici olarak kapat
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        for ($i = 0; $i < $chunks; $i++) {
            User::factory($chunkSize)->create();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->command->info("🎉 Toplam {$totalUsers} kullanıcı başarıyla oluşturuldu!");
    }
}
