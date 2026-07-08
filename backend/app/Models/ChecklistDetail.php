<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ChecklistDetail extends Model
{

    protected $fillable = [

        'checklist_id',

        'bag_detail_id',

        'device_name_snapshot',

        'asset_code_snapshot',

        'condition',

        'problem_type_id',

        'custom_note',

    ];



    /*
    Header checklist
    */
    public function checklist()
    {

        return $this->belongsTo(
            Checklist::class
        );

    }



    /*
    Device aktif dari SATS
    (bag_details)
    */
    public function bagDetail()
    {

        return $this->belongsTo(
            BagDetail::class
        );

    }



    /*
    Jenis kendala:
    Mati Total,
    Tidak Terbaca,
    dll
    */
    public function problemType()
    {

        return $this->belongsTo(
            ProblemType::class
        );

    }



    /*
    History pergantian device
    */
    public function replacement()
    {

        return $this->hasOne(
            DeviceReplacement::class
        );

    }

}