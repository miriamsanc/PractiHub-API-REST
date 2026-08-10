<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Ruta de prueba para ver si el usuario está autenticado
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
