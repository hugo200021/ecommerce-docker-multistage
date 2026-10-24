<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Http\Requests\OrderListRequest;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class OrderController extends Controller
{
    #[OA\Post(
        path: '/api/orders',
        operationId: 'createOrder',
        summary: 'Crear orden de compra',
        description: 'El total se calcula en el servidor usando los precios almacenados. El stock se bloquea durante la transacción para evitar sobreventa.',
        tags: ['Órdenes'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['items'],
                properties: [
                    new OA\Property(
                        property: 'items',
                        type: 'array',
                        minItems: 1,
                        items: new OA\Items(
                            required: ['product_id', 'quantity'],
                            properties: [
                                new OA\Property(property: 'product_id', type: 'integer', example: 1),
                                new OA\Property(property: 'quantity', type: 'integer', minimum: 1, example: 2)
                            ],
                            type: 'object'
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Orden creada.'),
            new OA\Response(response: 401, description: 'No autenticado.'),
            new OA\Response(response: 409, description: 'Stock insuficiente.'),
            new OA\Response(response: 422, description: 'Datos inválidos.')
        ]
    )]
    public function store(OrderRequest $request): JsonResponse
    {
        $items = $request->validated('items');

        $order = DB::transaction(function () use ($items, $request) {
            $productIds = collect($items)
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values();

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $totalCents = 0;
            $orderItems = [];

            foreach ($items as $item) {
                $product = $products->get((int) $item['product_id']);
                $quantity = (int) $item['quantity'];

                if (! $product) {
                    abort(422, 'Uno de los productos ya no está disponible.');
                }

                if ($product->stock < $quantity) {
                    throw new InsufficientStockException(
                        $product->name,
                        $quantity,
                        $product->stock
                    );
                }

                $priceCents = $this->moneyToCents((string) $product->price);
                $totalCents += $priceCents * $quantity;

                $product->stock -= $quantity;
                $product->save();

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => (string) $product->price,
                ];
            }

            $order = Order::create([
                'user_id' => $request->user('api')->id,
                'total_amount' => $this->centsToMoney($totalCents),
                'status' => 'pending',
            ]);

            $order->items()->createMany($orderItems);

            return $order->load('items.product');
        }, 3);

        return ApiResponse::success($order, 'Orden creada correctamente.', 201);
    }

    #[OA\Get(
        path: '/api/orders',
        operationId: 'orderHistory',
        summary: 'Consultar historial de compras',
        tags: ['Órdenes'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['pending', 'paid', 'cancelled'])),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 50), example: 10)
        ],
        responses: [
            new OA\Response(response: 200, description: 'Historial paginado del usuario autenticado.'),
            new OA\Response(response: 401, description: 'No autenticado.'),
            new OA\Response(response: 422, description: 'Filtros inválidos.')
        ]
    )]
    public function index(OrderListRequest $request): JsonResponse
    {
        $query = Order::query()
            ->where('user_id', $request->user('api')->id)
            ->with(['items.product', 'payments'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->validated('status'));
        }

        $perPage = (int) ($request->validated('per_page') ?? 10);
        $orders = $query->paginate($perPage);

        return ApiResponse::success(
            $orders->items(),
            'Historial de compras.',
            200,
            [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ]
        );
    }

    private function moneyToCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');
        $fraction = str_pad(substr($fraction, 0, 2), 2, '0');

        return ((int) $whole * 100) + (int) $fraction;
    }

    private function centsToMoney(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }
}
