<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductListRequest;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    #[OA\Get(
        path: '/api/products',
        operationId: 'listProducts',
        summary: 'Listar productos',
        tags: ['Productos'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'laptop'),
            new OA\Parameter(name: 'min_price', in: 'query', required: false, schema: new OA\Schema(type: 'number', format: 'float'), example: 10),
            new OA\Parameter(name: 'max_price', in: 'query', required: false, schema: new OA\Schema(type: 'number', format: 'float'), example: 1000),
            new OA\Parameter(name: 'in_stock', in: 'query', required: false, schema: new OA\Schema(type: 'boolean'), example: true),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['name', 'price', 'created_at'])),
            new OA\Parameter(name: 'sort_direction', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 50), example: 15)
        ],
        responses: [
            new OA\Response(response: 200, description: 'Catálogo paginado de productos.'),
            new OA\Response(response: 422, description: 'Filtros inválidos.')
        ]
    )]
    public function index(ProductListRequest $request): JsonResponse
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $search = $request->validated('search');
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->validated('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->validated('max_price'));
        }

        if ($request->has('in_stock')) {
            $request->boolean('in_stock')
                ? $query->where('stock', '>', 0)
                : $query->where('stock', 0);
        }

        $sortBy = $request->validated('sort_by') ?? 'created_at';
        $direction = $request->validated('sort_direction') ?? 'desc';
        $perPage = (int) ($request->validated('per_page') ?? 15);

        $products = $query->orderBy($sortBy, $direction)->paginate($perPage);

        return ApiResponse::success(
            $products->items(),
            'Catálogo de productos.',
            200,
            [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ]
        );
    }

    #[OA\Get(
        path: '/api/products/{product}',
        operationId: 'showProduct',
        summary: 'Consultar producto',
        tags: ['Productos'],
        parameters: [
            new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)
        ],
        responses: [
            new OA\Response(response: 200, description: 'Producto encontrado.'),
            new OA\Response(response: 404, description: 'Producto no encontrado.')
        ]
    )]
    public function show(Product $product): JsonResponse
    {
        return ApiResponse::success($product, 'Producto encontrado.');
    }

    #[OA\Post(
        path: '/api/products',
        operationId: 'createProduct',
        summary: 'Crear producto',
        description: 'Requiere JWT de un usuario administrador.',
        tags: ['Productos'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'price', 'stock'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Laptop Pro 14'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: '16 GB RAM y SSD de 512 GB'),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 899.99),
                    new OA\Property(property: 'stock', type: 'integer', example: 20)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Producto creado.'),
            new OA\Response(response: 401, description: 'No autenticado.'),
            new OA\Response(response: 403, description: 'Sin privilegios de administrador.'),
            new OA\Response(response: 422, description: 'Datos inválidos.')
        ]
    )]
    public function store(ProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return ApiResponse::success($product, 'Producto creado correctamente.', 201);
    }

    #[OA\Put(
        path: '/api/products/{product}',
        operationId: 'updateProduct',
        summary: 'Actualizar producto',
        description: 'Requiere JWT de un usuario administrador.',
        tags: ['Productos'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'price', 'stock'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Laptop Pro 14'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Edición actualizada'),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 849.99),
                    new OA\Property(property: 'stock', type: 'integer', example: 15)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Producto actualizado.'),
            new OA\Response(response: 401, description: 'No autenticado.'),
            new OA\Response(response: 403, description: 'Sin privilegios de administrador.'),
            new OA\Response(response: 404, description: 'Producto no encontrado.'),
            new OA\Response(response: 422, description: 'Datos inválidos.')
        ]
    )]
    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());

        return ApiResponse::success($product->fresh(), 'Producto actualizado correctamente.');
    }

    #[OA\Delete(
        path: '/api/products/{product}',
        operationId: 'deleteProduct',
        summary: 'Eliminar producto',
        description: 'Eliminación lógica para preservar el historial de órdenes.',
        tags: ['Productos'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)
        ],
        responses: [
            new OA\Response(response: 200, description: 'Producto eliminado.'),
            new OA\Response(response: 401, description: 'No autenticado.'),
            new OA\Response(response: 403, description: 'Sin privilegios de administrador.'),
            new OA\Response(response: 404, description: 'Producto no encontrado.')
        ]
    )]
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return ApiResponse::success(null, 'Producto eliminado correctamente.');
    }
}
