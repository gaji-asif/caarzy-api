<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function addService(Request $request)
    {
        $user = Auth::user();
        try {
            DB::beginTransaction();

            // need to add validation for request later. right now not validation applied.

            // $request->validate([
            //     'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            // ]);

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('services', 'public');
            } else {
                $imagePath = null;
            }

            $data = Service::create([
                'name' => $request->name,
                'category_ids' => $request->category_ids,
                'location' => $request->location,
                'city' => $request->city,
                'rating' => $request->rating,
                'discount' => $request->discount,
                'address' => $request->address,
                'website' => $request->website,
                'description' => $request->description,
                'short_description' => $request->short_description,
                'created_by' => $user->id,
                'discount' => $request->discount,
                'is_partner' => $request->is_partner,
                'discount_text' => $request->discount_text,
                'image' => $imagePath,
            ]);

            DB::commit(); // Commit if all good

            return response()->json([
                'success' => true,
                'message' => 'New Service Created Successfully',
                'data' => $data,

            ]);

        } catch (Throwable $e) {
            DB::rollback();

            return response()->json([
                'message' => 'An error occurred. Post creation failed.',

            ], 500);
        }
    }

    public function allServices(Request $request)
    {
         $services = Service::query()
            ->orderBy('id', 'asc')
            ->get();


        if (! $services) {
            return response()->json([
                'success' => false,
                'message' => 'Services not found',
                'total_count' => 0,
                'data' => [],
            ], 404);

        }

        return response()->json([
            'success' => true,
            'message' => 'Service found',
            'total_count' => $services->count(),
            'data' => $services,
        ], 200);
    }
}
