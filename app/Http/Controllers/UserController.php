<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Ver perfil 
    public function show(User $user)
    {
        // Verificacion que el perfil es de estudiante y no de empresa
        if ($user->role !== 'student') {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        return response()->json($user, 200);
    }

    // Actualizar perfil
    public function update(Request $request, User $user)
    {
        if ($request->user()->id !== $user->id) {
            return response()->json(['message' => 'Forbidden: You can only edit your own profile'], 403);
        }

        // Aqui sometimes para que solo valide el campo si se envía en la petición.
        // En email ignora el ID del usuario actual para la regla unique
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'cv_path' => 'nullable|string',
        ]);

        $user->update($validatedData);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ], 200);
    }

    // Eliminar perfil
    public function destroy(Request $request, User $user)
    {
        if ($request->user()->id !== $user->id) {
            return response()->json(['message' => 'Forbidden: You can only delete your own profile'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Profile deleted successfully'], 200);
    }
}
