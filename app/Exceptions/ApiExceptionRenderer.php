<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ApiExceptionRenderer
{
    /** @var array<class-string, array{int, string, string}> */
    private const MAP = [
        ValidationException::class => [400, 'Validation failed', 'Validation error'],
        InvalidCredentialsException::class => [401, 'Invalid credentials', 'Authentication error'],
        AuthenticationException::class => [401, 'Unauthenticated', 'Authentication error'],
        EmailAlreadyExistsException::class => [409, 'Email already exists', 'Conflict'],
        ModelNotFoundException::class => [404, 'Resource not found', 'Not found'],
    ];

    public function render(Throwable $e, Request $request): ?JsonResponse
    {
        if (! $request->is('api/*')) {
            return null;
        }

        [$status, $message, $error] = $this->resolve($e);

        $payload = [
            'status' => 'error',
            'code' => $status,
            'message' => $message,
            'path' => '/'.ltrim($request->path(), '/'),
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'error' => $error,
        ];

        if ($e instanceof ValidationException) {
            $payload['errors'] = $this->flattenErrors($e);
        }

        return response()->json($payload, $status);
    }

    /** @return array{int, string, string} */
    private function resolve(Throwable $e): array
    {
        foreach (self::MAP as $class => [$status, $message, $error]) {
            if ($e instanceof $class) {
                return [$status, $e->getMessage() ?: $message, $error];
            }
        }

        if ($e instanceof HttpExceptionInterface) {
            return [$e->getStatusCode(), $e->getMessage() ?: 'HTTP error', class_basename($e)];
        }

        return [500, 'Internal Server Error', 'Server error'];
    }

    private function flattenErrors(ValidationException $e): array
    {
        return collect($e->errors())
            ->map(fn (array $messages) => $messages[0] ?? null)
            ->toArray();
    }
}
