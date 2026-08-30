<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreApplicationRequest;
use App\Http\Requests\UpdateApplicationRequest;
use App\Http\Resources\ApplicationResource;
use Illuminate\Support\Facades\Gate;

class ApplicationController extends Controller
{
    public function store(StoreApplicationRequest $request, Offer $offer)
    {
        // Autorización: Llama a Create de ApplicationPolicy (lanza 403 si no es estudiante)
        Gate::authorize('create', Application::class);

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

        // Crear la inscripción (Usamos los datos validados del FormRequest (el cv_path))
        $application = Application::create([
            'user_id' => $request->user()->id,
            'offer_id' => $offer->id,
            'status' => 'pending', 
            'cv_path' => $request->validated('cv_path'),
        ]);

        return new ApplicationResource($application);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'student') {
            // ESTUDIANTE: Busca sus propias inscripciones
            // Cargamos la relación 'offer' para que sepa a qué se apuntó
            $applications = Application::with('offer')
                ->where('user_id', $user->id)
                ->get();
        } else {
            // EMPRESA: Busca inscripciones hechas a sus ofertas
            // Cargamos 'user' (el estudiante) y 'offer' (para saber a cuál de sus ofertas es)
            $applications = Application::with(['user', 'offer'])
                ->whereHas('offer', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->get();
        }

        return ApplicationResource::collection($applications);
    }
    
    public function update(UpdateApplicationRequest $request, Application $application): JsonResponse
    {
        // Autorización: Llama a update de ApplicationPolicy
        // Si no es la empresa propietaria de la oferta lanzará un 403 Forbidden
        Gate::authorize('update', $application);

        // Actualiza el estado con el dato validado
        $application->update([
            'status' => $request->validated('status'),
        ]);

        return response()->json([
            'message' => 'Application status updated successfully',
            'application' => new ApplicationResource($application)
        ], 200);
    }
}