<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\TenantDetail;

class TenantDetailSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::whereIn(
            'name',
            [
                'Loket Tiket',
                'Cakrawala',
                'Kupu-Kupu',
                'Paku Bumi'
            ]
        )
        ->get();


        foreach ($tenants as $tenant) {


            $assets = [

                [
                    'asset_code' =>
                        'TEN-' . $tenant->id . '-001',

                    'asset_name' =>
                        'Tablet',

                    'condition' =>
                        'GOOD',
                ],

                [
                    'asset_code' =>
                        'TEN-' . $tenant->id . '-002',

                    'asset_name' =>
                        'Printer',

                    'condition' =>
                        'GOOD',
                ],

                [
                    'asset_code' =>
                        'TEN-' . $tenant->id . '-003',

                    'asset_name' =>
                        'Scanner',

                    'condition' =>
                        'GOOD',
                ],

            ];


            foreach ($assets as $asset) {


                TenantDetail::updateOrCreate(

                    [
                        'asset_code'=>
                            $asset['asset_code']
                    ],

                    [
                        'tenant_id'=>
                            $tenant->id,


                        'asset_name'=>
                            $asset['asset_name'],


                        'condition'=>
                            $asset['condition'],


                        'is_active'=>
                            true,
                    ]

                );

            }

        }
    }
}