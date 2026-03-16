<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $userId = \App\Models\User::first()?->id;
        $categories = Category::withoutGlobalScopes()->pluck('id', 'name');

        $products = [
            // Minuman
            ['name' => 'Aqua 600ml',          'sku' => 'AQU-600',  'category' => 'Minuman',        'modal' => 2500,  'jual' => 3500,  'stok' => 50],
            ['name' => 'Teh Botol Sosro 450ml','sku' => 'TBS-450',  'category' => 'Minuman',        'modal' => 3000,  'jual' => 4000,  'stok' => 40],
            ['name' => 'Kopi Kapal Api Sachet','sku' => 'KKA-SCH',  'category' => 'Minuman',        'modal' => 1500,  'jual' => 2000,  'stok' => 100],
            ['name' => 'Indomilk UHT 250ml',   'sku' => 'IML-250',  'category' => 'Minuman',        'modal' => 4000,  'jual' => 5500,  'stok' => 30],
            ['name' => 'Pocari Sweat 500ml',   'sku' => 'PCS-500',  'category' => 'Minuman',        'modal' => 6000,  'jual' => 8000,  'stok' => 25],

            // Makanan Ringan
            ['name' => 'Chitato 68gr',         'sku' => 'CHT-068',  'category' => 'Makanan Ringan', 'modal' => 7000,  'jual' => 9000,  'stok' => 30],
            ['name' => 'Piattos 55gr',         'sku' => 'PTS-055',  'category' => 'Makanan Ringan', 'modal' => 6500,  'jual' => 8500,  'stok' => 25],
            ['name' => 'Oreo Original',        'sku' => 'ORO-ORI',  'category' => 'Makanan Ringan', 'modal' => 5500,  'jual' => 7000,  'stok' => 40],
            ['name' => 'Indomie Goreng',       'sku' => 'IDM-GRG',  'category' => 'Makanan Ringan', 'modal' => 2800,  'jual' => 3500,  'stok' => 80],
            ['name' => 'Mie Sedaap Goreng',    'sku' => 'MSD-GRG',  'category' => 'Makanan Ringan', 'modal' => 2600,  'jual' => 3500,  'stok' => 60],

            // Sembako
            ['name' => 'Beras 1kg',            'sku' => 'BRS-001',  'category' => 'Sembako',        'modal' => 12000, 'jual' => 14000, 'stok' => 20],
            ['name' => 'Gula Pasir 1kg',       'sku' => 'GLA-001',  'category' => 'Sembako',        'modal' => 13000, 'jual' => 15000, 'stok' => 15],
            ['name' => 'Minyak Goreng 1L',     'sku' => 'MNY-001',  'category' => 'Sembako',        'modal' => 16000, 'jual' => 18000, 'stok' => 20],
            ['name' => 'Telur Ayam (butir)',   'sku' => 'TLR-001',  'category' => 'Sembako',        'modal' => 2000,  'jual' => 2500,  'stok' => 100],
            ['name' => 'Kecap Bango 135ml',    'sku' => 'KBG-135',  'category' => 'Sembako',        'modal' => 8000,  'jual' => 10000, 'stok' => 15],

            // Rokok
            ['name' => 'Gudang Garam Merah',   'sku' => 'GGM-001',  'category' => 'Rokok',          'modal' => 23000, 'jual' => 25000, 'stok' => 20],
            ['name' => 'Sampoerna Mild',       'sku' => 'SML-001',  'category' => 'Rokok',          'modal' => 25000, 'jual' => 27000, 'stok' => 20],
            ['name' => 'Djarum Super',         'sku' => 'DJS-001',  'category' => 'Rokok',          'modal' => 22000, 'jual' => 24000, 'stok' => 15],

            // Kebersihan
            ['name' => 'Sabun Lifebuoy 80gr',  'sku' => 'SLB-080',  'category' => 'Kebersihan',     'modal' => 4000,  'jual' => 5500,  'stok' => 25],
            ['name' => 'Shampoo Sunsilk Sachet','sku' => 'SHK-SCH', 'category' => 'Kebersihan',     'modal' => 1000,  'jual' => 1500,  'stok' => 60],
            ['name' => 'Pasta Gigi Pepsodent', 'sku' => 'PGP-001',  'category' => 'Kebersihan',     'modal' => 7000,  'jual' => 9000,  'stok' => 20],

            // Frozen Food
            ['name' => 'Sosis Champ 375gr',    'sku' => 'SCS-375',  'category' => 'Frozen Food',    'modal' => 18000, 'jual' => 22000, 'stok' => 10],
            ['name' => 'Nugget So Good 500gr', 'sku' => 'NGG-500',  'category' => 'Frozen Food',    'modal' => 25000, 'jual' => 30000, 'stok' => 8],
        ];

        foreach ($products as $p) {
            Product::withoutGlobalScopes()->create([
                'name'           => $p['name'],
                'sku'            => $p['sku'],
                'category_id'    => $categories[$p['category']],
                'purchase_price' => $p['modal'],
                'selling_price'  => $p['jual'],
                'stock'          => $p['stok'],
                'user_id'        => $userId,
            ]);
        }
    }
}
