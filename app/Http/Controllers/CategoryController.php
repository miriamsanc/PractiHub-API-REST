<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        // Muestra todas las categorias
        return response()->json(Category::all(), 200);
    }
}
