<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('регистрирует пользователя и выдаёт токен', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJsonPath('type', 'Bearer')
        ->assertJsonPath('email', 'test@example.com')
        ->assertJsonStructure(['token', 'type', 'id', 'name', 'email', 'role', 'expiresIn']);

    expect(User::where('email', 'test@example.com')->exists())->toBeTrue();
});

it('возвращает 409, если email уже занят', function () {
    User::factory()->create(['email' => 'dup@example.com']);

    $response = $this->postJson('/api/register', [
        'name' => 'Dup User',
        'email' => 'dup@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(409)
        ->assertJsonPath('code', 409)
        ->assertJsonPath('message', 'Email already exists')
        ->assertJsonPath('error', 'Conflict');
});

it('логинит пользователя и выдаёт токен', function () {
    User::factory()->create([
        'email' => 'login@example.com',
        'password' => 'password123',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'login@example.com',
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJsonPath('email', 'login@example.com')
        ->assertJsonStructure(['token', 'expiresIn']);
});

it('возвращает 401 при неверном пароле', function () {
    User::factory()->create([
        'email' => 'bad@example.com',
        'password' => 'password123',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'bad@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(401)
        ->assertJsonPath('code', 401)
        ->assertJsonPath('message', 'Invalid credentials')
        ->assertJsonPath('error', 'Authentication error');
});

it('возвращает 400 при невалидных данных', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'not-an-email',
    ]);

    $response->assertStatus(400)
        ->assertJsonPath('code', 400)
        ->assertJsonStructure(['message', 'errors' => ['email']]);
});
