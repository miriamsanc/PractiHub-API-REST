<?php

use App\Models\Offer;
use App\Models\User;
use App\Models\Application;
use Laravel\Passport\Passport;


it('allows a student to apply for an offer', function () {
    $student = User::factory()->create(['role' => 'student']);
    $offer = Offer::factory()->create();

    Passport::actingAs($student);

    $response = $this->postJson("/api/offers/{$offer->id}/applications");

    $response->assertStatus(201);

    $this->assertDatabaseHas('applications', [
        'user_id' => $student->id,
        'offer_id' => $offer->id,
        'status' => 'pending',
    ]);
});

it('forbids a student from applying twice to the same offer', function () {
    $student = User::factory()->create(['role' => 'student']);
    $offer = Offer::factory()->create();

    // Ya está inscrito
    Application::factory()->create([
        'user_id' => $student->id,
        'offer_id' => $offer->id,
    ]);

    Passport::actingAs($student);

    $response = $this->postJson("/api/offers/{$offer->id}/applications");

    // Debería fallar con un error de conflicto o validación
    $response->assertStatus(422); 
});


