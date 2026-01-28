<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatGroupController;
use App\Http\Controllers\FindFriendsController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\FriendsController;
use App\Http\Controllers\EventSyncController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::middleware('auth:sanctum', 'verified')->group(function(){
//     // all protected routes
// });

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/email-verified', [AuthController::class, 'isEmailVerified']);
    Route::post('/user/profile', [UserController::class, 'updateProfile']);

    // users route
    Route::get('/user/profile', [UserController::class, 'userProfile']);
    Route::get('/user-friends', [UserController::class, 'userFriendLists']);
    Route::get('/user-received-friend-requests', [UserController::class, 'getReceivedFriendRequests']);
    Route::get('/user-sent-friend-requests', [UserController::class, 'getSentFriendRequests']);
    Route::post('/leave-group', [UserController::class, 'leaveGroup']);

    // get all users list
    Route::get('/users', [UserController::class, 'getAllUsers']);

    // find Friends
    Route::post('/friend-request', [FindFriendsController::class, 'friendRequest']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/account/deactivate', [AuthController::class, 'deactivate']);


    // chat groups

    Route::get('/all-chat-groups', [ChatGroupController::class, 'allChatGroups']);
    Route::post('/add-group', [ChatGroupController::class, 'addGroup']);
    Route::get('/chat-groups/{id}/edit', [ChatGroupController::class, 'editGroup']);
    Route::put('/chat-groups/{id}', [ChatGroupController::class, 'updateGroup']);
    Route::delete('/delete-chat-group/{id}', [ChatGroupController::class, 'deleteChatGroup']); // need to make this one
    Route::post('/join-group-request', [ChatGroupController::class, 'joinGroupRequest']);
    Route::get('/my-groups', [ChatGroupController::class, 'myGroups']);

    // messages
    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);

    // friends
    Route::post('/remove-friend', [FriendsController::class, 'removeFriend']);

    Route::post('/accept-friend-request', [FriendsController::class, 'acceptFriendRequest']);
    Route::post('/reject-friend-request', [FriendsController::class, 'rejectFriendRequest']);

    Route::post('/cancel-friend-request', [FriendsController::class, 'cancelFriendRequest']);

    // private chats/Messages
    Route::get('/private-message-lists', [MessageController::class, 'privateMessageLists']);
    
    //events
    Route::get('/all-events', [EventController::class, 'allEvents']);
    Route::get('/sync-events', [EventSyncController::class, 'fetch']);
    Route::post('/add-event', [EventController::class, 'addEvent']);
    

    //services module
    Route::post('/add-service', [ServiceController::class, 'addService']);
    Route::get('/all-services', [ServiceController::class, 'allServices']);

    //category
    Route::post('/add-category', [CategoryController::class, 'addCategory']);
    Route::get('/all-categories', [CategoryController::class, 'allCategories']);
});

// Auth Route
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
// verify email
Route::get('/auth/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])->middleware('signed')->name('verification.verify');

// resend Email
Route::post('/auth/resend-verification', [AuthController::class, 'resendVerification']);
// End Auth Route

// users route


// Settings Route
Route::get('/interests', [SettingsController::class, 'getInterests']);
Route::get('/languages', [SettingsController::class, 'getLanguages']);
Route::get('/children-age-range', [SettingsController::class, 'childrenAgeRange']);
Route::post('/testUploadImage', [UserController::class, 'testUploadImage']);

// Route::post('/debug-auth', function (\Illuminate\Http\Request $request) {
//     \Log::info('Auth debug', [
//         'user' => $request->user(),
//         'token' => $request->bearerToken(),
//     ]);
//     return response()->json(['user' => $request->user()]);
// })->middleware('auth:sanctum');


// Route::middleware('auth:sanctum')->post('/broadcasting/auth', function (Request $request) {
//     \Log::info('Broadcast auth attempt', [
//         'user_id' => optional($request->user())->id,
//         'channel_name' => $request->input('channel_name'),
//     ]);
//    return Broadcast::auth($request);
// });

