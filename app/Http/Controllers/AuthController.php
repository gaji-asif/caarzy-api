<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class AuthController extends Controller
{
    
    // contructor Function

    public function __construct()
    {
        // write contructor work
    }

    /* register API

     @return Illuminate/Http/response
     */

    public function register(RegisterRequest $request): JsonResponse
    {
        
        // $attr = $request->validated();
        // $location = geoip()->getLocation($request->ip());
        // dd($location);
        try {
            DB::beginTransaction();

            $data = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
            ]);

            if($request->filled(['lat', 'lng'])){
                $this->updateUserLocation($request, $data); // pass user explicitly
            }

            DB::commit(); // Commit if all good
            //event(new Registered($data)); // Trigger email verification

            return response()->json([
                'success' => true,
                'message' => 'User Profile Created Successfully',
                'data' => $data,

            ]);

        } catch (Throwable $e) {
            DB::rollback();

            return response()->json([
                'message' => 'An error occurred. Post creation failed.',

            ], 500);
        }

    }

    /*  login API

       @return @Illuminate\Http\Response
       */

    public function login(LoginRequest $request): JsonResponse
    {
        $attr = $request->validated();
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,

        ];
        if (Auth::attempt($credentials)) {
            $user = Auth::user();

             //  Block inactive users
            if (!$user->is_active) {
                Auth::logout(); // ensure no session/token is created

                return response()->json([
                    'success' => false,
                    //'message' => 'Your account has been deactivated. Please contact support.', 
                    'message' => 'Tilisi on poistettu käytöstä. Ota yhteyttä tukeen.',
                    
                ], 403); // 403 Forbidden
            }
            
            //  Update location if lat/lng are sent
            // if ($request->filled(['lat', 'lng'])) {
            //     $this->updateUserLocation($request, $user);
            // }
            // generate a new token for auth user
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'User logged in Successfully',
                'data' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'unathorized',
                'data' => [],
            ]);
        }
    }

    /**
     * function for logout from all devices
     */
    public function logout(Request $request): JsonResponse
    {
        // delete all access tokens for the current user
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'logged out successfully',
        ]);
    }

    public function verifyEmail($id, $hash, Request $request)
    {
        // Find user by ID
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        // Verify if the hash is correct
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'message' => 'Invalid verification link.',
            ], 403);
        }

        // Mark email as verified
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return redirect()->away(config('app.frontend_url').'/email-verified');

    }

    public function resendVerification(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.',
            ], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification link resent!',
        ]);
    }

    public function isEmailVerified(Request $request)
    {
        $user = $request->user(); // get all details of authenticated users data - from token

        return response()->json([
            'email' => $user->email,
            'verified' => $user->hasVerifiedEmail(),
            'verified_at' => $user->email_verified_at,
        ]);
    }

    public function deactivate(Request $request)
    {
        $user = $request->user(); // Get logged-in user

        // 2. Prevent re-deactivation
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is already deactivated.',
            ]);
        }

        $user->is_active = false;
        $user->save();

        // Optional: logout all devices
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Your account has been deactivated.',
        ]);
    }
}
