<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreApplicationRequest;
use App\Http\Resources\ApplicationResource;
use Illuminate\Support\Facades\Gate;

class ApplicationController extends Controller
{
    public function store(StoreApplicationRequest $request, Offer $offer)
    {
        // Autorización: Llama a ApplicationPolicy@create (lanza 403 si no es estudiante)
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
}
