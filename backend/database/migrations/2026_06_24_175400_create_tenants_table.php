<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('area');
            $table->decimal('top', 5, 2);
            $table->decimal('left', 5, 2);
            $table->string('status')->default('pending')->index();
            $table->integer('route_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_position_updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
