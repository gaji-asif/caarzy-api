<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'category_ids',
        'location',
        'city',
        'rating',
        'address',
        'website',
        'description',
        'short_description',
        'created_by',
        'discount',
        'image',
        'is_partner',
        'discount_text'
    ];

    protected $appends = ['image_url'];

    protected $casts = [
        'category_ids' => 'array',
    ];

    public function getImageUrlAttribute()
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : null;
    }
}
