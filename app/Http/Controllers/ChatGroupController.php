<?php

namespace App\Http\Controllers;

use App\Models\ChatGroup;
use App\Models\UserGroup;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ChatGroupController extends Controller
{
    public function allChatGroups(Request $request)
    {
        $name = $request->input('name');
        $groups = ChatGroup::query()
            ->when($name, function ($query, $name) {
                $query->where('name', 'ILIKE', '%'.$name.'%');
            })
            ->select('chat_groups.*', DB::raw('(SELECT COUNT(*) FROM user_groups ug WHERE ug.group_id = chat_groups.id) AS members_count'))
            ->get();
        if (! $groups) {
            return response()->json([
                'success' => false,
                'message' => 'groups not found',
                'data' => [],
            ], 404);

        }

        return response()->json([
            'success' => true,
            'message' => 'groups found',
            'data' => $groups,
        ], 200);
    }

    public function addGroup(Request $request)
    {
        $user = Auth::user();
        try {
            DB::beginTransaction();

            // need to add validation for request. right now not validation applied.

            $data = ChatGroup::create([
                'name' => $request->name,
                'group_type' => $request->group_type,
                'description' => $request->description,
                'created_by' => $user->id,
            ]);
            DB::commit(); // Commit if all good

            return response()->json([
                'success' => true,
                'message' => 'Group Created Successfully',
                'data' => $data,

            ]);

        } catch (Throwable $e) {
            DB::rollback();

            return response()->json([
                'message' => 'An error occurred. Post creation failed.',

            ], 500);
        }
    }

    /**
     * Edit Each chat group.
     * @param  int  $id                             The ID of the chat group for retrieve.
     * 
     * @return \Illuminate\Http\JsonResponse        JSON response containing group details
     *                                         or an error message if unauthorized or not found
     *
     */

    public function editGroup($id)
    {
        // Find group
        $group = ChatGroup::find($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not Found',
                'data' => [],
            ], 404);
        }

        // Authorization check
        if ($group->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        // Success
        return response()->json([
            'success' => true,
            'message' => 'Group details fetched successfully',
            'data' => $group
        ], 200);
    }

    /**
     * Update chat group details.
     *
     * @param  \Illuminate\Http\Request  $request   Incoming request containing new group data.
     * @param  int  $id                             The ID of the chat group to update.
     * 
     * @return \Illuminate\Http\JsonResponse        JSON response containing success or error message.
     *
     * -----------------------------------------
     * SAMPLE SUCCESS RESPONSE:
     * {
     *     "status": true,
     *     "message": "Group updated successfully",
     *     "data": {
     *          "id": 1,
     *          "name": "New Group Name",
     *          "description": "Updated description",
     *          "created_by": 5
     *     }
     * }
     * -----------------------------------------
     * SAMPLE ERROR RESPONSE:
     * {
     *     "status": false,
     *     "message": "Unauthorized action."
     * }
     * -----------------------------------------
     */


   public function updateGroup(Request $request, $id)
    {
        // Validate incoming request data
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Fetch the group by ID, or return 404 if not found
        $group = ChatGroup::find($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found',
                'data' => [],
            ], 404);
        }

        // Ensure only group creator can update it
        if ($group->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        // Update the group details
        $group->name = $request->name;
        $group->description = $request->description;
        $group->save();

        // Return success response
        return response()->json([
            'success' => true,
            'message' => 'Group updated successfully',
            'data' => $group,
        ], 200);
    }

    /**
     * Delete chat group.
     *
     * @param  int  $id                             The ID of the chat group to delete.
     * 
     * @return \Illuminate\Http\JsonResponse        JSON response containing success or error message.
     *
     * -----------------------------------------
     * SAMPLE SUCCESS RESPONSE:
     * {
     *     "status": true,
     *     "message": "Group Deleted successfully",
     *
     * }
     * -----------------------------------------
     * SAMPLE ERROR RESPONSE:
     * {
     *     "status": false,
     *     "message": "Unauthorized action."
     * }
     * -----------------------------------------
     */

    public function deleteChatGroup($id)
    {
       try {
            $group = ChatGroup::find($id);
            if (!$group) {
                return response()->json([
                        'success' => false,
                        'message' => 'Chat group not found.',
                    ], 404);
            }

            // Ensure only group creator can delete it
            if ($group->created_by !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.',
                ], 403);
            }
            
            // Check if group has messages
                $hasMessages = Message::where('conversation_id', $id)->exists(); // here conversation_id means group id

                if ($hasMessages) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This group cannot be deleted because it contains chat messages.'
                    ], 400);
                }

                // If no messages, allow deletion
                $group->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Chat group deleted successfully.',
                ], 200);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong.',
                    'error'   => $e->getMessage(),
                ], 500);
            }     
    }


    public function joinGroupRequest(Request $request)
    {
        $user = Auth::user();
        try {
            DB::beginTransaction();

            $data = UserGroup::create([
                'user_id' => $user->id,
                'group_id' => $request->group_id,
                'request_status' => 1,
            ]);

            DB::commit(); // Commit if all good

            return response()->json([
                'success' => true,
                'message' => 'You have joined this group Successfully',
                'data' => $data,

            ], 200);

        } catch (Throwable $e) {
            DB::rollback();

            return response()->json([
                'message' => 'An error occurred. Request Not Sent.',

            ], 500);
        }
    }

    public function myGroups(Request $request){
        
    $user = Auth::user();// Authenticated user
        // Get the group IDs the user belongs to
    $groupIds = DB::table('user_groups')
    ->where('user_id', $user->id)
    ->pluck('group_id');

    // Fetch group info + member count (no duplicate alias)
    $groups = ChatGroup::whereIn('id', $groupIds)
        ->select('chat_groups.*', DB::raw('(SELECT COUNT(*) FROM user_groups ug WHERE ug.group_id = chat_groups.id) AS members_count'))
        ->get();

        if ($groups->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'groups not found',
                'data' => [],
            ], 404);

        }

        return response()->json([
            'success' => true,
            'message' => 'groups found',
            'data' => $groups,
        ], 200);
    }
}
