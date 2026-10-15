<?php

namespace Database\Seeders;

use App\Models\Gym;
use Illuminate\Database\Seeder;

class GymSeeder extends Seeder
{
    public const NAME = 'Fitness Gym AI';

    public function run(): void
    {
        Gym::firstOrCreate(['slug' => 'fitness-gym-ai'], ['slug' => 'fitness-gym-ai', 'name' => 'Fitness Gym AI', 'description' => 'Gym demo interaktif dengan kelas latihan, personal training, dan AI Fitness Concierge. Harga, alamat, serta kebijakan di sini adalah contoh.', 'translations' => ['en' => ['description' => 'An interactive demo gym with classes, personal training and an AI Fitness Concierge. Prices, address and policies are examples.'], 'ja' => ['description' => 'クラス、パーソナルトレーニング、AIコンシェルジュを備えたデモジム。料金、住所、規約はサンプルです。']], 'address' => 'Lokasi demo — Kemang, Jakarta', 'city' => 'Jakarta', 'country' => 'Indonesia', 'latitude' => -6.2615, 'longitude' => 106.813, 'phone' => null, 'whatsapp' => null, 'email' => 'fitness-gym-ai@fts-tech.co.id', 'instagram' => null, 'timezone' => 'Asia/Jakarta', 'currency' => 'IDR', 'default_locale' => 'id', 'opening_time' => '06:00', 'closing_time' => '22:00', 'public_status' => 'published']);
    }
}
