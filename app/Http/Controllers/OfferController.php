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
    /**
     * @group Offers
     * 
     * List offers
     * 
     * Returns all internship offers. Supports filtering by category_id or location.
     * 
     * @authenticated
     */
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

    /**
     * @group Offers
     * 
     * Get offer details
     * 
     * Retrieves the specific details of an internship offer.
     * 
     * @authenticated
     */
    public function show(Offer $offer): OfferResource
    {
        // Autorización (La Policy permite que cualquier usuario autenticado la vea)
        Gate::authorize('view', $offer);

        // Cargamos las relaciones para que el Frontend tenga el nombre de la categoría y la empresa
        $offer->load(['category', 'company']);

        // Devuelve el dato limpio usando el Resource 
        return new OfferResource($offer);
    }

    /**
     * @group Offers
     * 
     * Create offer
     * 
     * Publishes a new internship offer. Only accessible by users with the 'company' role.
     * 
     * @authenticated
     */
    public function store(StoreOfferRequest $request): OfferResource
    {
        // AUTORIZACIÓN: Solo usuarios con rol 'company' pueden crear, validamos permiso con policy, si devuelve false (403)
        Gate::authorize('create', Offer::class);

        // CREACIÓN: Le asignamos la oferta al usuario autenticado
        $offer = $request->user()->offers()->create($request->validated());

        return new OfferResource($offer);
        
    }

    /**
     * @group Offers
     * 
     * Update offer
     * 
     * Modifies an existing offer. The company can also toggle 'is_active' to close the offer.
     * 
     * @authenticated
     */
    public function update(UpdateOfferRequest $request, Offer $offer): OfferResource
    {
        // AUTORIZACIÓN: Comprobar que el usuario autenticado es el dueño de la oferta(false en policy da 403)
        Gate::authorize('update', $offer);
       
        // ACTUALIZACIÓN
        $offer->update($request->validated());

        return new OfferResource($offer);
    }

    /**
     * @group Offers
     * 
     * Delete offer
     * 
     * Permanently removes an offer. Only the company that owns the offer can delete it.
     * 
     * @authenticated
     */
    public function destroy(Request $request, Offer $offer): JsonResponse
    {
        // AUTORIZACIÓN: Comprueba que es el propietario
        Gate::authorize('delete', $offer);

        $offer->delete();

        return response()->json(['message' => 'Offer deleted successfully'], 200);
    }
}
