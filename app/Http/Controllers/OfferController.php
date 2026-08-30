<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Http\Requests\StoreOfferRequest;
use App\Http\Requests\UpdateOfferRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class OfferController extends Controller
{
    // Lista todas las ofertas (con filtros opcionales)
    public function index(Request $request): JsonResponse
    {
        $offers = Offer::query()
            // Filtro por category_id exacto
            ->when($request->query('category_id'), function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            // Filtro por ubicación ( like para que sea mas flexible)
            ->when($request->query('location'), function ($query, $location) {
                $query->where('location', 'like', '%' . $location . '%');
            })
            // Si no es empresa solo ve las ofertas activas
            ->when($request->user()->role !== 'company', function ($query) {
                $query->where('is_active', true);
            })
            // traermos los datos de categoria y empresa para que el front tenga mas info
            ->with(['category', 'company']) 
            ->get();
            
        return response()->json($offers, 200);
    }

    // Crea una oferta (Solo empresas)
    public function store(StoreOfferRequest $request): JsonResponse
    {
        // AUTORIZACIÓN: Solo usuarios con rol 'company' pueden crear, validamos permiso con policy, si devuelve false (403)
        Gate::authorize('create', Offer::class);

        // CREACIÓN: Le asignamos la oferta al usuario autenticado
        $offer = $request->user()->offers()->create($request->validated());

        return response()->json(['offer' => $offer], 201);
        
    }

    // Edita una oferta (Solo empresa dueña)
    public function update(UpdateOfferRequest $request, Offer $offer): JsonResponse
    {
        // AUTORIZACIÓN: Comprobar que el usuario autenticado es el dueño de la oferta(false en policy da 403)
        Gate::authorize('update', $offer);
       
        // ACTUALIZACIÓN
        $offer->update($request->validated());

        return response()->json(['offer' => $offer], 200);
    }

    // Elimina una oferta (Solo la empresa creadora)
    public function destroy(Request $request, Offer $offer): JsonResponse
    {
        // AUTORIZACIÓN: Comprueba que es el propietario
        Gate::authorize('delete', $offer);

        $offer->delete();

        return response()->json(['message' => 'Offer deleted successfully'], 200);
    }
}
