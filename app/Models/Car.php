<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Car extends Model
{
    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'name',
        'brand_id',
        'used_condition',
        'model',
        'fuel_type',
        'body_type',
        'mileage',
        'image',
        'registration_year',
        'selling_year',
        'is_vat',
        'is_active',
    ];

     /**
     * Automatically handle created_by & updated_by
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->created_by = Auth::id();
            $model->updated_by = Auth::id();
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::id();
        });
    }
}
