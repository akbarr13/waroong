<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            'id'               => 2,
            'name'             => 'Akbar Ramadhan',
            'email'            => 'riegerlukas1945@gmail.com',
            'google_id'        => '111794205221658960608',
            'email_verified_at'=> null,
            'password'         => null,
            'remember_token'   => '8KaNmQcL5HsWhhXvO2wTm0Ldnd5wYdhuhrL1CMDEDyRrFF2Pokry1EaKpbLF',
            'created_at'       => '2026-03-16 09:58:16',
            'updated_at'       => '2026-03-16 09:58:16',
        ]);

        // Set auto_increment agar ID berikutnya tidak bentrok
        DB::statement('ALTER TABLE users AUTO_INCREMENT = 3');
    }
}
