<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bag_logs', function (Blueprint $table) {

            $table->id();

            $table->foreignId('activity_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('bag_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('name_store');

            $table->string('barcode');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bag_logs');
    }
};