<?php

use App\Models\Offer;
use App\Models\Category;
use App\Models\User;
use Laravel\Passport\Passport;

// VER OFERTAS (publico para usuarios autenticados)
it('allows authenticated users to list all offers', function () {
    Offer::factory(5)->create();
    
    Passport::actingAs(User::factory()->create());

    $response = $this->getJson('/api/offers');

    $response->assertStatus(200)->assertJsonCount(5);
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
             ->assertJsonPath('offer.title', 'Desarrollador Junior Laravel');

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

// FILTROS DE OFERTAS
it('filters offers by location', function () {
    // Creamos ofertas en diferentes ciudades
    Offer::factory()->create(['location' => 'Madrid']);
    Offer::factory()->create(['location' => 'Barcelona']);
    Offer::factory()->create(['location' => 'Madrid']);
    
    Passport::actingAs(User::factory()->create());

    // Hacemos la petición filtrando por Madrid
    $response = $this->getJson('/api/offers?location=Madrid');

    $response->assertStatus(200)
             ->assertJsonCount(2); // Debería devolver solo las 2 de Madrid
});

it('filters offers by category', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    // Creamos ofertas con diferentes categorías
    Offer::factory()->create(['category_id' => $category1->id]);
    Offer::factory()->create(['category_id' => $category1->id]);
    Offer::factory()->create(['category_id' => $category2->id]);
    
    Passport::actingAs(User::factory()->create());

    // Filtramos por la categoría 1
    $response = $this->getJson("/api/offers?category_id={$category1->id}");

    $response->assertStatus(200)
             ->assertJsonCount(2); // Debería devolver solo las 2 de la categoría 1
});
