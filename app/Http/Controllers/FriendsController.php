<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserFriend;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FriendsController extends Controller
{
    public function acceptFriendRequest(Request $request){

        $request->validate([
            'friend_id' => 'required|exists:users,id',
        ]);

        $userId = auth()->id(); // the currently logged-in user
        $friendId = $request->friend_id;

        // find the friend request where this user is the receiver
        $friendRequest = UserFriend::where('user_id', $friendId)
            ->where('friend_id', $userId)
            ->where('request_status', 'pending')
            ->first();

        if (!$friendRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Friend request not found'
            ], 404);
        }

        // Update request status
        $friendRequest->update(['request_status' => 'accepted']);
        
        return response()->json([
            'success' => true,
            'message' => 'Friend request accepted successfully'
        ]);

    }

    public function rejectFriendRequest(Request $request){

        $request->validate([
            'friend_id' => 'required|exists:users,id',
        ]);

        $userId = auth()->id(); // the currently logged-in user
        $friendId = $request->friend_id;

        // find the friend request where this user is the receiver
        $friendRequest = UserFriend::where('user_id', $friendId)
            ->where('friend_id', $userId)
            ->where('request_status', 'pending')
            ->first();

        if (!$friendRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Friend request not found'
            ], 404);
        }

        // Update request status
        $friendRequest->update(['request_status' => 'rejected']);
        
        return response()->json([
            'success' => true,
            'message' => 'Friend request rejected successfully'
        ]);

    }

    public function cancelFriendRequest(Request $request){
        $request->validate([
            'friend_id' => 'required|exists:users,id',
        ]);

        $userId = auth()->id(); // the currently logged-in user
        $friendId = $request->friend_id;

        // find the friend request where this user is the sender
        $friendRequest = UserFriend::where('user_id', $userId)
            ->where('friend_id', $friendId)
            ->where('request_status', 'pending')
            ->first();

        if (!$friendRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Friend request not found'
            ], 404);
        }

        // Update request status
        $friendRequest->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Friend request cancelled successfully'
        ]);
    }


    public function removeFriend(Request $request)
    {
        $user = Auth::user();

        // Validate input
        $request->validate([
            'friend_id' => 'required|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            // Find the accepted friendship (either direction)
            $friendship = UserFriend::where(function($query) use ($user, $request) {
                    $query->where('user_id', $user->id)
                        ->where('friend_id', $request->friend_id);
                })
                ->orWhere(function($query) use ($user, $request) {
                    $query->where('user_id', $request->friend_id)
                        ->where('friend_id', $user->id);
                })
                ->where('request_status', 'accepted')
                ->first();

            if (!$friendship) {
                return response()->json([
                    'success' => false,
                    'message' => 'Friend not found or not yet accepted.',
                ], 404);
            }

            // Delete the friendship
            $friendship->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Friend removed successfully.',
            ], 200);

        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while removing the friend.',
                'error' => $e->getMessage(), // optional, remove in production
            ], 500);
        }
    }

}
