<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'employee_name',
        'date',
        'type'
    ];
    public function bagLogs()
    {
        return $this->hasMany(
            BagLog::class
        );
    }
}