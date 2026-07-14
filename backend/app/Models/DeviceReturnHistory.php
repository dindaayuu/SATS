<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceReturnHistory extends Model
{
    protected $fillable = [
        'activity_id',
        'bag_id',
        'bag_detail_id',
        'asset',
        'barcode',
        'is_return',
        'condition_note',
        'employee_name',
        'returned_at',
    ];
}