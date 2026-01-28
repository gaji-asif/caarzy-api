<?php
namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if ($request->has('peer_id')) {
            $peerId = $request->query('peer_id'); // the other user

        // simple 1:1 fetch: messages between authenticated user and peer
        $messages = Message::where(function($q) use ($user, $peerId) {
            $q->where('sender_id', $user->id)->where('receiver_id', $peerId);
        })->orWhere(function($q) use ($user, $peerId) {
            $q->where('sender_id', $peerId)->where('receiver_id', $user->id);
        })->orderBy('created_at')->get();

        return response()->json([
            'success' => true,
            'message' => 'messages',
            'data' => $messages
    ], 201);
        }
        if ($request->has('conversation_id')) {
        $conversation_id = $request->conversation_id;

        $messages = Message::with(['sender:id,name']) // this load sender id, name
            ->where('conversation_id', $conversation_id)
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'messages',
            'data' => $messages
    ], 201);
    }
    return response()->json([
        'error' => 'peer_id or conversation_id required'
    ], 422);
        
    }


    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'nullable|exists:users,id',
            'message_body' => 'required|string|max:2000',
            'conversation_id'    => 'nullable',
        ]);

         // Must have either receiver_id OR group_id
        if (!$request->receiver_id && !$request->conversation_id) {
            return response()->json([
                'error' => 'receiver_id or conversation_id is required'
            ], 422);
        }

        $user = $request->user();

        $message = Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $request->receiver_id, // null for group chat
            'conversation_id'     => $request->conversation_id,    // null for private chat
            'message_body' => $request->message_body,
        ]);
       // \Log::info('🚀 Broadcasting now for message ID: ' . $message->id);
        //broadcast(new MessageSent($message))->toOthers();
        return response()->json([
            'success' => true,
            'message' => 'message sent',
            'data' => $message
    ], 201);
    }
    
    public function privateMessageLists(Request $request){
         $userId = auth()->id();

    // Subquery: find the latest message per chat partner
    $subQuery = Message::select(
            DB::raw('CASE 
                        WHEN sender_id = ' . $userId . ' THEN receiver_id 
                        ELSE sender_id 
                    END AS chat_user_id'),
            DB::raw('MAX(id) AS last_message_id')
        )
        ->where(function ($query) use ($userId) {
            $query->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
        })
        ->groupBy('chat_user_id');

    // Main query: join messages, users, and user_profiles
    $chats = Message::joinSub($subQuery, 'latest', 'messages.id', '=', 'latest.last_message_id')
        ->join('users', 'users.id', '=', 'latest.chat_user_id')
        ->leftJoin('user_profiles', 'user_profiles.user_id', '=', 'users.id')
        ->select(
            'users.id as user_id',
            'users.name',
            'user_profiles.users_img_url', // ✅ from user_profiles
            'messages.message_body as last_message',
            'messages.created_at as last_message_time'
        )
        ->orderByDesc('messages.created_at')
        ->get();

     if ($chats) {
            return response()->json([
                'success' => true,
                'message' => 'Chats found successfully',
                'data' => $chats,

            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'not Found',
                'data' => [],
            ]);
        }
    }
}

