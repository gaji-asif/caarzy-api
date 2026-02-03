<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Car;

class CarController extends Controller
{
    public function allCars(Request $request)
    {
         $cars = Car::query()
            ->orderBy('id', 'asc')
            ->get();


        if ($cars->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cars not found',
                'total_count' => 0,
                'data' => [],
            ], 404);

        }
        
        return response()->json([
            'success' => true,
            'message' => 'Cars found',
            'total_count' => $cars->count(),
            'data' => $cars,
        ], 200);
    }
}
