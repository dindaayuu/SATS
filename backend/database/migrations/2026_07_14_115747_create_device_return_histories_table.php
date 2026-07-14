<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_return_histories', function (Blueprint $table) {

            $table->id();

            $table->foreignId('activity_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('bag_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('bag_detail_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('asset');

            $table->string('barcode');

            $table->boolean('is_return');

            $table->text('condition_note')
                ->nullable();

            $table->string('employee_name');

            $table->timestamp('returned_at');

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'device_return_histories'
        );
    }
};