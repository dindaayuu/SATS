<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistDetail extends Model
{
    protected $fillable = [
        'checklist_id',
        'bag_detail_id',
        'tenant_detail_id',
        'source_type',
        'device_name_snapshot',
        'asset_code_snapshot',
        'condition',
        'problem_type_id',
        'custom_note',
    ];

    public function checklist()
    {
        return $this->belongsTo(
            Checklist::class
        );
    }

    public function bagDetail()
    {
        return $this->belongsTo(
            BagDetail::class
        );
    }

    public function tenantDetail()
    {
        return $this->belongsTo(
            TenantDetail::class
        );
    }

    public function problemType()
    {
        return $this->belongsTo(
            ProblemType::class
        );
    }

    public function replacement()
    {
        return $this->hasOne(
            DeviceReplacement::class
        );
    }
}