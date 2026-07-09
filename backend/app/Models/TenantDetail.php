<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantDetail extends Model
{
    protected $fillable = [
        'tenant_id',
        'asset_code',
        'asset_name',
        'condition',
        'is_active',
    ];

    public function tenant()
    {
        return $this->belongsTo(
            Tenant::class
        );
    }
}