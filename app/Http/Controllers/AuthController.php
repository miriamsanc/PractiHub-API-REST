<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

/**
 * @group Authentication
 *
 * Public endpoints for registration, login and logout via Passport (Bearer Token).
 */

class AuthController extends Controller
{    
    /** 
     * Register user
     * 
     * Creates a new account in the system. The role must be either 'student' or 'company'.
     * 
     * @unauthenticated
     */

    public function register(RegisterRequest $request): JsonResponse
    {
        // Validacion de los datos entrantes en registerrequest
        $validatedData = $request->validated();

        // Creacion usuario encriptando contraseña
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'],
        ]);

        // Generacion token Passport
        $token = $user->createToken('auth_token')->accessToken;

        // Devolvemos la respuesta con código 201 (Created)
        return response()->json([
            'user' => new UserResource($user),
            'token' => $token
        ], 201);
    }

    /**
     * Login user
     * 
     * Authenticates a user and returns a Passport access token to be used in protected routes.
     * 
     * @unauthenticated
     */

    public function login(LoginRequest $request): JsonResponse
    {
        // Comprobacion credenciales
        if (!Auth::attempt($request->validated())) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Si es correcto obtiene el usuario y genera token
        $user = User::where('email', $request->validated('email'))->firstOrFail();
        $token = $user->createToken('auth_token')->accessToken;

        // Devuelve respuesta con código 200 (OK)
        return response()->json([
            'user' => new UserResource($user),
            'token' => $token
        ], 200);
    }

    /**
     * Logout user
     * 
     * Revokes the current authenticated user's token.
     * 
     * @authenticated
     */

    public function logout(Request $request): JsonResponse
    {
        // Obtiene el token actual con el que el usuario hizo la petición y lo revoca
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }

}
