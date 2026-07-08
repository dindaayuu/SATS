<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ProblemType extends Model
{

    protected $fillable = [

        'name',

        'description',

        'is_active',

    ];



    /*
    Semua device yang mengalami
    jenis kendala ini
    */
    public function checklistDetails()
    {

        return $this->hasMany(
            ChecklistDetail::class
        );

    }

}