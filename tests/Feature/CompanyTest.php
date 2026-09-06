<?php

use App\Models\User;
use Laravel\Passport\Passport;

// VER PERFIL
it('allows an authenticated user to view a company profile', function () {
    $company = User::factory()->create(['role' => 'company']);
    
    // Hace login con otro usuario 
    Passport::actingAs(User::factory()->create());

    $response = $this->getJson("/api/companies/{$company->id}");

    $response->assertStatus(200)
             ->assertJsonPath('data.name', $company->name)
             ->assertJsonPath('data.role', 'company');
});

it('returns 404 when trying to view a student as a company', function () {
    $student = User::factory()->create(['role' => 'student']);

    Passport::actingAs(User::factory()->create());

    $response = $this->getJson("/api/companies/{$student->id}");

    $response->assertStatus(404);
});

// EDITAR PERFIL
it('allows a company to update their own profile', function () {
    $company = User::factory()->create(['role' => 'company']);
    
    // Hace login como esa misma empresa
    Passport::actingAs($company);

    $response = $this->putJson("/api/companies/{$company->id}", [
        'name' => 'Empresa Modificada SL',
        'email' => $company->email,
    ]);

    $response->assertStatus(200)
             ->assertJsonPath('data.name', 'Empresa Modificada SL');

    $this->assertDatabaseHas('users', [
        'id' => $company->id,
        'name' => 'Empresa Modificada SL'
    ]);
});

it('forbids a user from updating another company profile', function () {
    $company = User::factory()->create(['role' => 'company']);
    $otherUser = User::factory()->create(); // Otro usuario (hacker)
    
    Passport::actingAs($otherUser);

    $response = $this->putJson("/api/companies/{$company->id}", [
        'name' => 'Hacked Company'
    ]);

    $response->assertStatus(403);
});

it('forbids a company from updating another company profile', function () {
    $company = User::factory()->create(['role' => 'company']);
    $otherCompany = User::factory()->create(['role' => 'company']);

    Passport::actingAs($otherCompany);

    $response = $this->putJson("/api/companies/{$company->id}", [
        'name' => 'Hacked Company',
    ]);

    $response->assertStatus(403);
});

// ELIMINAR CUENTA
it('allows a company to delete their own account', function () {
    $company = User::factory()->create(['role' => 'company']);
    
    Passport::actingAs($company);

    $response = $this->deleteJson("/api/companies/{$company->id}");

    $response->assertStatus(200);
    $this->assertDatabaseMissing('users', ['id' => $company->id]);
});
