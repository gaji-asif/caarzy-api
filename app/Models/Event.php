<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'start_time',
        'end_time',
        'images',
        'location',
        'location_extra_info',
        'audience_min_age',
        'audience_max_age',
        'price',
        'description',
        'short_description',
        'headline',
        'secondary_headline',
        'publisher_name',
        'event_date_time',
        'details_url',
        'event_for',
        'created_by'
    ];


    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];

    public function creator(){
        return $this->belongsTo(User::class, 'created_by');
    }
}
