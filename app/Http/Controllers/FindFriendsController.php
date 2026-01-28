<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Notifications\FriendRequestReceived;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\UserFriend;
use App\Models\User;

class FindFriendsController extends Controller
{
    public function __construct()
    {

    }

    public function friendRequest(Request $request)
    {
        $user = Auth::user();
        $receiver = User::findOrFail($request->friend_id);
        try{
            DB::beginTransaction();

            $data = UserFriend::create([
            'user_id' => $user->id,
            'friend_id' => $request->friend_id,
            'request_status' => 'pending'
        ]);

        DB::commit(); // Commit if all good
        // Send email notification
        $receiver->notify(new FriendRequestReceived($user));

        return response()->json([
            'success' => true,
            'message' => 'Friend Request Sent Successfully',
            'data' => $data
            
        ],200);
        }
        catch(Throwable $e){
            DB::rollback();
            return response()->json([
            'message' => 'An error occurred. Request Not Sent.',
           
        ],500);
        }
    }
}
