<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    // Ver el perfil de una empresa
    public function show(User $company)
    {
        // Asegurarnos de que el usuario solicitado es realmente una empresa
        if ($company->role !== 'company') {
            return response()->json(['message' => 'Company not found'], 404);
        }
        
        return response()->json($company, 200);
    }

    // Actualizar el perfil
    public function update(Request $request, User $company)
    {
        // 1. AUTORIZACIÓN: Solo el dueño de la cuenta puede editarla
        if ($request->user()->id !== $company->id) {
            return response()->json(['message' => 'Forbidden: You can only edit your own profile'], 403);
        }

        // 2. VALIDACIÓN
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $company->id,
        ]);

        // 3. ACTUALIZACIÓN
        $company->update($validatedData);

        return response()->json([
            'message' => 'Company profile updated successfully',
            'user' => $company
        ], 200);
    }

    // Eliminar la empresa
    public function destroy(Request $request, User $company)
    {
        // 1. AUTORIZACIÓN
        if ($request->user()->id !== $company->id) {
            return response()->json(['message' => 'Forbidden: You can only delete your own profile'], 403);
        }

        // 2. ELIMINACIÓN
        $company->delete();

        return response()->json(['message' => 'Company deleted successfully'], 200);
    }
}
