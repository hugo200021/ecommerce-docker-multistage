import Link from 'next/link';
import AddToCartButton from './AddToCartButton';

interface Product {
  id: number;
  name: string;
  price: number;
  description?: string;
  image?: string;
}

async function getProducts(): Promise<Product[]> {
  
  const API_URL =
  process.env.API_INTERNAL_URL ||
  process.env.NEXT_PUBLIC_API_URL ||
  'http://localhost:8000';

  try {
    const res = await fetch(`${API_URL}/api/products`, {
      cache: 'no-store', // Desactiva caché para ver cambios al instante
    });

    if (!res.ok) {
      console.error('Error en la API:', res.statusText);
      return [];
    }

    const json = await res.json();
    // La API de Laravel devuelve { success: true, data: [...] }
    return Array.isArray(json) ? json : json.data || [];
  } catch (error) {
    console.error('Error al conectar con Laravel:', error);
    return [];
  }
}

export default async function ProductList() {
  const products = await getProducts();

  if (products.length === 0) {
    return (
      <div className="bg-white p-8 rounded-lg text-slate-800 text-center shadow-sm">
        No se encontraron productos disponibles en la API de Laravel.
      </div>
    );
  }

  return (
    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      {products.map((product) => (
        <div key={product.id} className="bg-white border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition flex flex-col">
          <div className="h-48 bg-slate-100 flex items-center justify-center p-4">
            {product.image ? (
              <img src={product.image} alt={product.name} className="h-full object-contain" />
            ) : (
              <span className="text-slate-400 text-sm">Sin imagen</span>
            )}
          </div>
          <div className="p-4 flex-1 flex flex-col justify-between space-y-3">
            <div>
              <Link href={`/products/${product.id}`} className="font-semibold text-slate-800 hover:text-blue-600 transition line-clamp-1">
                {product.name}
              </Link>
              <p className="text-slate-500 text-sm line-clamp-2 mt-1">
                {product.description || 'Sin descripción disponible.'}
              </p>
            </div>
            <div className="flex items-center justify-between pt-2 border-t">
              <span className="text-lg font-bold text-slate-900">${Number(product.price).toFixed(2)}</span>
              <AddToCartButton product={product} />
            </div>
          </div>
        </div>
      ))}
    </div>
  );
}