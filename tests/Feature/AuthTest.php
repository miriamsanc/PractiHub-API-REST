<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

//Generar las claves de passport
beforeEach(function () {
    Artisan::call('passport:keys');
    Artisan::call('passport:client --personal --no-interaction');
});

// Tests de registro
it('registers a user successfully as student', function () {
    $data = [
        'name' => 'Juan',
        'email' => 'juan@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'student',
    ];

    $response = $this->postJson('/api/register', $data);

    $response->assertStatus(201)
             ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'role'],
                'token'
            ]);

    $this->assertDatabaseHas('users', [
        'email' => 'juan@example.com',
        'role' => 'student',
    ]);
});

it('registers a user successfully as company', function () {
    $data = [
        'name' => 'Empresa Tech',
        'email' => 'contacto@empresa.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'company',
    ];

    $response = $this->postJson('/api/register', $data);

    $response->assertStatus(201);
    
    $this->assertDatabaseHas('users', [
        'email' => 'contacto@empresa.com',
        'role' => 'company',
    ]);
});

it('fails registration if validation fails', function () {
    $data = [
        'name' => 'Test',
        // Aqui faltan datos ( email, password y rol)
    ];

    $response = $this->postJson('/api/register', $data);

    $response->assertStatus(422)->assertJsonValidationErrors(['email', 'password', 'role']);
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

    $response->assertStatus(200)->assertJsonStructure(['user', 'token']);
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

    $response->assertStatus(401)->assertJson(['message' => 'Invalid credentials']);
});

// Tests de cierre de sesion
it('logs out an authenticated user successfully', function () {
    // Crea un usuario y le genera un token
    $user = User::factory()->create();
    $token = $user->createToken('auth_token')->accessToken;

    // Hace la petición enviando el token en la cabecera Authorization
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/logout');

    $response->assertStatus(200)
             ->assertJson(['message' => 'Logged out successfully']);
});

it('fails logout if user is not authenticated', function () {
    // Intenta hacer logout sin enviar ningún token
    $response = $this->postJson('/api/logout');

    $response->assertStatus(401) // 401 Unauthorized
             ->assertJson(['message' => 'Unauthenticated.']);
});



