<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'users_img_url',
        'postcode',
        'location',
        'bio',
        'children_age_range',
        'is_pregnent',
        'interests',
        'language',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'children_age_range' => 'array', // automatically cast json to array
            'interests' => 'array',
            'language' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Custom accessor to fetch related interests from JSON IDs.
     */
    public function getInterestsAttribute($value)
    {
        // $ids = json_decode($value, true) ?? [];
        $ids = (! empty($value)) ? json_decode($value, true) : [];
        if (empty($ids)) {
            return collect(); // return empty collection if none
        }

        return UserInterest::whereIn('id', $ids)->pluck('name');
    }

    /**
     * Custom accessor to fetch related children age ranges from JSON IDs.
     */
    public function getChildrenAgeRangeAttribute($value)
    {
        // $ids = json_decode($value, true) ?? [];
        $ids = (! empty($value)) ? json_decode($value, true) : [];

        if (empty($ids)) {
            return collect(); // return empty collection if none
        }

        return ChildrenAgeRange::whereIn('id', $ids)->pluck('name');
    }

    public function getLanguageAttribute($value)
    {
        // $ids = json_decode($value, true) ?? [];
        $ids = (! empty($value)) ? json_decode($value, true) : [];

        if (empty($ids)) {
            return collect(); // return empty collection if none
        }

        return Language::whereIn('id', $ids)->pluck('name');
    }
}
