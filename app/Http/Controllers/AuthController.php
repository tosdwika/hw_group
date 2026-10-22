<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Registrasi user baru
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = auth('api')->login($user);

        return $this->respondWithToken($token, 201);
    }

    // Login user dan buat token JWT
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        return $this->respondWithToken($token);
    }

    // Ambil data user yang sedang login
    public function me(): JsonResponse
    {
        return response()->json(auth('api')->user());
    }

    // Logout user dan destroy token
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }

    // Perbarui token JWT
    public function refresh(): JsonResponse
    {
        $newToken = auth('api')->refresh();

        return $this->respondWithToken($newToken);
    }

    // Format response token JWT
    protected function respondWithToken(string $token, int $status = 200): JsonResponse
    {
        $guard = auth('api');

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $guard->factory()->getTTL() * 60,
        ], $status);
    }
}
