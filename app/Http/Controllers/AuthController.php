<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\AuthResource;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
    ) {}

    public function authenticate(AuthRequest $request): AuthResource
    {
        $data = $request->validated();

        $result = $this->auth->login($data['email'], $data['password']);

        return new AuthResource($result);
    }

    public function register(RegisterRequest $request): AuthResource
    {
        $data = $request->validated();

        $result = $this->auth->register($data['name'], $data['email'], $data['password']);

        return new AuthResource($result);
    }
}
