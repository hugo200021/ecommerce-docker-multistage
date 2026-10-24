<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Audífonos Bluetooth',
                'description' => 'Audífonos inalámbricos con cancelación de ruido.',
                'price' => '59.99',
                'stock' => 50,
            ],
            [
                'name' => 'Teclado Mecánico RGB',
                'description' => 'Teclado mecánico con iluminación RGB y switches táctiles.',
                'price' => '89.90',
                'stock' => 30,
            ],
            [
                'name' => 'Mouse Inalámbrico',
                'description' => 'Mouse ergonómico con conexión 2.4 GHz y Bluetooth.',
                'price' => '34.50',
                'stock' => 45,
            ],
            [
                'name' => 'Monitor IPS 24 pulgadas',
                'description' => 'Monitor Full HD con panel IPS y frecuencia de 100 Hz.',
                'price' => '189.99',
                'stock' => 18,
            ],
            [
                'name' => 'SSD NVMe 1 TB',
                'description' => 'Unidad de estado sólido NVMe PCIe de 1 TB.',
                'price' => '79.95',
                'stock' => 35,
            ],
            [
                'name' => 'Webcam Full HD',
                'description' => 'Webcam 1080p con micrófono integrado.',
                'price' => '49.99',
                'stock' => 25,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['name' => $product['name']],
                $product
            );
        }
    }
}
