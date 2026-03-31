<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse {
        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','string','max:255'],
            'password' => ['required','string','min:8',"confirmed"],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user'
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if(!Auth::attempt($validated)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ],401);
        }

        $user = User::where('email', $validated['email'])->first();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User logged in successfully.',
            'user'=>$user,
            'token'=>$token,
        ],200);
    }

    public function profile(Request $request): JsonResponse {
        return response()->json([
            'user' => $request->user(),
        ], 200);
    }

    public function updateProfile(Request $request): JsonResponse {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255',Rule::unique('users','email')->ignore($user->id)],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user,
        ],200);
    }

    public function logout(Request $request): JsonResponse {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'User logged out successfully.',
        ], 200);
    }
}
