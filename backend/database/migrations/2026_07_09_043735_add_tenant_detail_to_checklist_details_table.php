<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklist_details', function (Blueprint $table) {
            $table
                ->foreignId('tenant_detail_id')
                ->nullable()
                ->after('bag_detail_id')
                ->constrained('tenant_details')
                ->nullOnDelete();

            $table
                ->enum('source_type', [
                    'BAG',
                    'TENANT'
                ])
                ->default('BAG')
                ->after('tenant_detail_id');
        });
    }

    public function down(): void
    {
        Schema::table('checklist_details', function (Blueprint $table) {
            $table->dropForeign([
                'tenant_detail_id'
            ]);

            $table->dropColumn([
                'tenant_detail_id',
                'source_type'
            ]);
        });
    }
};