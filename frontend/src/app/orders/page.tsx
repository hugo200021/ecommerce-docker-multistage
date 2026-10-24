import { cookies } from 'next/headers';
import { Package } from 'lucide-react';

interface Order {
  id: number;
  total: number;
  created_at: string;
  status?: string;
}

async function getUserOrders(): Promise<Order[]> {
  const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000';
  const cookieStore = await cookies();
  const token = cookieStore.get('auth_token')?.value;

  if (!token) return [];

  try {
    const res = await fetch(`${API_URL}/api/orders`, {
      headers: { Authorization: `Bearer ${token}` },
      next: { tags: ['orders'] },
    });

    if (!res.ok) return [];

    const data = await res.json();
    return Array.isArray(data) ? data : data.data || [];
  } catch (err) {
    console.error('Error cargando órdenes:', err);
    return [];
  }
}

export default async function OrdersPage() {
  const orders = await getUserOrders();

  return (
    <div className="space-y-6 max-w-4xl mx-auto">
      <div className="border-b pb-4">
        <h1 className="text-3xl font-extrabold text-slate-900">Historial de Compras</h1>
        <p className="text-slate-600 mt-1">Órdenes registradas y revalidadas mediante Server Actions.</p>
      </div>

      {orders.length === 0 ? (
        <div className="text-center py-12 bg-white rounded-lg border border-dashed border-slate-300 space-y-3">
          <Package className="w-12 h-12 text-slate-300 mx-auto" />
          <p className="text-slate-500">No tienes órdenes de compra registradas.</p>
        </div>
      ) : (
        <div className="space-y-4">
          {orders.map((order) => (
            <div key={order.id} className="bg-white border rounded-lg p-5 shadow-sm flex items-center justify-between">
              <div>
                <span className="text-xs font-semibold uppercase text-blue-600 bg-blue-50 px-2 py-1 rounded">
                  Orden #{order.id}
                </span>
                <p className="text-xs text-slate-400 mt-2">
                  Fecha: {new Date(order.created_at).toLocaleDateString()}
                </p>
              </div>

              <div className="text-right">
                <span className="text-lg font-bold text-slate-900">${Number(order.total || 0).toFixed(2)}</span>
                <p className="text-xs text-green-600 font-medium">{order.status || 'Completado'}</p>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}