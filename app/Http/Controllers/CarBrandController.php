<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarBrand;

class CarBrandController extends Controller
{
    public function allBrands(Request $request)
    {
         $car_brands = CarBrand::query()
            ->orderBy('id', 'asc')
            ->get();


        if ($car_brands->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Car Brands not found',
                'total_count' => 0,
                'data' => [],
            ], 404);

        }
        
        return response()->json([
            'success' => true,
            'message' => 'Car Brands found',
            'total_count' => $car_brands->count(),
            'data' => $car_brands,
        ], 200);
    }
}
