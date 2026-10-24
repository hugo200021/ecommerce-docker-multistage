<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'E-commerce Secure API',
    version: '1.0.0',
    description: 'API REST para catálogo, órdenes y pagos con autenticación JWT y Stripe.'
)]
#[OA\Server(url: '/', description: 'Servidor actual')]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Token JWT obtenido en /api/auth/login.'
)]
#[OA\Tag(name: 'Autenticación', description: 'Registro, inicio y cierre de sesión.')]
#[OA\Tag(name: 'Productos', description: 'Catálogo público y administración de productos.')]
#[OA\Tag(name: 'Órdenes', description: 'Creación e historial de compras.')]
#[OA\Tag(name: 'Pagos', description: 'Procesamiento seguro de pagos mediante Stripe.')]
final class OpenApiSpec
{
}
