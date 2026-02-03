<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarBrandController;
use App\Http\Controllers\CarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::middleware('auth:sanctum')->group(function () {
    // Route::get('/email-verified', [AuthController::class, 'isEmailVerified']);
    // Route::post('/user/profile', [UserController::class, 'updateProfile']);

    // // users route
    // Route::get('/user/profile', [UserController::class, 'userProfile']);

    // get all users list
    // Route::get('/users', [UserController::class, 'getAllUsers']);

    // Route::post('/logout', [AuthController::class, 'logout']);
    // Route::post('/account/deactivate', [AuthController::class, 'deactivate']);


    // // chat groups

    // Route::get('/all-chat-groups', [ChatGroupController::class, 'allChatGroups']);
    // Route::post('/add-group', [ChatGroupController::class, 'addGroup']);
    // Route::get('/chat-groups/{id}/edit', [ChatGroupController::class, 'editGroup']);
    // Route::put('/chat-groups/{id}', [ChatGroupController::class, 'updateGroup']);
    // Route::delete('/delete-chat-group/{id}', [ChatGroupController::class, 'deleteChatGroup']); // need to make this one
    // Route::post('/join-group-request', [ChatGroupController::class, 'joinGroupRequest']);
    // Route::get('/my-groups', [ChatGroupController::class, 'myGroups']);

    // messages
    // Route::get('/messages', [MessageController::class, 'index']);
    // Route::post('/messages', [MessageController::class, 'store']);

    //car brands
    Route::get('/all-car-models', [CarBrandController::class, 'allBrands']);

     //cars
    Route::get('/all-cars', [CarController::class, 'allcars']);
});

// Auth Route
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
// Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
// Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
// verify email
Route::get('/auth/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])->middleware('signed')->name('verification.verify');

// resend Email
Route::post('/auth/resend-verification', [AuthController::class, 'resendVerification']);
// End Auth Route





