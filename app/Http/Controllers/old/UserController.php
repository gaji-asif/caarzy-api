<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct() {}

    public function updateProfile(UpdateProfileRequest $request)
    {

        $user = Auth::user();
        try {
            DB::beginTransaction();
            if ($request->has('name')) {
                $user->name = $request->name;
            }
            $user->save();

            // Update profile table (create if not exists)
            $profile = $user->profile ?: $user->profile()->create();
            $profile->user_id = $user->id;
            $profile->fill($request->only(['postcode', 'location', 'bio', 'children_age_range', 'is_pregnent', 'interests', 'language']));
            // Handle image upload
            if ($request->hasFile('users_img_url')) {
                $path = $request->file('users_img_url')->store('profiles', 'public');
                $profile->users_img_url = asset('storage/'.$path);
            }

            $profile->save();
            DB::commit(); // Commit if all good

            return response()->json([
                'success' => true,
                'message' => 'User Profile Updated Successfully',
                'data' => $user,

            ]);
        } catch (Throwable $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Not Updated',
                'data' => [],
            ]);
        }
    }

    public function getAllUsers(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->lat || !$user->lng) {
        return response()->json([
                'success' => false,
                'message' => 'User location not available',
                'data' => []
            ], 400);
         } 
        $name = $request->query('name');
        $selectedInterests = $request->input('interests', []); // array from frontEnd
        $selectedAges = $request->input('ages', []); // array from frontEnd
        $users = User::with('profile') // eager load the profile
            ->withFriendStatus($user->id)
            ->where('id', '!=', $user->id)
            ->where('is_active', true)
            ->when($name, function ($query, $name) {
                $query->where('name', 'ILIKE', "%{$name}%");
            })
            ->when($request->postcode, function ($query, $postcode) {
                $query->whereHas('profile', function ($query) use ($postcode) {
                    $query->where('postcode', 'ILIKE', "%{$postcode}%");
                });
            })
            ->when($selectedInterests, function ($query, $selectedInterests) {
                $query->whereHas('profile', function ($q) use ($selectedInterests) {
                    // Match any interest (OR)
                    $q->where(function ($sub) use ($selectedInterests) {
                        foreach ($selectedInterests as $interest) {
                            $sub->orWhereJsonContains('interests', $interest);
                        }
                    });
                });
            })
            ->when($selectedAges, function ($query, $selectedAges) {

                $now = new \DateTime();
                $includePregnant = false;
                $targetIds = [];

                foreach ($selectedAges as $key) {

                    switch ($key) {
                        case 0: // Pregnant
                            $includePregnant = true;
                            break;

                        case 1: // 0–6 months
                            $ranges = range(0, 5);
                            break;

                        case 2: // 6–12 months
                            $ranges = range(6, 11);
                            break;

                        case 3: // 1 year (12–23)
                            $ranges = range(12, 23);
                            break;

                        case 4: // 2 years (24–35)
                            $ranges = range(24, 35);
                            break;

                        case 5: // 3 years+ (36–120)
                            $ranges = range(36, 120);
                            break;

                        default:
                            $ranges = [];
                    }

                    // Convert months → m.Y → children_age_ranges.id
                    foreach ($ranges as $months) {
                        $date = (clone $now)->modify("-$months months")->format('m.Y');

                        $id = DB::table('children_age_ranges')
                                ->where('name', $date)
                                ->value('id');

                        if ($id) {
                            $targetIds[] = (string)$id; // stored as string in JSON
                        }
                    }
                }

                $targetIds = array_unique($targetIds);

                $query->whereHas('profile', function ($q) use ($targetIds, $includePregnant) {
                    $q->where(function ($sub) use ($targetIds, $includePregnant) {

                        foreach ($targetIds as $id) {
                            $sub->orWhereJsonContains('children_age_range', $id);
                        }

                        if ($includePregnant) {
                            $sub->orWhere('is_pregnent', 1);
                        }
                    });
                });
            })
            ->get();

        if ($users) {
            return response()->json([
                'success' => true,
                'message' => 'User lists found successfully',
                'data' => $users,

            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'not Found',
                'data' => [],
            ]);
        }
    }

    /**
     * Get single user profile details.
     *
     * @param  none
     * @return JsonResponse
     */
    public function userProfile(Request $request)
    {
        // this section is used in NewChat.tsx page for showing the users details like image and name
        //If a specific user_id is passed (for peer), use that
        $targetUserId = $request->input('user_id');

        if ($targetUserId) {
            $user = User::with('profile')->find($targetUserId);

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data' => [],
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Peer user found successfully',
                'data' => $user,
            ]);
        }
        // End Section
        
        $user = Auth::user();
        $data = User::with(['profile', 'chatGroups'])->find($user->id);

        // Count accepted friends (bidirectional)
        $friendsCount = \DB::table('user_friends')
            ->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                ->orWhere('friend_id', $user->id);
            })
            ->where('request_status', 'accepted')
            ->count();

        // Count pending friend requests (incoming to user)
        $pendingRequestsCount = \DB::table('user_friends')
            ->where('friend_id', $user->id)
            ->where('request_status', 'pending')
            ->count();

        // Add counts to response data
        $data->friends_count = $friendsCount;
        $data->pending_requests_count = $pendingRequestsCount;

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'user not Found',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'User found successfully',
            'data' => $data,

        ]);
    }

    public function userFriendLists()
    {
        $user = Auth::user();// Get IDs of friends in both directions
    $friendIds = \DB::table('user_friends')
    ->where('request_status', 'accepted')
    ->where(function ($q) use ($user) {
        $q->where('user_id', $user->id)
          ->orWhere('friend_id', $user->id);
    })
    ->get()
    ->map(function ($row) use ($user) {
        // Return the other user's id
        return $row->user_id == $user->id ? $row->friend_id : $row->user_id;
    });

    // Fetch user models with profile
    $users = User::with('profile')->whereIn('id', $friendIds)->get();

        if ($users) {
            return response()->json([
                'success' => true,
                'message' => 'User lists found successfully',
                'data' => $users,

            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'not Found',
                'data' => [],
            ]);
        }
    }

    public function getReceivedFriendRequests()
    {
        $user = Auth::user();
        $users = $user->receivedFriendRequests()->withPivot('request_status')->wherePivot('request_status', 'pending')->with(['profile'])->get();    // need to optimize this codebase later 03.11.2025
        if ($users) {
            return response()->json([
                'success' => true,
                'message' => 'User lists found successfully',
                'data' => $users,

            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'not Found',
                'data' => [],
            ]);
        }
    }

    public function getSentFriendRequests()
    {
        $user = Auth::user();
        $users = $user->sentFriendRequests()->withPivot('request_status')->wherePivot('request_status', 'pending')->with(['profile'])->get();    // need to optimize this codebase later 03.11.2025
        if ($users) {
            return response()->json([
                'success' => true,
                'message' => 'User lists found successfully',
                'data' => $users,

            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'not Found',
                'data' => [],
            ]);
        }
    }

    public function testUploadImage(Request $request)
    {

        // return $request->file('users_img_url');
        // exit;

        return response()->json([
            'success' => true,
            'message' => "all good",

        ]);

        $user = Auth::user();
        $path = '';
        $profile = $user->profile ?: $user->profile()->create();
        $profile->user_id = 1;
        // $profile->fill($request->only(['children_age_range','location','bio', 'language', 'interests']));

        // Handle image upload
        if ($request->hasFile('users_img_url')) {
            $path = $request->file('users_img_url')->store('profiles', 'public');
            $profile->users_img_url = asset('storage/'.$path);
        }
        $profile->save();

        return response()->json([
            'success' => true,
            'url' => asset('storage/'.$path),

        ]);

    }

    
    public function leaveGroup(Request $request)
    {
    $request->validate([
        'group_id' => 'required|integer|exists:chat_groups,id',
    ]);

    $user = Auth::user();

    // Delete row from pivot table
    $deleted = \DB::table('user_groups')
        ->where('user_id', $user->id)
        ->where('group_id', $request->group_id)
        ->delete();

    if ($deleted) {
       return response()->json([
                'success' => true,
                'message' => 'You have left the group successfully.'
               
            ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'You are not a member of this group or already left.'
    ], 404);
}

}
