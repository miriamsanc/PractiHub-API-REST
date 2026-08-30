<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Http\Requests\StoreOfferRequest;
use App\Http\Requests\UpdateOfferRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\OfferResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OfferController extends Controller
{
    // Lista todas las ofertas (con filtros opcionales)
    public function index(Request $request): AnonymousResourceCollection
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
            
        
        return OfferResource::collection($offers);
    }

    public function show(Offer $offer): OfferResource
    {
        // Autorización (La Policy permite que cualquier usuario autenticado la vea)
        Gate::authorize('view', $offer);

        // Cargamos las relaciones para que el Frontend tenga el nombre de la categoría y la empresa
        $offer->load(['category', 'company']);

        // Devuelve el dato limpio usando el Resource 
        return new OfferResource($offer);
    }

    // Crea una oferta (Solo empresas)
    public function store(StoreOfferRequest $request): OfferResource
    {
        // AUTORIZACIÓN: Solo usuarios con rol 'company' pueden crear, validamos permiso con policy, si devuelve false (403)
        Gate::authorize('create', Offer::class);

        // CREACIÓN: Le asignamos la oferta al usuario autenticado
        $offer = $request->user()->offers()->create($request->validated());

        return new OfferResource($offer);
        
    }

    // Edita una oferta (Solo empresa dueña)
    public function update(UpdateOfferRequest $request, Offer $offer): OfferResource
    {
        // AUTORIZACIÓN: Comprobar que el usuario autenticado es el dueño de la oferta(false en policy da 403)
        Gate::authorize('update', $offer);
       
        // ACTUALIZACIÓN
        $offer->update($request->validated());

        return new OfferResource($offer);
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
