<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProblemType;


class ProblemTypeSeeder extends Seeder
{

    public function run(): void
    {

        $problems = [

            [
                'name' => 'Mati Total',
                'description' => 'Device tidak dapat menyala'
            ],


            [
                'name' => 'Tidak Terbaca',
                'description' => 'Device tidak terdeteksi sistem'
            ],


            [
                'name' => 'Rusak Fisik',
                'description' => 'Kerusakan pada kondisi fisik device'
            ],


            [
                'name' => 'Hilang',
                'description' => 'Device tidak ditemukan'
            ],

        ];



        foreach ($problems as $problem) {


            ProblemType::updateOrCreate(

                [
                    'name' => $problem['name']
                ],

                [
                    'description' =>
                        $problem['description'],

                    'is_active' => true
                ]

            );


        }

    }

}