<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BagLog extends Model
{
    protected $fillable = [
        'activity_id',
        'bag_id',
        'name_store',
        'barcode'
    ];

    public function bag()
    {
        return $this->belongsTo(Bag::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}