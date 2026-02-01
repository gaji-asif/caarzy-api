<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CarBrand extends Model
{
    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'name',
        'created_by',
        'updated_by',
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
