<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Checklist extends Model
{

    protected $fillable = [

        'tenant_id',

        'bag_id',

        'pic_name',

        'check_date',

        'start_time',

        'finish_time',

        'status',

        'overall_note',

    ];



    /*
    Relasi ke tenant
    */
    public function tenant()
    {

        return $this->belongsTo(
            Tenant::class
        );

    }



    /*
    Relasi ke tas SATS
    */
    public function bag()
    {

        return $this->belongsTo(
            Bag::class
        );

    }



    /*
    Detail device yang dicek
    */
    public function details()
    {

        return $this->hasMany(
            ChecklistDetail::class
        );

    }

}