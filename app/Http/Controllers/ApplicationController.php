<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApplicationController extends Controller
{
    public function store(Request $request, Offer $offer): JsonResponse
    {
        // Autorización: Solo los estudiantes pueden inscribirse
        if ($request->user()->role !== 'student') {
            return response()->json(['message' => 'Forbidden: Only students can apply to offers'], 403);
        }

        // No se puede aplicar a una oferta cerrada
        if (!$offer->is_active) {
            return response()->json(['message' => 'This offer is no longer open'], 400);
        }

        // Evitar inscripciones duplicadas (error 500 de SQL)
        $alreadyApplied = Application::where('user_id', $request->user()->id)
            ->where('offer_id', $offer->id)
            ->exists();

        if ($alreadyApplied) {
            return response()->json(['message' => 'You have already applied to this offer'], 409); // 409 Conflict
        }

        // Validación: Pide una url para el cv
        $request->validate([
            'cv_path' => 'required|url|max:255',
        ]);

        // Coge el texto que ha enviado el estudiante
        $path = $request->input('cv_path');

        // Crear la inscripción
        $application = Application::create([
            'user_id' => $request->user()->id,
            'offer_id' => $offer->id,
            'status' => 'pending', 
            'cv_path' => $path,
        ]);

        
        return response()->json([
            'message' => 'Application submitted successfully',
            'application' => $application
        ], 201);
    }
}
