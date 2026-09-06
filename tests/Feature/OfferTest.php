<?php

use App\Models\Offer;
use App\Models\Category;
use App\Models\User;
use Laravel\Passport\Passport;

// VER OFERTAS (publico para usuarios autenticados)
it('allows authenticated users to list all offers', function () {
    Offer::factory(5)->create(['is_active' => true]);
    
    Passport::actingAs(User::factory()->create());

    $response = $this->getJson('/api/offers');

    $response->assertStatus(200)->assertJsonCount(5, 'data');
});

// CREAR OFERTAS (solo las empresas)
it('allows a company to create an offer', function () {
    $company = User::factory()->create(['role' => 'company']);
    $category = Category::factory()->create();

    Passport::actingAs($company);

    $data = [
        'category_id' => $category->id,
        'title' => 'Desarrollador Junior Laravel',
        'description' => 'Buscamos talento para nuestra API.',
        'location' => 'Remoto',
    ];

    $response = $this->postJson('/api/offers', $data);

    $response->assertStatus(201) // 201 created
             ->assertJsonPath('data.title', 'Desarrollador Junior Laravel');

    $this->assertDatabaseHas('offers', [
        'title' => 'Desarrollador Junior Laravel',
        'user_id' => $company->id // Verifica que se ha asignado al creador
    ]);
});

it('forbids a student from creating an offer', function () {
    $student = User::factory()->create(['role' => 'student']);
    Passport::actingAs($student);

    $response = $this->postJson('/api/offers', [
        'title' => 'Intento de Hackeo',
        'description' => 'No debería permitirse',
        'location' => 'Madrid',
    ]);

    $response->assertStatus(403); // 403 Forbidden
});

// EDITAR OFERTAS (Solo el dueño)
it('allows the owner company to update their offer', function () {
    $company = User::factory()->create(['role' => 'company']);
    $offer = Offer::factory()->create(['user_id' => $company->id]); // Oferta de esta empresa

    Passport::actingAs($company);

    $response = $this->putJson("/api/offers/{$offer->id}", [
        'title' => 'Título Actualizado'
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('offers', ['title' => 'Título Actualizado']);
});

it('forbids another company from updating an offer they do not own', function () {
    $ownerCompany = User::factory()->create(['role' => 'company']);
    $offer = Offer::factory()->create(['user_id' => $ownerCompany->id]);

    $otherCompany = User::factory()->create(['role' => 'company']); // Otra empresa
    Passport::actingAs($otherCompany);

    $response = $this->putJson("/api/offers/{$offer->id}", [
        'title' => 'Hackeo'
    ]);

    $response->assertStatus(403); // 403 Forbidden
});

// ELIMINAR OFERTAS
it('allows the owner company to delete their offer', function () {
    $company = User::factory()->create(['role' => 'company']);
    $offer = Offer::factory()->create(['user_id' => $company->id]);

    Passport::actingAs($company);

    $response = $this->deleteJson("/api/offers/{$offer->id}");

    $response->assertStatus(200);
    $this->assertDatabaseMissing('offers', ['id' => $offer->id]);
});

it('forbids another company from deleting an offer they do not own', function () {
    $ownerCompany = User::factory()->create(['role' => 'company']);

    $offer = Offer::factory()->create([
        'user_id' => $ownerCompany->id,
    ]);

    $otherCompany = User::factory()->create(['role' => 'company']);

    Passport::actingAs($otherCompany);

    $response = $this->deleteJson("/api/offers/{$offer->id}");

    $response->assertStatus(403);

    $this->assertDatabaseHas('offers', [
        'id' => $offer->id,
    ]);
});

it('forbids a student from updating an offer', function () {
    $company = User::factory()->create(['role' => 'company']);

    $offer = Offer::factory()->create([
        'user_id' => $company->id,
    ]);

    $student = User::factory()->create(['role' => 'student']);

    Passport::actingAs($student);

    $this->putJson("/api/offers/{$offer->id}", [
        'title' => 'Hack',
    ])->assertStatus(403);
});

// FILTROS DE OFERTAS
it('filters offers by location', function () {
    // Creamos 2 ofertas en Madrid FORZANDO que estén activas
    Offer::factory()->count(2)->create([
        'location' => 'Madrid',
        'is_active' => true,
    ]);

    // Creamos otras ofertas en otra ciudad para asegurar que no se cuelan
    Offer::factory()->count(3)->create([
        'location' => 'Barcelona',
        'is_active' => true,
    ]);
    
    Passport::actingAs(User::factory()->create());

    // Hacemos la petición filtrando por Madrid
    $response = $this->getJson('/api/offers?location=Madrid');

    $response->assertStatus(200)
             ->assertJsonCount(2, 'data'); // Debería devolver solo las 2 de Madrid
});

it('filters offers by category', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    // Creamos 2 ofertas para la categoría 1 FORZANDO que estén activas
    Offer::factory()->count(2)->create([
        'category_id' => $category1->id,
        'is_active' => true,
    ]);

    // Creamos otras ofertas para la categoría 2
    Offer::factory()->count(3)->create([
        'category_id' => $category2->id,
        'is_active' => true,
    ]);
    
    Passport::actingAs(User::factory()->create());

    // Filtramos por la categoría 1
    $response = $this->getJson("/api/offers?category_id={$category1->id}");

    $response->assertStatus(200)
             ->assertJsonCount(2, 'data'); // Debería devolver solo las 2 de la categoría 1
});

it('forbids unauthenticated users from listing offers', function () {
    $response = $this->getJson('/api/offers');

    $response->assertStatus(401);
});
