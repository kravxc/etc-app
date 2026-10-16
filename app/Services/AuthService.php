<?php

namespace App\Services;

use App\Exceptions\EmailAlreadyExistsException;
use App\Exceptions\InvalidCredentialsException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public const TOKEN_TTL_MS = 86_400_000;

    /**
     * @throws InvalidCredentialsException
     */
    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new InvalidCredentialsException;
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
            'expiresIn' => self::TOKEN_TTL_MS,
        ];
    }

    public function register(string $name, string $email, string $password): array
    {
        if (User::where('email', $email)->exists()) {
            throw new EmailAlreadyExistsException;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
            'expiresIn' => self::TOKEN_TTL_MS,
        ];
    }
}
