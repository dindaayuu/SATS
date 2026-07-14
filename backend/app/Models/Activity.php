<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DeviceReturnHistory;

class Activity extends Model
{
    protected $fillable = [
        'employee_name',
        'date',
        'type',
        'bag_id',
        'barcode',
        'name_store',
    ];
    public function bagLogs()
    {
        return $this->hasMany(
            BagLog::class
        );
    }
    public function deviceReturnHistories()
{
    return $this->hasMany(
        DeviceReturnHistory::class,
        'activity_id'
    );
}
}