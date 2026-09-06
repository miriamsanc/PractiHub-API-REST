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

    // Rutas de Estudiantes 
    Route::apiResource('users', UserController::class)->only(['show', 'update', 'destroy']);

    // Rutas del ranking(Todas las empresas ordenadas por % de aceptación)
    Route::get('/companies/ranking', [CompanyController::class, 'ranking']);

    // Rutas de Empresas 
    Route::apiResource('companies', CompanyController::class)->only(['show', 'update', 'destroy']);

    // Categorias
    Route::apiResource('categories', CategoryController::class)->only(['index']);

    // Ofertas
    Route::apiResource('offers', OfferController::class);

    // Applications
    Route::post('/offers/{offer}/applications', [ApplicationController::class, 'store']);
    Route::get('/applications', [ApplicationController::class, 'index']);
    Route::get('/applications/{application}', [ApplicationController::class, 'show']);
    Route::get('/applications/{application}/cv', [ApplicationController::class, 'cv']);
    Route::delete('/applications/{application}', [ApplicationController::class, 'destroy']);
    Route::put('/applications/{application}', [ApplicationController::class, 'update']);
    Route::get('/offers/{offer}/applications', [ApplicationController::class, 'byOffer']);

  

});
