<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Http\Requests\StoreOfferRequest;
use App\Http\Requests\UpdateOfferRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OfferController extends Controller
{
    // Lista todas las ofertas (con filtros opcionales)
    public function index(Request $request)
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
    public function store(StoreOfferRequest $request)
    {
        // AUTORIZACIÓN: Solo usuarios con rol 'company' pueden crear, validamos permiso con policy, si devuelve false (403)
        Gate::authorize('create', Offer::class);

        // CREACIÓN: Le asignamos la oferta al usuario autenticado
        $offer = $request->user()->offers()->create($request->validated());

        return response()->json(['offer' => $offer], 201);
        
    }

    // Edita una oferta (Solo empresa dueña)
    public function update(Request $request, Offer $offer)
    {
        // AUTORIZACIÓN: Comprobar que el usuario autenticado es el dueño de la oferta
        if ($request->user()->id !== $offer->user_id) {
            return response()->json(['message' => 'Forbidden: You do not own this offer'], 403);
        }

        // VALIDACIÓN
        $validatedData = $request->validate([
            'category_id' => 'sometimes|required|exists:categories,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'location' => 'sometimes|required|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        // ACTUALIZACIÓN
        $offer->update($validatedData);

        return response()->json(['offer' => $offer], 200);
    }

    // Elimina una oferta (Solo la empresa dueña)
    public function destroy(Request $request, Offer $offer)
    {
        // AUTORIZACIÓN: Comprueba que es el dueño
        if ($request->user()->id !== $offer->user_id) {
            return response()->json(['message' => 'Forbidden: You do not own this offer'], 403);
        }

        $offer->delete();

        return response()->json(['message' => 'Offer deleted successfully'], 200);
    }
}
