<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $userId = \App\Models\User::first()?->id;

        $customers = [
            ['name' => 'Budi Santoso',   'phone' => '081234567890', 'address' => 'Jl. Mawar No. 1'],
            ['name' => 'Siti Rahayu',    'phone' => '082345678901', 'address' => 'Jl. Melati No. 5'],
            ['name' => 'Ahmad Fauzi',    'phone' => '083456789012', 'address' => 'Jl. Kenanga No. 3'],
            ['name' => 'Dewi Lestari',   'phone' => '084567890123', 'address' => 'Jl. Anggrek No. 7'],
            ['name' => 'Rudi Hermawan',  'phone' => '085678901234', 'address' => null],
        ];

        foreach ($customers as $c) {
            Customer::withoutGlobalScopes()->create(array_merge($c, ['user_id' => $userId]));
        }
    }
}
