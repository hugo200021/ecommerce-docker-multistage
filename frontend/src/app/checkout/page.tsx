'use client';

import { useCart } from '@/context/CartContext';
import { createOrderAction } from '@/actions/orders';
import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { CreditCard, CheckCircle2 } from 'lucide-react';

export default function CheckoutPage() {
  const { cart, total, clearCart } = useCart();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);
  const router = useRouter();

  const handlePayment = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    // Formatear items del carrito para la API de Laravel
    const items = cart.map((item) => ({
      product_id: item.product.id,
      quantity: item.quantity,
      price: item.product.price,
    }));

    const result = await createOrderAction(items);

    if (result.error) {
      setError(result.error);
      setLoading(false);
    } else {
      setSuccess(true);
      clearCart();
      setTimeout(() => {
        router.push('/orders');
      }, 2500);
    }
  };

  if (success) {
    return (
      <div className="max-w-md mx-auto mt-12 bg-white p-8 rounded-lg border text-center space-y-4">
        <CheckCircle2 className="w-16 h-16 text-green-500 mx-auto animate-bounce" />
        <h2 className="text-2xl font-bold text-slate-900">¡Pago Exitoso!</h2>
        <p className="text-slate-600">Tu orden ha sido procesada correctamente en la API.</p>
        <p className="text-xs text-slate-400">Redirigiendo a tu historial de compras...</p>
      </div>
    );
  }

  return (
    <div className="max-w-xl mx-auto space-y-6">
      <h1 className="text-3xl font-extrabold text-slate-900 border-b pb-4">Finalizar Compra</h1>

      {error && (
        <div className="bg-red-50 text-red-600 p-3 rounded text-sm">
          {error}
        </div>
      )}

      <div className="bg-white p-6 rounded-lg border shadow-sm space-y-6">
        <div className="border-b pb-4">
          <h2 className="font-semibold text-slate-700 mb-2">Resumen del Pedido</h2>
          <div className="flex justify-between text-slate-600 text-sm">
            <span>Productos ({cart.length}):</span>
            <span className="font-medium text-slate-900">${total.toFixed(2)}</span>
          </div>
        </div>

        <form onSubmit={handlePayment} className="space-y-4">
          <h2 className="font-semibold text-slate-700 flex items-center gap-2">
            <CreditCard className="w-5 h-5 text-blue-600" /> Detalles de Tarjeta (Stripe Test)
          </h2>

          <div>
            <label className="block text-xs font-medium text-slate-600 mb-1">Número de Tarjeta</label>
            <input
              type="text"
              placeholder="4242 4242 4242 4242"
              required
              className="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-blue-500 outline-none"
            />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-medium text-slate-600 mb-1">Vencimiento</label>
              <input
                type="text"
                placeholder="MM/YY"
                required
                className="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-blue-500 outline-none"
              />
            </div>
            <div>
              <label className="block text-xs font-medium text-slate-600 mb-1">CVC</label>
              <input
                type="text"
                placeholder="123"
                required
                className="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-blue-500 outline-none"
              />
            </div>
          </div>

          <button
            type="submit"
            disabled={loading || cart.length === 0}
            className="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-md transition disabled:bg-gray-300 mt-4"
          >
            {loading ? 'Procesando Pago con Stripe...' : `Pagar $${total.toFixed(2)}`}
          </button>
        </form>
      </div>
    </div>
  );
}