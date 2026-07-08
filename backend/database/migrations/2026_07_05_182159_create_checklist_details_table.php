<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{


    public function up(): void
    {

        Schema::create('checklist_details', function (Blueprint $table) {


            $table->id();



            // header checklist
            $table
                ->foreignId('checklist_id')
                ->constrained('checklists')
                ->cascadeOnDelete();



            // device aktif dari tas
            $table
                ->foreignId('bag_detail_id')
                ->constrained('bag_details')
                ->cascadeOnDelete();



            /*
            Snapshot agar history tidak berubah
            walaupun device sudah diganti
            */


            $table->string(
                'device_name_snapshot'
            );


            $table->string(
                'asset_code_snapshot'
            );



            // hasil pengecekan device
            $table->enum('condition', [
                'GOOD',
                'PROBLEM'
            ])
            ->default('GOOD');



            // alasan umum kendala
            $table
                ->foreignId('problem_type_id')
                ->nullable()
                ->constrained('problem_types')
                ->nullOnDelete();



            // alasan manual
            $table->text('custom_note')
                ->nullable();



            $table->timestamps();


        });

    }





    public function down(): void
    {

        Schema::dropIfExists(
            'checklist_details'
        );

    }


};