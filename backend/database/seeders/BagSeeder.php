<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bag;
use App\Models\BagDetail;

class BagSeeder extends Seeder
{
    public function run(): void
    {
        $bags = [
            [
                'barcode' => 'SAT-001',
                'name' => 'Tas JustFluff',
                'name_store' => 'JustFluff',
                'devices' => [
                    'Tablet',
                    'Printer',
                    'Barcode Scanner',
                    'Charger'
                ]
            ],

            [
                'barcode' => 'SAT-002',
                'name' => 'Tas Klamben',
                'name_store' => 'Klamben',
                'devices' => [
                    'Tablet',
                    'Printer',
                    'Barcode Scanner',
                    'Charger'
                ]
            ],

            [
                'barcode' => 'SAT-003',
                'name' => 'Tas Asong',
                'name_store' => 'Asong',
                'devices' => [
                    'Tablet',
                    'Printer',
                    'Charger'
                ]
            ],

            [
                'barcode' => 'SAT-004',
                'name' => 'Tas Adu Nyali Marchandise',
                'name_store' => 'Adu Nyali Marchandise',
                'devices' => [
                    'Tablet',
                    'Printer',
                    'Barcode Scanner',
                    'Charger'
                ]
            ],

            [
                'barcode' => 'SAT-005',
                'name' => 'Tas VW',
                'name_store' => 'VW',
                'devices' => [
                    'Tablet',
                    'Printer',
                    'Charger'
                ]
            ],

            [
                'barcode' => 'SAT-006',
                'name' => 'Tas Tata Titi',
                'name_store' => 'Tata Titi',
                'devices' => [
                    'Tablet',
                    'Printer',
                    'Barcode Scanner',
                    'Charger'
                ]
            ],

            [
                'barcode' => 'SAT-007',
                'name' => 'Tas Booth Kamayayi',
                'name_store' => 'Booth Kamayayi',
                'devices' => [
                    'Tablet',
                    'Printer',
                    'Charger'
                ]
            ],

            [
                'barcode' => 'SAT-008',
                'name' => 'Tas Green Truck',
                'name_store' => 'Green Truck',
                'devices' => [
                    'Tablet',
                    'Printer',
                    'Charger'
                ]
            ],

            [
                'barcode' => 'SAT-009',
                'name' => 'Tas Angon Ingon',
                'name_store' => 'Angon Ingon',
                'devices' => [
                    'Sunmi',
                    'Charger'
                ]
            ],

            [
                'barcode' => 'SAT-010',
                'name' => 'Tas Jamur Apung',
                'name_store' => 'Jamur Apung',
                'devices' => [
                    'Tablet',
                    'Printer',
                    'Charger'
                ]
            ],

            [
                'barcode' => 'SAT-011',
                'name' => 'Tas Scooter',
                'name_store' => 'Scooter',
                'devices' => [
                    'HP',
                    'Printer',
                    'Charger'
                ]
            ],

            [
                'barcode' => 'SAT-012',
                'name' => 'Tas Kumbang Layang',
                'name_store' => 'Kumbang Layang',
                'devices' => [
                    'Tablet',
                    'Printer',
                    'Charger'
                ]
            ],

            [
                'barcode' => 'SAT-013',
                'name' => 'Tas Booth Sosro Lika Liku',
                'name_store' => 'Booth Sosro Lika Liku',
                'devices' => [
                    'Tablet',
                    'Printer',
                    'Charger'
                ]
            ],
        ];

        foreach ($bags as $item) {

            $bag = Bag::create([
                'barcode' => $item['barcode'],
                'name' => $item['name'],
                'name_store' => $item['name_store'],
                'status' => 'available',
                'is_active' => true
            ]);

            foreach ($item['devices'] as $index => $device) {

                BagDetail::create([
                    'bag_id' => $bag->id,
                    'barcode' => $item['barcode'] . '-DEV-' . ($index + 1),
                    'asset' => $device,
                    'condition_note' => null
                ]);
            }
        }
    }
}