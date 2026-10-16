<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'token' => $this->resource['token'],
            'type' => 'Bearer',
            'id' => $this->resource['user']->id,
            'name' => $this->resource['user']->name,
            'email' => $this->resource['user']->email,
            'role' => $this->resource['user']->role,
            'expiresIn' => $this->resource['expiresIn'],
        ];
    }
}
