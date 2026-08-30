<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\ApplicationController;

// Rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas (Requieren enviar el Token)
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rutas de Estudiantes (Users)
    Route::apiResource('users', UserController::class)->only(['show', 'update', 'destroy']);

    // Rutas de Empresas (Companies)
    Route::apiResource('companies', CompanyController::class)->only(['show', 'update', 'destroy']);

    // Categorias
    Route::apiResource('categories', CategoryController::class)->only(['index']);

    // Ofertas
    Route::apiResource('offers', OfferController::class);

    // Applications
    Route::post('/offers/{offer}/applications', [ApplicationController::class, 'store']);
    Route::get('/applications', [ApplicationController::class, 'index']);
    Route::delete('/applications/{application}', [ApplicationController::class, 'destroy']);




});
