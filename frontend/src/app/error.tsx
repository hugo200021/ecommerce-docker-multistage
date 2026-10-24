'use client';

import { useEffect } from 'react';

export default function Error({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  useEffect(() => {
    console.error('Error detectado en el segmento:', error);
  }, [error]);

  return (
    <div className="min-h-[50vh] flex flex-col items-center justify-center p-6 text-center">
      <h2 className="text-2xl font-bold text-red-600 mb-2">¡Ocurrió un error inesperado!</h2>
      <p className="text-gray-600 mb-4">No se pudo cargar la información desde el servidor.</p>
      <button
        onClick={() => reset()}
        className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
      >
        Reintentar
      </button>
    </div>
  );
}