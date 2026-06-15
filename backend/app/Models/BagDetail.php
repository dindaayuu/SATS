<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BagDetail extends Model
{
    protected $fillable = [
        'bag_id',
        'barcode',
        'asset',
        'condition_note',
        'is_return'
    ];

    protected $casts = [
        'is_return' => 'boolean'
    ];

    public function bag()
    {
        return $this->belongsTo(Bag::class);
    }
}