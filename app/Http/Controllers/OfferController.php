<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Http\Request;

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
            // Filtro por ubicación (usamos 'like' para que sea una búsqueda más flexible)
            ->when($request->query('location'), function ($query, $location) {
                $query->where('location', 'like', '%' . $location . '%');
            })
            // Opcional pero recomendado: traernos los datos de la categoría y la empresa para que el Front-End tenga más info
            ->with(['category', 'company']) 
            ->get();
            
        return response()->json($offers, 200);
    }

    // Crea una oferta (Solo empresas)
    public function store(Request $request)
    {
        // AUTORIZACIÓN: Solo usuarios con rol 'company' pueden crear
        if ($request->user()->role !== 'company') {
            return response()->json(['message' => 'Forbidden: Only companies can create offers'], 403);
        }

        // VALIDACIÓN
        $validatedData = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
        ]);

        // CREACIÓN: Le asignamos la oferta al usuario autenticado
        $validatedData['user_id'] = $request->user()->id;
        $offer = Offer::create($validatedData);

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
