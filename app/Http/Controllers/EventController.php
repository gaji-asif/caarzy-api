<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function allEvents(Request $request)
    {
        $city = $request->query('city');
        $eventFor = $request->query('event_for');
        $search   = $request->query('search_text_q'); 

        $events = Event::query()
            ->with(['creator:id,name'])
            ->whereDate('start_time', '>=', today())
            // ->where('publisher_name', 'Nesti Community')
            ->whereNull('event_id')
            // Filter by city
            // ->when($city, function($query, $city){
            //     $query->whereRaw(
            //             "trim(split_part(location, ',', 3)) ILIKE ?",
            //             [$city]
            //         );
            // })
            ->when($city, function ($query, $city) {
                $query->where('location', 'ILIKE', "%{$city}%");
            })
            // Filter by audience ( like babies / children)
            ->when($eventFor, function ($query, $eventFor) {
                $query->where('event_for', $eventFor);
            })

             // 🔍 Search in name & short_description
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('short_description', 'ILIKE', "%{$search}%");
                });
            })
            ->orderBy('start_time', 'asc')
            ->get();


        if (! $events) {
            return response()->json([
                'success' => false,
                'message' => 'Events not found',
                'total_count' => 0,
                'data' => [],
            ], 404);

        }

        return response()->json([
            'success' => true,
            'message' => 'Events found',
            'total_count' => $events->count(),
            'data' => $events,
            'filters' => [
                'city' => $city,
                'event_for' => $eventFor,
                'search_text_q'         => $search
            ]
        ], 200);
    }

    public function addEvent(Request $request)
    {
        $user = Auth::user();
        try {
            DB::beginTransaction();

            // need to add validation for request later. right now not validation applied.

            // $request->validate([
            //     'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            // ]);

            $timeParts = explode('-', $request->time); // ["10:00 ", " 12:00"]
            $startTime = trim($timeParts[0]); // "10:00"
            $endTime = trim($timeParts[1]);   // "12:00"

            $startDateTime = $request->date . ' ' . $startTime . ':00'; // "2026-01-14 10:00:00"
            $endDateTime   = $request->date . ' ' . $endTime . ':00';   // "2026-01-14 12:00:00"

            

             $data = Event::create([
                'name' => $request->title,
                'location' => $request->place . ', ' . $request->city,
                'start_time' => $startDateTime,
                'end_time' => $endDateTime,
                'short_description' => $request->description,
                'created_by' => $user->id,
                'event_for' => $request->audience,
                //'publisher_name' => 'Nesti Community'
                
            ]);

            DB::commit(); // Commit if all good

            return response()->json([
                'success' => true,
                'message' => 'New Event Created Successfully',
                'data' => $data,

            ]);

        } catch (Throwable $e) {
            DB::rollback();

            return response()->json([
                'message' => 'An error occurred. Post creation failed.',

            ], 500);
        }
    }
}
