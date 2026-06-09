<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BagDetail extends Model
{
    protected $fillable = [
        'bag_id',
        'barcode',
        'asset',
        'condition_note'
    ];

    public function bag()
    {
        return $this->belongsTo(Bag::class);
    }
}