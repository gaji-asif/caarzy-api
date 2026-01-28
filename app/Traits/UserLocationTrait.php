<?php

namespace App\Traits;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait UserLocationTrait
{
     /**
     * Update the authenticated user's location.
     */
    public function updateUserLocation(Request $request, $user = null)
    {
        // $request->validate([
        // 'lat' => 'required|numeric',
        // 'lng' => 'required|numeric',
        // ]);

        if (!$user) {
            $user = auth()->user();
        }

        if (!$user) {
            return false; // user not found
        }

        $user->lat = $request->lat;
        $user->lng = $request->lng;
        $user->save();

        return $user;
    }

    public function nearbyUsersQuery(float $lat, float $lng, float $radiusKm)
    {
        $earthRadius = 6371;

        // Bounding box
        $latDelta = rad2deg($radiusKm / $earthRadius);
        $lngDelta = rad2deg($radiusKm / ($earthRadius * cos(deg2rad($lat))));

        $latMin = $lat - $latDelta;
        $latMax = $lat + $latDelta;
        $lngMin = $lng - $lngDelta;
        $lngMax = $lng + $lngDelta;

        // Distance formula (safe for acos)
        $distanceSql = "
            $earthRadius * acos(
                LEAST(
                    1,
                    GREATEST(
                        -1,
                        cos(radians(?))
                        * cos(radians(lat))
                        * cos(radians(lng) - radians(?))
                        + sin(radians(?))
                        * sin(radians(lat))
                    )
                )
            )
        ";

        return User::select('users.*')
            ->selectRaw("$distanceSql AS distance", [$lat, $lng, $lat])
            ->whereNotNull('lat')
            ->whereNotNull('lng')

            // Bounding box
            ->whereBetween('lat', [$latMin, $latMax])
            ->whereBetween('lng', [$lngMin, $lngMax])

            // PostgreSQL-safe distance filter
            ->whereRaw(
                "($distanceSql) <= ?",
                [$lat, $lng, $lat, $radiusKm + 0.01]
            )

            ->orderBy('distance');
    }



   /**
     * Add distance calculation to the query (Haversine formula)
     *
     * @param Builder $query
     * @param float $latitude
     * @param float $longitude
     * @param float $radius (optional, km)
     * @return Builder
     */
// public function addDistance(Builder $query, float $latitude, float $longitude, float $radius): Builder
// {
//     // Haversine formula as a raw expression
//     $haversine = "(6371 * acos(
//         cos(radians(?)) 
//         * cos(radians(users.lat)) 
//         * cos(radians(users.lng) - radians(?)) 
//         + sin(radians(?)) 
//         * sin(radians(users.lat))
//     ))";

//     return $query->select('users.*')
//                  ->selectRaw("$haversine AS distance", [$latitude, $longitude, $latitude])
//                  ->havingRaw("$haversine <= ?", [$latitude, $longitude, $latitude, $radius]) // directly use formula
//                  ->orderByRaw("distance ASC");
// }
}
