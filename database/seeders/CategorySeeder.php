<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::first()?->id;

        $categories = [
            'Minuman',
            'Makanan Ringan',
            'Sembako',
            'Rokok',
            'Kebersihan',
            'Frozen Food',
        ];

        foreach ($categories as $name) {
            Category::withoutGlobalScopes()->create(['name' => $name, 'user_id' => $userId]);
        }
    }
}
