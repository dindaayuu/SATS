<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            BagSeeder::class,
            TenantSeeder::class,
            ProblemTypeSeeder::class,
            BagDetailSeeder::class,
        ]);

        $this->call([
            TenantDetailSeeder::class,
        ]);
    }
}