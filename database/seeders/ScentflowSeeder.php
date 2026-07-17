<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductRecipe;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ScentflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. DATA USER DENGAN PEMBAGIAN ROLE (Otomatis dapat UUID)
        $admin = User::create([
            'name' => 'Owner Vian',
            'email' => 'admin@scentflow.com',
            'password' => Hash::make('Gawol151004#'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $kasir = User::create([
            'name' => 'Kasir Scentflow',
            'email' => 'employee@scentflow.com',
            'password' => Hash::make('kasir123'),
            'role' => 'employee',
            'status' => 'active',
        ]);

        // 2. DATA STOK BAHAN BAKU (MATERIALS)
        $bibitBaccarat = Material::create([
            'sku' => 'BIB-001',
            'name' => 'Baccarat Rouge 540 Extract',
            'category' => 'bibit',
            'stock' => 500.00, // 500 ml
            'unit' => 'ml',
            'threshold_minimum' => 50.00,
        ]);

        $bibitSauvage = Material::create([
            'sku' => 'BIB-002',
            'name' => 'Sauvage Dior Absolute',
            'category' => 'bibit',
            'stock' => 45.00, // 45 ml (Akan terdeteksi "Menipis" karena < threshold)
            'unit' => 'ml',
            'threshold_minimum' => 50.00,
        ]);

        $absolut = Material::create([
            'sku' => 'CAM-001',
            'name' => 'Cairan Campuran Absolute Premium',
            'category' => 'campuran',
            'stock' => 2000.00, // 2000 ml / 2 Liter
            'unit' => 'ml',
            'threshold_minimum' => 200.00,
        ]);

        $botol30 = Material::create([
            'sku' => 'BTL-030',
            'name' => 'Botol Spray Kaca 30ml',
            'category' => 'botol',
            'stock' => 100.00, // 100 Pcs
            'unit' => 'pcs',
            'threshold_minimum' => 15.00,
        ]);

        // 3. DATA KATALOG INDUK PARFUM
        $baccarat = Product::create([
            'name' => 'Baccarat Rouge',
            'slug' => Str::slug('Baccarat Rouge') . '-' . Str::random(5),
            'sku' => 'PRD-BCC-RGE', // Tambah SKU unik
            'category' => 'Unisex',
            'description' => 'Aroma mewah perpaduan dari wangi melati, saffron, amberwood, dan cedarwood yang memberikan kesan elegan dan timeless.', // Tambah deskripsi aroma
            'base_price_per_ml' => 5000.00,
            'image' => 'products/baccarat.png', // Tambah nama file gambar
            'is_available' => true,
        ]);

        $sauvage = Product::create([
            'name' => 'Sauvage Dior',
            'slug' => Str::slug('Sauvage Dior') . '-' . Str::random(5),
            'sku' => 'PRD-SVG-DIO', // Tambah SKU unik
            'category' => 'Men',
            'description' => 'Wangi maskulin yang segar dan radikal berkat perpaduan bergamot Calabria yang berair serta semburan amberwood yang pekat.', // Tambah deskripsi aroma
            'base_price_per_ml' => 6000.00,
            'image' => 'products/sauvage.png', // Tambah nama file gambar
            'is_available' => true,
        ]);

        // 4. DATA RESEP RACIKAN UTAMA (Logika Harga Jadi & Potong Stok Otomatis)
        // Paket Baccarat 30ml - Standard (Rasio 1:1 -> 15ml bibit, 15ml campuran)
        ProductRecipe::create([
            'product_id' => $baccarat->id,
            'bottle_size' => '30 ml',
            'ratio_type' => 'Standard - 1:1',
            'bibit_material_id' => $bibitBaccarat->id,
            'bibit_volume' => 15.00,
            'campuran_material_id' => $absolut->id,
            'campuran_volume' => 15.00,
            'botol_material_id' => $botol30->id,
            'selling_price' => 85000.00, // Harga jual jadi siap saji ke pembeli
        ]);

        // Paket Baccarat 30ml - Premium (Rasio 2:1 -> 20ml bibit, 10ml campuran)
        ProductRecipe::create([
            'product_id' => $baccarat->id,
            'bottle_size' => '30 ml',
            'ratio_type' => 'Premium - 2:1',
            'bibit_material_id' => $bibitBaccarat->id,
            'bibit_volume' => 20.00,
            'campuran_material_id' => $absolut->id,
            'campuran_volume' => 10.00,
            'botol_material_id' => $botol30->id,
            'selling_price' => 110000.00,
        ]);
    }
}