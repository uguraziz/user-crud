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

        // PostgreSQL için foreign key kontrollerini geçici olarak kapat
        DB::statement('SET session_replication_role = replica;');

        for ($i = 0; $i < $chunks; $i++) {
            User::factory($chunkSize)->create();
        }

        // Foreign key kontrollerini tekrar aç
        DB::statement('SET session_replication_role = DEFAULT;');
        $this->command->info("🎉 Toplam {$totalUsers} kullanıcı başarıyla oluşturuldu!");
    }
}
