<?php

use App\Models\Offer;
use App\Models\User;
use App\Models\Application;
use Laravel\Passport\Passport;


it('allows a student to apply for an offer', function () {
    $student = User::factory()->create(['role' => 'student']);
    $offer = Offer::factory()->create(['is_active' => true]);

    Passport::actingAs($student);

    $response = $this->postJson("/api/offers/{$offer->id}/applications", [
        'cv_path' => 'https://example.com/cv.pdf',
    ]);
    //metodo aux que equivale al 201
    $response->assertCreated()
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.cv_link', 'https://example.com/cv.pdf');

    $this->assertDatabaseHas('applications', [
        'user_id' => $student->id,
        'offer_id' => $offer->id,
        'status' => 'pending',
        'cv_path' => 'https://example.com/cv.pdf',
    ]);
});

it('fails to apply when cv_path is missing or not a valid URL', function () {
    $student = User::factory()->create(['role' => 'student']);
    $offer = Offer::factory()->create(['is_active' => true]);

    Passport::actingAs($student);

    $response = $this->postJson("/api/offers/{$offer->id}/applications", [
        'cv_path' => 'invalid-url',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['cv_path']);
});


it('forbids a student from applying twice to the same offer', function () {
    $student = User::factory()->create(['role' => 'student']);
    $offer = Offer::factory()->create(['is_active' => true]);

    // Ya está inscrito
    Application::factory()->create([
        'user_id' => $student->id,
        'offer_id' => $offer->id,
    ]);

    Passport::actingAs($student);

    $response = $this->postJson("/api/offers/{$offer->id}/applications", [
        'cv_path' => 'https://example.com/cv.pdf',
    ]);

    $response->assertStatus(409)
        ->assertJson(['message' => 'You have already applied to this offer']);
    
});


