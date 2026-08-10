<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

//Generar las claves de passport
beforeEach(function () {
    Artisan::call('passport:keys');
});

// Tests de registro
it('registers a user successfully as student', function () {
    $data = [
        'name' => 'Juan',
        'email' => 'juan@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'estudiante',
    ];

    $response = $this->postJson('/api/register', $data);

    $response->assertStatus(201)
             ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'role'],
                'token'
            ]);

    $this->assertDatabaseHas('users', [
        'email' => 'juan@example.com',
        'role' => 'estudiante',
    ]);
});

it('registers a user successfully as company', function () {
    $data = [
        'name' => 'Empresa Tech',
        'email' => 'contacto@empresa.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'empresa',
    ];

    $response = $this->postJson('/api/register', $data);

    $response->assertStatus(201);
    
    $this->assertDatabaseHas('users', [
        'email' => 'contacto@empresa.com',
        'role' => 'empresa',
    ]);
});

it('fails registration if validation fails', function () {
    $data = [
        'name' => 'Test',
        // Aqui faltan datos ( email, password y rol)
    ];

    $response = $this->postJson('/api/register', $data);

    $response->assertStatus(422) 
             ->assertJsonValidationErrors(['email', 'password', 'role']);
});

// Tests de inicio de sesion
it('logs in an existing user successfully', function () {
    
    $user = User::factory()->create([
        'email' => 'login@example.com',
        'password' => bcrypt('password123'), // Aqui se encripta la contraseña
    ]);

    $data = [
        'email' => 'login@example.com',
        'password' => 'password123',
    ];

    $response = $this->postJson('/api/login', $data);

    $response->assertStatus(200)
             ->assertJsonStructure(['user', 'token']);
});

it('fails login with incorrect credentials', function () {
    $user = User::factory()->create([
        'email' => 'login@example.com',
        'password' => bcrypt('password123'),
    ]);

    $data = [
        'email' => 'login@example.com',
        'password' => 'wrong-password', 
    ];

    $response = $this->postJson('/api/login', $data);

    $response->assertStatus(401) 
             ->assertJson(['message' => 'Invalid credentials']);
});
