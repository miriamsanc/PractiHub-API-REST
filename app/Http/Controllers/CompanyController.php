<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CompanyController extends Controller
{
    // Ver el perfil de una empresa
    public function show(User $company)
    {
        // Asegurarnos de que el usuario solicitado es una empresa
        if ($company->role !== 'company') {
            return response()->json(['message' => 'Company not found'], 404);
        }
        
        return response()->json($company, 200);
    }

    // Actualizar el perfil
    public function update(Request $request, User $company)
    {
        // Verificamos que el perfil a editar sea una empresa
        if ($company->role !== 'company') {
            return response()->json(['message' => 'Company not found'], 404);
        }
    
        // AUTORIZACIÓN VÍA POLICY: Solo el dueño de la cuenta puede editarla
        // Si no es el dueño, detiene la ejecución y devuelve un 403.
        Gate::authorize('update', $company);

        // VALIDACIÓN
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $company->id,
        ]);

        // ACTUALIZACIÓN
        $company->update($validatedData);

        return response()->json([
            'message' => 'Company profile updated successfully',
            'user' => $company
        ], 200);
    }

    // Eliminar la empresa
    public function destroy(Request $request, User $company)
    {
        if ($company->role !== 'company') {
            return response()->json(['message' => 'Company not found'], 404);
        }
    
        // AUTORIZACIÓN CON POLICY
        Gate::authorize('delete', $company);

        // ELIMINACIÓN
        $company->delete();

        return response()->json(['message' => 'Company deleted successfully'], 200);
    }
}
