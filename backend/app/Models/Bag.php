<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bag extends Model
{
    protected $fillable = [
        'barcode',
        'name',
        'name_store',
        'status',
        'is_active'
    ];

    public function details()
    {
        return $this->hasMany(BagDetail::class);
    }

    public function logs()
    {
        return $this->hasMany(BagLog::class);
    }
}