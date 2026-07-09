<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_details', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete();

            $table
                ->string('asset_code')
                ->unique();

            $table
                ->string('asset_name');

            $table
                ->string('condition')
                ->default('GOOD');

            $table
                ->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->index('tenant_id');
            $table->index('asset_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_details');
    }
};