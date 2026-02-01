<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarModel;

class CarModelController extends Controller
{
    public function allModels(Request $request)
    {
         $car_models = CarModel::query()
            ->orderBy('id', 'asc')
            ->get();


        if (! $car_models) {
            return response()->json([
                'success' => false,
                'message' => 'Car Models not found',
                'total_count' => 0,
                'data' => [],
            ], 404);

        }
        
        return response()->json([
            'success' => true,
            'message' => 'Car Model found',
            'total_count' => $car_models->count(),
            'data' => $car_models,
        ], 200);
    }
}
