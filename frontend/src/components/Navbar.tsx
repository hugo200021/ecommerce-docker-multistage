'use client';

import Link from 'next/link';
import { useCart } from '@/context/CartContext';
import { ShoppingCart, User, LogOut, Package } from 'lucide-react';
import { logoutAction } from '@/actions/auth';

export default function Navbar() {
  const { cart } = useCart();
  const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);

  return (
    <header className="bg-slate-900 text-white sticky top-0 z-50 shadow-md">
      <div className="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
        <Link href="/" className="text-xl font-bold tracking-tight text-blue-400 hover:text-blue-300 transition">
          E-Commerce App
        </Link>

        <nav className="flex items-center gap-6">
          <Link href="/" className="hover:text-blue-400 transition text-sm font-medium">
            Catálogo
          </Link>
          <Link href="/orders" className="hover:text-blue-400 transition text-sm font-medium flex items-center gap-1">
            <Package className="w-4 h-4" /> Mis Órdenes
          </Link>
          <Link href="/cart" className="relative hover:text-blue-400 transition flex items-center gap-1">
            <ShoppingCart className="w-5 h-5" />
            {totalItems > 0 && (
              <span className="absolute -top-2 -right-3 bg-blue-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                {totalItems}
              </span>
            )}
          </Link>
          <Link href="/login" className="hover:text-blue-400 transition flex items-center gap-1 text-sm font-medium">
            <User className="w-4 h-4" /> Login
          </Link>
          <form action={logoutAction}>
            <button type="submit" title="Cerrar Sesión" className="hover:text-red-400 transition flex items-center">
              <LogOut className="w-4 h-4" />
            </button>
          </form>
        </nav>
      </div>
    </header>
  );
}