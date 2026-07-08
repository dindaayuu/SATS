<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceReplacement extends Model
{
    protected $fillable = [
        'checklist_detail_id',
        'bag_id',
        'device_type',
        'old_asset_code',
        'old_device_name',
        'new_asset_code',
        'new_device_name',
        'reason',
        'replaced_by',
        'replacement_time',
    ];

    /**
     * Detail checklist yang menyebabkan device diganti.
     */
    public function checklistDetail()
    {
        return $this->belongsTo(ChecklistDetail::class);
    }

    /**
     * Tas SATS tempat device berada.
     */
    public function bag()
    {
        return $this->belongsTo(Bag::class);
    }
}