'use client';

import { useCart, Product } from '@/context/CartContext';
import { ShoppingCart } from 'lucide-react';

export default function AddToCartButton({ product }: { product: Product }) {
  const { addToCart } = useCart();

  return (
    <button
      onClick={() => addToCart(product)}
      className="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-2 rounded flex items-center gap-1 transition active:scale-95"
    >
      <ShoppingCart className="w-3.5 h-3.5" /> Agregar
    </button>
  );
}