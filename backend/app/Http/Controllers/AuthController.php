<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/auth/register',
        operationId: 'register',
        summary: 'Registrar cliente',
        tags: ['Autenticación'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'María López'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'maria@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'Compra2026')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Cliente registrado y token JWT generado.'),
            new OA\Response(response: 422, description: 'Datos de registro inválidos.'),
            new OA\Response(response: 429, description: 'Demasiados intentos.')
        ]
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ]);

        $token = auth('api')->login($user);

        return ApiResponse::success(
            $this->tokenPayload($user, $token),
            'Usuario registrado correctamente.',
            201
        );
    }

    #[OA\Post(
        path: '/api/auth/login',
        operationId: 'login',
        summary: 'Iniciar sesión',
        tags: ['Autenticación'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'maria@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'Compra2026')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Autenticación correcta.'),
            new OA\Response(response: 401, description: 'Credenciales inválidas.'),
            new OA\Response(response: 422, description: 'Datos inválidos.'),
            new OA\Response(response: 429, description: 'Demasiados intentos.')
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (! $token = auth('api')->attempt($credentials)) {
            return ApiResponse::error('Las credenciales proporcionadas no son válidas.', 401);
        }

        return ApiResponse::success(
            $this->tokenPayload(auth('api')->user(), $token),
            'Inicio de sesión exitoso.'
        );
    }

    #[OA\Get(
        path: '/api/me',
        operationId: 'me',
        summary: 'Consultar usuario autenticado',
        tags: ['Autenticación'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Datos del usuario autenticado.'),
            new OA\Response(response: 401, description: 'Token ausente, inválido o expirado.')
        ]
    )]
    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success($request->user('api'), 'Usuario autenticado.');
    }

    #[OA\Post(
        path: '/api/auth/logout',
        operationId: 'logout',
        summary: 'Cerrar sesión',
        tags: ['Autenticación'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Token invalidado correctamente.'),
            new OA\Response(response: 401, description: 'Token ausente, inválido o expirado.')
        ]
    )]
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return ApiResponse::success(null, 'Sesión cerrada correctamente.');
    }

    private function tokenPayload(User $user, string $token): array
    {
        return [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ];
    }
}
