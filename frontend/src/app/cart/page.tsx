'use client';

import { useCart } from '@/context/CartContext';
import Link from 'next/link';
import { Trash2, ShoppingBag, ArrowRight } from 'lucide-react';

export default function CartPage() {
  const { cart, removeFromCart, clearCart, total } = useCart();

  if (cart.length === 0) {
    return (
      <div className="text-center py-16 bg-white rounded-lg border space-y-4">
        <ShoppingBag className="w-16 h-16 text-slate-300 mx-auto" />
        <h2 className="text-xl font-bold text-slate-700">Tu carrito está vacío</h2>
        <p className="text-slate-500">Agrega algunos productos desde nuestro catálogo.</p>
        <Link
          href="/"
          className="inline-block bg-blue-600 text-white px-6 py-2 rounded-md font-medium hover:bg-blue-700 transition"
        >
          Ir al Catálogo
        </Link>
      </div>
    );
  }

  return (
    <div className="space-y-6 max-w-4xl mx-auto">
      <h1 className="text-3xl font-extrabold text-slate-900 border-b pb-4">Carrito de Compras</h1>

      <div className="bg-white rounded-lg border shadow-sm divide-y">
        {cart.map(({ product, quantity }) => (
          <div key={product.id} className="p-4 flex items-center justify-between gap-4">
            <div className="flex items-center gap-4">
              <div className="w-16 h-16 bg-slate-100 rounded flex items-center justify-center font-bold text-slate-400">
                {product.image ? (
                  <img src={product.image} alt={product.name} className="w-full h-full object-contain" />
                ) : (
                  'IMG'
                )}
              </div>
              <div>
                <h3 className="font-semibold text-slate-800">{product.name}</h3>
                <p className="text-sm text-slate-500">
                  ${Number(product.price).toFixed(2)} x {quantity}
                </p>
              </div>
            </div>

            <div className="flex items-center gap-6">
              <span className="font-bold text-slate-900">
                ${(product.price * quantity).toFixed(2)}
              </span>
              <button
                onClick={() => removeFromCart(product.id)}
                className="text-red-500 hover:text-red-700 p-1"
                title="Eliminar producto"
              >
                <Trash2 className="w-5 h-5" />
              </button>
            </div>
          </div>
        ))}
      </div>

      <div className="bg-slate-50 p-6 rounded-lg border flex flex-col sm:flex-row items-center justify-between gap-4">
        <button
          onClick={clearCart}
          className="text-sm text-slate-500 hover:text-slate-800 underline"
        >
          Vaciar carrito
        </button>

        <div className="flex items-center gap-6">
          <div>
            <span className="text-slate-600 mr-2">Total:</span>
            <span className="text-2xl font-bold text-slate-900">${total.toFixed(2)}</span>
          </div>

          <Link
            href="/checkout"
            className="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-md font-semibold flex items-center gap-2 transition"
          >
            Proceder al Pago <ArrowRight className="w-4 h-4" />
          </Link>
        </div>
      </div>
    </div>
  );
}