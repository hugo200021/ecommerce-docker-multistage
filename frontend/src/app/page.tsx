import { Suspense } from 'react';
import ProductList from '@/components/ProductList';
export const revalidate = 60; // Revalidación ISR cada 60 segundos

export default function HomePage() {
  return (
    <div className="space-y-6">
      <div className="border-b pb-4">
        <h1 className="text-3xl font-extrabold text-slate-900">Catálogo de Productos</h1>
        <p className="text-slate-600 mt-1">Explora nuestros productos alimentados por la API en Laravel.</p>
      </div>

      {/* Requisito explícito: Utilizar Suspense en sección pesada */}
      <Suspense fallback={<ProductSkeleton />}>
        <ProductList />
      </Suspense>
    </div>
  );
}

function ProductSkeleton() {
  return (
    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      {[...Array(8)].map((_, i) => (
        <div key={i} className="border rounded-lg p-4 space-y-4 animate-pulse bg-white">
          <div className="h-48 bg-slate-200 rounded" />
          <div className="h-4 bg-slate-200 rounded w-3/4" />
          <div className="h-4 bg-slate-200 rounded w-1/2" />
        </div>
      ))}
    </div>
  );
}