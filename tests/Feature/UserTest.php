<?php

use App\Models\User;
use Laravel\Passport\Passport;

// ENDPOINTS ESTUDIANTE
// VER PERFIL
it('allows an authenticated user to view a student profile', function () {
    // Aqui crea un estudiante para el test
    $student = User::factory()->create(['role' => 'student']);
    
    // Hace login con un usuario
    Passport::actingAs(User::factory()->create());

    $response = $this->getJson("/api/users/{$student->id}");

    $response->assertStatus(200)
             ->assertJsonPath('name', $student->name)
             ->assertJsonPath('role', 'student');
});

it('fails to view a profile if not authenticated', function () {
    $student = User::factory()->create(['role' => 'student']);

    // Peticion SIN hacer login
    $response = $this->getJson("/api/users/{$student->id}");

    $response->assertStatus(401);
});

// EDITAR PERFIL
it('allows a student to update their own profile', function () {
    $student = User::factory()->create(['role' => 'student']);
    
    // Hacemos login como ese mismo estudiante
    Passport::actingAs($student);

    $response = $this->putJson("/api/users/{$student->id}", [
        'name' => 'Nombre Actualizado',
        'email' => $student->email, 
    ]);

    $response->assertStatus(200)
             ->assertJsonPath('user.name', 'Nombre Actualizado');

    $this->assertDatabaseHas('users', [
        'id' => $student->id,
        'name' => 'Nombre Actualizado'
    ]);
});

it('forbids a user from updating someone else profile', function () {
    $student = User::factory()->create(['role' => 'student']);
    $hacker = User::factory()->create(); // Otro usuario diferente
    
    // Hace login como el hacker e intenta editar al estudiante
    Passport::actingAs($hacker);

    $response = $this->putJson("/api/users/{$student->id}", [
        'name' => 'Hacked Name'
    ]);

    $response->assertStatus(403); // 403 Forbidden 
});

// ELIMINAR CUENTA
it('allows a student to delete their own account', function () {
    $student = User::factory()->create(['role' => 'student']);
    
    Passport::actingAs($student);

    $response = $this->deleteJson("/api/users/{$student->id}");

    $response->assertStatus(200);
    $this->assertDatabaseMissing('users', ['id' => $student->id]);
});