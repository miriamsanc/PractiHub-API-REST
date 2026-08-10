<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validacion de los datos entrantes
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:student,company',
        ]);

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
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Comprobacion credenciales
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Si es correcto obtiene el usuario y genera token
        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->accessToken;

        // Devuelve respuesta con código 200 (OK)
        return response()->json([
            'user' => $user,
            'token' => $token
        ], 200);
    }
}
