<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BagDetail extends Model
{
    protected $table = 'bag_details';

    protected $fillable = [
        'bag_id',
        'barcode',
        'asset',
        'condition_note',
        'is_return',
    ];

    public function bag()
    {
        return $this->belongsTo(Bag::class);
    }
}