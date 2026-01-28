<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;

class CategoryController extends Controller
{
    public function addCategory(Request $request)
    {
        $user = Auth::user();
        try {
            DB::beginTransaction();

            // need to add validation for request. right now not validation applied.

            $data = Category::create([
                'name' => $request->name,
                'description' => $request->description,
                'category_for' => 'service',
            ]);
            DB::commit(); // Commit if all good

            return response()->json([
                'success' => true,
            'message' => 'Category Created Successfully',
                'data' => $data,

            ]);

        } catch (Throwable $e) {
            DB::rollback();

            return response()->json([
                'message' => 'An error occurred. Post creation failed.',

            ], 500);
        }
    }

    public function allCategories(Request $request)
    {
         $categories = Category::query()
            ->where('category_for', 'service')
            ->orderBy('id', 'asc')
            ->get();


        if (! $categories) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
                'total_count' => 0,
                'data' => [],
            ], 404);

        }

        return response()->json([
            'success' => true,
            'message' => 'Category found',
            'total_count' => $categories->count(),
            'data' => $categories,
        ], 200);
    }
}
