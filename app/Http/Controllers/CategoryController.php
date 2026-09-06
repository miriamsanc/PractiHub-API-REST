<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * @group Categories
     * 
     * List categories
     * 
     * Returns all available categories to be used for filtering offers.
     * 
     * @authenticated
     */
    public function index(): JsonResponse
    {
        // Muestra todas las categorias
        return response()->json(Category::all(), 200);
    }
}
