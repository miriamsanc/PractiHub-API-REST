<?php

use App\Models\Category;
use App\Models\User;
use Laravel\Passport\Passport;

it('allows authenticated users to list categories', function () {
    // De momento creo 3 categorías de prueba
    Category::factory(3)->create();

    // login con cualquier usuario
    Passport::actingAs(User::factory()->create());

    // Aqui hace la petición al endpoint
    $response = $this->getJson('/api/categories');

    // Verifica que devuelve 200 OK y que hay 3 elementos en la respuesta
    $response->assertStatus(200)
             ->assertJsonCount(3);
});
