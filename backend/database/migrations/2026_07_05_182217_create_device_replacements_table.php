<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{


    public function up(): void
    {

        Schema::create('device_replacements', function (Blueprint $table) {


            $table->id();



            /*
            Kendala yang menyebabkan
            pergantian device
            */
            $table
                ->foreignId('checklist_detail_id')
                ->constrained('checklist_details')
                ->cascadeOnDelete();




            /*
            Tas tempat device diganti
            */
            $table
                ->foreignId('bag_id')
                ->constrained('bags')
                ->cascadeOnDelete();




            /*
            Jenis device:
            Tablet
            Printer
            Scanner
            */
            $table->string(
                'device_type'
            );




            /*
            Device lama
            */
            $table->string(
                'old_asset_code'
            );


            $table->string(
                'old_device_name'
            );




            /*
            Device pengganti
            */
            $table->string(
                'new_asset_code'
            );


            $table->string(
                'new_device_name'
            );




            /*
            alasan pergantian
            */
            $table->text(
                'reason'
            )
            ->nullable();




            /*
            Staff IT yang mengganti
            */
            $table->string(
                'replaced_by'
            );




            /*
            waktu pergantian
            */
            $table->timestamp(
                'replacement_time'
            );




            $table->timestamps();


        });

    }





    public function down(): void
    {

        Schema::dropIfExists(
            'device_replacements'
        );

    }


};