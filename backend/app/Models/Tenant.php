<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'code',
        'name',
        'area',
        'top',
        'left',
        'status',
        'route_order',
        'is_active',
        'last_position_updated_at',
        'updated_by',
    ];

    protected $casts = [
        'top' => 'float',
        'left' => 'float',
        'is_active' => 'boolean',
        'last_position_updated_at' => 'datetime',
    ];

    /*Get the route key for the model.*/
    public function getRouteKeyName(): string
    {
        return 'code';
    }

     /*Checklist History Tenant*/
    public function checklists()
    {
        return $this->hasMany(
            Checklist::class
        );
    }
}

   
