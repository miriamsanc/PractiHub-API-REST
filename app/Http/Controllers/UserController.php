<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    // Ver perfil 
    public function show(User $user): UserResource
    {
        // Verificacion que el perfil es de estudiante y no de empresa
        if ($user->role !== 'student') {
            abort(404, 'Student profile not found');
        }

        Gate::authorize('view', $user);

        return new UserResource($user);
    }

    // Actualizar perfil
    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        // 1. Validamos que el endpoint sea el correcto (Estudiante)
        if ($user->role !== 'student') {
            abort(404, 'Student profile not found');
        }

        //Uso de la policy (verifica si puede hacer update, si da false devuelve un 403)
        Gate::authorize('update', $user);
            
        $user->update($request->validated());

        return new UserResource($user);
    }

    // Eliminar perfil
    public function destroy(User $user): JsonResponse
    {
        if ($user->role !== 'student') {
            return response()->json(['message' => 'Not a student profile'], 404);
        }
    
        // USO POLICY
        Gate::authorize('delete', $user);

        $user->delete();

        return response()->json(['message' => 'Profile deleted successfully'], 200);
    }
}
