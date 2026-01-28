<?php

namespace App\Http\Controllers;

use App\Models\ChildrenAgeRange;
use App\Models\Language;
use App\Models\UserInterest;

class SettingsController extends Controller
{
    // contructor Function

    public function __construct()
    {
        // write contructor work
    }

    public function getInterests()
    {
        $interests = UserInterest::all();
        if (! $interests) {
            return response()->json([
                'success' => false,
                'message' => 'Interests not found',
                'data' => [],
            ], 404);

        }

        return response()->json([
            'success' => true,
            'message' => 'Interests found',
            'data' => $interests,
        ], 200);
    }

    public function getLanguages()
    {
        $languages = Language::all();
        if (! $languages) {
            return response()->json([
                'success' => false,
                'message' => 'languages not found',
                'data' => [],
            ], 404);

        }

        return response()->json([
            'success' => true,
            'message' => 'languages found',
            'data' => $languages,
        ], 200);
    }

    /**
     * Get all children age ranges from the database table.
     *
     * @param  none
     * @return JsonResponse
     */
    public function childrenAgeRange()
    {

        $children_age_ranges = ChildrenAgeRange::all();
        if (! $children_age_ranges) {
            return response()->json([
                'success' => false,
                'message' => 'Age Ranges not found',
                'data' => [],
            ], 404);

        }

        return response()->json([
            'success' => true,
            'message' => 'Children Age ranges found',
            'data' => $children_age_ranges,
        ], 200);

    }
}
