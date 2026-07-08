<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{


    public function up(): void
    {

        Schema::create('checklists', function (Blueprint $table) {


            $table->id();



            // tenant yang melakukan checklist
            $table
                ->foreignId('tenant_id')
                ->constrained('tenants')
                ->cascadeOnDelete();



            // tas yang sedang digunakan tenant
            $table
                ->foreignId('bag_id')
                ->constrained('bags')
                ->cascadeOnDelete();



            // PIC tenant
            $table->string('pic_name');



            // tanggal checklist
            $table->date('check_date');



            // mulai checklist
            $table->time('start_time')
                ->nullable();



            // selesai checklist
            $table->time('finish_time')
                ->nullable();



            // status akhir checklist
            $table->enum('status', [
                'DONE',
                'PROBLEM'
            ])
            ->default('DONE');



            $table->timestamps();


        });

    }




    public function down(): void
    {

        Schema::dropIfExists('checklists');

    }


};