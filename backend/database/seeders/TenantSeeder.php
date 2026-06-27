<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = [
            ['id' => 1, 'name' => 'Loket Tiket', 'area' => 'Pesisir', 'top' => 69.00, 'left' => 14.00, 'status' => 'pending'],
            ['id' => 2, 'name' => 'Lumbung Ilmu Galileo', 'area' => 'Pesisir', 'top' => 55.00, 'left' => 22.00, 'status' => 'done'],
            ['id' => 3, 'name' => 'Taman Galileo', 'area' => 'Pesisir', 'top' => 54.00, 'left' => 38.00, 'status' => 'pending'],
            ['id' => 4, 'name' => 'Arena Jejogedan', 'area' => 'Pesisir', 'top' => 42.00, 'left' => 21.00, 'status' => 'pending'],
            ['id' => 5, 'name' => 'Kapal Jenju', 'area' => 'Pesisir', 'top' => 24.00, 'left' => 22.00, 'status' => 'done'],
            ['id' => 6, 'name' => 'Cakrawala', 'area' => 'Pesisir', 'top' => 6.00, 'left' => 23.00, 'status' => 'pending'],
            
            ['id' => 7, 'name' => 'Angon Ingon', 'area' => 'Balalantara', 'top' => 10.00, 'left' => 33.00, 'status' => 'pending'],
            ['id' => 8, 'name' => 'Resi Waringin', 'area' => 'Balalantara', 'top' => 14.00, 'left' => 35.00, 'status' => 'pending'],
            ['id' => 9, 'name' => 'Kumbang Layang', 'area' => 'Balalantara', 'top' => 18.00, 'left' => 39.00, 'status' => 'done'],
            ['id' => 10, 'name' => 'Agrowisata', 'area' => 'Balalantara', 'top' => 18.00, 'left' => 55.00, 'status' => 'pending'],
            ['id' => 11, 'name' => 'Jamur Apung', 'area' => 'Balalantara', 'top' => 28.00, 'left' => 36.00, 'status' => 'pending'],
            ['id' => 12, 'name' => 'Safari Bocah', 'area' => 'Balalantara', 'top' => 39.00, 'left' => 41.00, 'status' => 'done'],
            ['id' => 13, 'name' => 'Adu Nyali', 'area' => 'Balalantara', 'top' => 39.00, 'left' => 48.00, 'status' => 'pending'],
            
            ['id' => 14, 'name' => 'Polah Bocah', 'area' => 'Kamayayi', 'top' => 43.00, 'left' => 54.00, 'status' => 'pending'],
            ['id' => 15, 'name' => 'Kupu-Kupu', 'area' => 'Kamayayi', 'top' => 46.00, 'left' => 60.00, 'status' => 'done'],
            ['id' => 16, 'name' => 'Pinguin', 'area' => 'Kamayayi', 'top' => 47.00, 'left' => 69.00, 'status' => 'pending'],
            ['id' => 17, 'name' => 'Tata-Titi', 'area' => 'Kamayayi', 'top' => 59.00, 'left' => 68.00, 'status' => 'issue'],
            ['id' => 18, 'name' => 'Semprat-Semprot', 'area' => 'Kamayayi', 'top' => 52.00, 'left' => 57.00, 'status' => 'pending'],
            ['id' => 19, 'name' => 'Komidi Kuda Laut', 'area' => 'Kamayayi', 'top' => 69.00, 'left' => 53.00, 'status' => 'pending'],
            ['id' => 20, 'name' => 'Teka Teko', 'area' => 'Kamayayi', 'top' => 72.00, 'left' => 64.00, 'status' => 'done'],
            ['id' => 21, 'name' => 'Titihan Bocah', 'area' => 'Kamayayi', 'top' => 48.00, 'left' => 52.00, 'status' => 'pending'],
            
            ['id' => 22, 'name' => 'Paku Bumi', 'area' => 'Ararya', 'top' => 31.00, 'left' => 84.00, 'status' => 'issue'],
            ['id' => 23, 'name' => 'Bengak-Bengok', 'area' => 'Ararya', 'top' => 44.00, 'left' => 89.00, 'status' => 'pending'],
            ['id' => 24, 'name' => 'Senggal-Senggol', 'area' => 'Ararya', 'top' => 73.00, 'left' => 73.00, 'status' => 'pending'],
            ['id' => 25, 'name' => 'Lika-Liku', 'area' => 'Ararya', 'top' => 44.00, 'left' => 98.00, 'status' => 'done'],
            ['id' => 26, 'name' => 'Obat-Abit', 'area' => 'Ararya', 'top' => 64.00, 'left' => 84.00, 'status' => 'pending'],
            
            ['id' => 27, 'name' => 'Gonjang-Ganjing', 'area' => 'Segara Prada', 'top' => 47.00, 'left' => 18.00, 'status' => 'pending'],
            
            ['id' => 28, 'name' => 'Kafe Jenju', 'area' => 'Resto', 'top' => 37.00, 'left' => 28.00, 'status' => 'done'],
            ['id' => 29, 'name' => 'Kedai Adu Tangkas', 'area' => 'Resto', 'top' => 28.00, 'left' => 52.00, 'status' => 'pending'],
            ['id' => 30, 'name' => 'Rimba Resto', 'area' => 'Resto', 'top' => 29.00, 'left' => 74.00, 'status' => 'pending'],
            ['id' => 31, 'name' => 'Kedai Daimami', 'area' => 'Resto', 'top' => 63.00, 'left' => 46.00, 'status' => 'done'],
        ];

        foreach ($tenants as $t) {
            $code = 'TENANT-' . str_pad($t['id'], 3, '0', STR_PAD_LEFT);
            Tenant::updateOrCreate(
                [
                    'code' => $code
                ],
                [
                    'name' => $t['name'],
                    'area' => $t['area'],
                    'top' => $t['top'],
                    'left' => $t['left'],
                    'status' => $t['status'],
                    'route_order' => $t['id'],
                    'is_active' => true
                ]
            );
        }
    }
}
