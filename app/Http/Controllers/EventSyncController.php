<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class EventSyncController extends Controller
{
    private array $publisherCache = [];
    private array $locationCache = [];

    private array $fetchedEventsIDs = [
        'babies' => [],
        'children' => [],
    ];

    private function fetchFromUrl(string  $audience, string  $url){
        $batchSize = 200;   // how many rows to insert at once
        $rows = [];
        $totalSaved = 0;

        do {
            $response = Http::timeout(30)->get($url);

            if ($response->failed()) {
                throw new \Exception("API request failed for {$audience}");
            }

            $data = $response->json('data') ?? [];

            foreach ($data as $item) {

                $eventId = $item['id'] ?? null;
                if($eventId){
                    $this->fetchedEventIds[$audience][] = $eventId;
                }

                $rows[] = [
                    'event_id' => $eventId,
                    'event_for' => $audience, //  (babies / children)
                    'name' => $item['name']['en']
                        ?? $item['name']['fi']
                        ?? null,

                    'start_time' => $item['start_time'] ?? null,
                    'end_time'   => $item['end_time'] ?? null,

                    'location' => $this->getLocation($item['location']['@id'] ?? null),
                    'audience_min_age' => $item['audience_min_age'] ?? null,
                    'audience_max_age' => $item['audience_max_age'] ?? null,

                    'price' => $this->getPrice($item['offers'] ?? []),

                    'short_description' =>
                        $item['short_description']['fi']
                        ?? $item['short_description']['en']
                        ?? null,

                    'publisher_name' => $this->getPublisherName($item['publisher']) ?? null, // disabled for speed
                    'details_url' => 'https://tapahtumat.hel.fi/fi/events/'.$eventId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // When batch is full → save to DB
                if (count($rows) >= $batchSize) {
                    $this->bulkUpsert($rows);
                    $totalSaved += count($rows);
                    $rows = [];
                }
            }

            // next page
            $url = $response->json('meta.next') ?? null;

        } while (!empty($url));

        // Save remaining rows
        if (!empty($rows)) {
            $this->bulkUpsert($rows);
            $totalSaved += count($rows);
        }

        return $totalSaved;
    }

    public function fetch()
    {
        set_time_limit(0);
        ignore_user_abort(true);

        $urls = [
            'babies' => 'https://api.hel.fi/linkedevents/v1/event/?division=ocd-division%2Fcountry%3Afi%2Fkunta%3Ahelsinki&event_type=General&include=keywords%2Clocation&keyword_OR_set3=yso%3Ap20513%2Cyso%3Ap15937&ongoing=true&page_size=25&sort=end_time&start=now',
            'children' => 'https://api.hel.fi/linkedevents/v1/event/?division=ocd-division%2Fcountry%3Afi%2Fkunta%3Ahelsinki&event_type=General&include=keywords%2Clocation&keyword_OR_set3=yso%3Ap4354%2Cyso%3Ap13050&ongoing=true&page_size=25&sort=end_time&start=now'
        ];

        $totalSaved = 0;

        foreach($urls as $audience=>$url){
            $totalSaved  += $this->fetchFromUrl($audience, $url);

        }
        $babiesIds   = array_unique($this->fetchedEventIds['babies']);
        $childrenIds = array_unique($this->fetchedEventIds['children']);
        $overlapIds = array_intersect($babiesIds, $childrenIds);
        
        return response()->json([
            'success' => true,
            'message' => "Events synced successfully: {$totalSaved} rows",
            'stat' => [
                'total_saved'      => $totalSaved,
                'babies_count'     => count($babiesIds),
                'children_count'   => count($childrenIds),
                'overlap_count'    => count($overlapIds),
            ]
        ]);
    }

    /**
     * Bulk insert / update helper
     */
    private function bulkUpsert(array $events): void
    {
        $events = $this->uniqueByEventId($events);

        Event::upsert($events, ['event_id'], // unique key 
        [
                        'name',
                        'event_for',
                        'start_time',
                        'end_time',
                        'location',
                        // 'location_extra_info',
                        'audience_min_age',
                        'audience_max_age',
                        'price',
                        'short_description',
                        'publisher_name',
                        'updated_at'
        ]);
    }


    private function uniqueByEventId(array $events): array
    {
        $unique = [];

        foreach ($events as $event) {
            if (!empty($event['event_id'])) {
                $unique[$event['event_id']] = $event; 
                // If duplicate appears, latest one overwrites previous
            }
        }

        return array_values($unique);
    }


    private function getPrice(array $offers = []): ?string
    {
        if (empty($offers)) {
            return null; // No offers
        }

        // Take the first offer (most APIs only have one main offer)
        $offer = $offers[0];

        if (empty($offer)) {
            return null;
        }

        // Price can be null, string, or object with language keys
        $price = $offer['price'] ?? null;

        if (is_array($price)) {
            // Use Finnish first, fallback to English 
            return $price['fi'] ?? $price['en'] ?? null;
        }

        // Can be string or null
        return is_string($price) ? $price : null;
    }

    private function getPublisherName(?string $publisherID): ?string
    {
        // steps
        // if empty publisher ID return null
        // return cached data from in memory array if already fetched before without api call
        // try catch block  and inside try if found name against ID then cached it again inside array.

        if(!$publisherID){
            return null;
        }

        if(isset($this->publisherCache[$publisherID])){
            return $this->publisherCache[$publisherID];
        }

        $url = "https://api.hel.fi/linkedevents/v1/organization/{$publisherID}";

        try {
            $response = Http::timeout(5)->get($url);

            if ($response->failed()) {
                return null;
            }

            $name = $response->json('name') ?? null;

            // store in cache
            return $this->publisherCache[$publisherID] = $name;

        } catch (\Exception $e) {
            return null;
        }
    }


    private function getLocation(?string $locationID): ?string
    {
        if (!$locationID) {
            return null;
        }

        // Return from cache if already fetched
        if (isset($this->locationCache[$locationID])) {
            return $this->locationCache[$locationID];
        }

        try {
            $response = Http::timeout(10)->get($locationID);

            if ($response->failed()) {
                return null;
            }

            $data = $response->json();

            // Extract fields safely
            $name     = $data['name']['fi'] ?? null;
            $street   = $data['street_address']['fi'] ?? null;
            $city     = $data['address_locality']['fi'] ?? null;

            // Build location string
            $parts = array_filter([$name, $street, $city]);
            $location = !empty($parts) ? implode(', ', $parts) : null;

            // Cache result
            $this->locationCache[$locationID] = $location;

            return $location;

        } catch (\Exception $e) {
            return null;
        }
    }

}
