<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;


class UserController extends Controller
{
    /**
     * @group Students
     * 
     * Get student profile
     * 
     * Retrieves the details of a specific student account.
     * 
     * @authenticated
     */ 
    public function show(User $user): UserResource
    {
        // Verificacion que el perfil es de estudiante y no de empresa
        if ($user->role !== 'student') {
            abort(404, 'Student profile not found');
        }

        Gate::authorize('view', $user);

        return new UserResource($user);
    }

    /**
     * @group Students
     * 
     * Update student profile
     * 
     * Modifies the information of the authenticated student.
     * Note: Because it includes a file upload, you must send a POST request with a `_method=PUT` field in the form-data.
     * 
     * @bodyParam cv file optional The student's CV in PDF format.
     * 
     * @authenticated
     */
    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        // Validamos que el endpoint sea el correcto (Estudiante)
        if ($user->role !== 'student') {
            abort(404, 'Student profile not found');
        }

        //Uso de la policy (verifica si puede hacer update, si da false devuelve un 403)
        Gate::authorize('update', $user);

        // Obtenemos TODOS los datos validados del request
        $data = $request->validated();

        if ($request->hasFile('cv')) {
            // Borramos el anterior si existía
            if ($user->cv_path) {
                Storage::delete($user->cv_path);
            }
        
            // Guardamos el archivo en la carpeta 'cvs' (dentro de storage/app)
            $path = $request->file('cv')->store('cvs');
            $data['cv_path'] = $path; // Esto es lo que se guarda en la BD
        }
        // Actualizamos y devolvemos el Resource
        $user->update($data);

        return new UserResource($user);
    }

    /**
     * @group Students
     * 
     * Delete student account
     * 
     * Permanently removes the authenticated student's account from the database.
     * 
     * @authenticated
     */
    public function destroy(User $user): JsonResponse
    {
        if ($user->role !== 'student') {
            return response()->json(['message' => 'Not a student profile'], 404);
        }
    
        // USO POLICY
        Gate::authorize('delete', $user);

        if ($user->cv_path) {
            Storage::delete($user->cv_path);
        }

        $user->delete();

        return response()->json(['message' => 'Profile deleted successfully'], 200);
    }
}
