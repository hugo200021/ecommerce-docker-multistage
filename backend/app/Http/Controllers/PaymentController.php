<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class PaymentController extends Controller
{
    #[OA\Post(
        path: '/api/orders/{order}/pay',
        operationId: 'payOrder',
        summary: 'Procesar pago de una orden con Stripe',
        description: 'El importe se obtiene de la orden almacenada; nunca se acepta un monto enviado por el cliente.',
        tags: ['Pagos'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['payment_method'],
                properties: [
                    new OA\Property(property: 'payment_method', type: 'string', example: 'pm_card_visa')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Pago confirmado o previamente procesado.'),
            new OA\Response(response: 202, description: 'Pago creado, pendiente de una acción adicional de Stripe.'),
            new OA\Response(response: 401, description: 'No autenticado.'),
            new OA\Response(response: 404, description: 'Orden no encontrada para el usuario autenticado.'),
            new OA\Response(response: 402, description: 'Stripe rechazó el pago.'),
            new OA\Response(response: 409, description: 'La orden no puede pagarse en su estado actual.'),
            new OA\Response(response: 422, description: 'Datos inválidos.'),
            new OA\Response(response: 429, description: 'Demasiados intentos de pago.')
        ]
    )]
    public function pay(PaymentRequest $request, Order $order): JsonResponse
    {
        if ((int) $order->user_id !== (int) $request->user('api')->id) {
            abort(404);
        }

        if ($order->status === 'paid') {
            $payment = $order->payments()->where('status', 'succeeded')->latest()->first();

            return ApiResponse::success($payment, 'La orden ya se encuentra pagada.');
        }

        if ($order->status !== 'pending') {
            return ApiResponse::error('La orden no se encuentra disponible para pago.', 409);
        }

        $secret = config('services.stripe.secret');

        if (! is_string($secret) || $secret === '') {
            return ApiResponse::error('La pasarela de pago no está configurada.', 503);
        }

        $currency = strtolower((string) config('services.stripe.currency', 'usd'));
        $idempotencyKey = 'order-'.$order->id.'-payment';

        try {
            $stripe = new StripeClient($secret);
            $intent = $stripe->paymentIntents->create([
                'amount' => $this->moneyToCents((string) $order->total_amount),
                'currency' => $currency,
                'payment_method' => $request->validated('payment_method'),
                'confirm' => true,
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never',
                ],
                'description' => 'Pago de orden #'.$order->id,
                'metadata' => [
                    'order_id' => (string) $order->id,
                    'user_id' => (string) $request->user('api')->id,
                ],
            ], [
                'idempotency_key' => $idempotencyKey,
            ]);
        } catch (ApiErrorException $exception) {
            return ApiResponse::error(
                'Stripe no pudo procesar el pago. Verifica el método de pago e inténtalo nuevamente.',
                402
            );
        }

        $payment = DB::transaction(function () use ($intent, $order, $currency) {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            $payment = Payment::updateOrCreate(
                ['stripe_payment_intent_id' => $intent->id],
                [
                    'order_id' => $lockedOrder->id,
                    'amount' => $lockedOrder->total_amount,
                    'currency' => $currency,
                    'status' => $intent->status,
                ]
            );

            if ($intent->status === 'succeeded') {
                $lockedOrder->update(['status' => 'paid']);
            }

            return $payment;
        }, 3);

        $data = [
            'payment' => $payment,
            'order_status' => $intent->status === 'succeeded' ? 'paid' : 'pending',
        ];

        if ($intent->status !== 'succeeded') {
            $data['stripe_status'] = $intent->status;
            $data['client_secret'] = $intent->client_secret;

            return ApiResponse::success(
                $data,
                'Stripe requiere completar una acción adicional para confirmar el pago.',
                202
            );
        }

        return ApiResponse::success($data, 'Pago procesado correctamente.');
    }

    private function moneyToCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');
        $fraction = str_pad(substr($fraction, 0, 2), 2, '0');

        return ((int) $whole * 100) + (int) $fraction;
    }
}
