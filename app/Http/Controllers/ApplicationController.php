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

        // Evitar inscripciones duplicadas 
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

    public function show(Application $application): ApplicationResource
    {
        Gate::authorize('view', $application);

        return new ApplicationResource($application->load(['user', 'offer']));
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Application::class);
    
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

        if ($application->status !== 'read') {
            return response()->json([
                'message' => 'Only applications in read status can be accepted or rejected.'
            ], 400);
        }
        
        // Actualiza el estado con el dato validado
        $application->update([
            'status' => $request->validated('status'),
        ]);

        return response()->json([
            'message' => 'Application status updated successfully',
            'application' => new ApplicationResource($application)
        ], 200);
    }

    public function destroy(Request $request, Application $application): JsonResponse
    {
        // Autorización: Debe ser el estudiante dueño de la candidatura
        Gate::authorize('delete', $application);

        // No se puede retirar si la empresa ya la ha gestionado
        if ($application->status !== 'pending') {
            return response()->json([
                'message' => 'You cannot withdraw an application that has already been processed.'
            ], 400);
        }

        // Límite de 30 minutos para retirar la candidatura
        if ($application->created_at->addMinutes(30)->isPast()) {
            return response()->json([
                'message' => 'Time limit exceeded. You can only withdraw your application within the first 30 minutes.'
            ], 400);
        }

        $application->delete();

        return response()->json([
            'message' => 'Application withdrawn successfully'
        ], 200);
    }

    //Permite que la empresa vea los estudiantes apuntados a su oferta

    public function byOffer(Request $request, Offer $offer)
    {
        // Verificar que la empresa es la propietaria de la oferta
        if ($request->user()->role !== 'company' || $request->user()->id !== $offer->user_id) {
        return response()->json(['message' => 'Unauthorized'], 403);
        }

        $applications = $offer->applications()->with('user')->get();

        return ApplicationResource::collection($applications);
    }

}