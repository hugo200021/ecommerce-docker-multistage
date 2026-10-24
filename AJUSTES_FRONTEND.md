# Adaptaciones necesarias en el frontend original

Revisa los archivos originales antes de construir; no están corregidos automáticamente por este ZIP.

1. En los Server Components y Server Actions que llaman a Laravel, sustituye una asignación como `const API_URL = process.env.NEXT_PUBLIC_API_URL` por `const API_URL = process.env.API_INTERNAL_URL || process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000';`. En el código público que se ejecuta en el navegador, conserva `NEXT_PUBLIC_API_URL`.
   - `src/components/ProductList.tsx`
   - `src/actions/auth.ts`
   - `src/actions/orders.ts`
   - `src/app/orders/page.tsx`
   - Busca otros usos con `Get-ChildItem .rontend\src -Recurse -Include *.ts,*.tsx | Select-String 'NEXT_PUBLIC_API_URL'`.
2. En `src/actions/auth.ts`, corrige `/api/login` a `/api/auth/login` y cambia `const token = data.access_token || data.token;` a `const token = data.data?.access_token;`. Laravel devuelve `{ success, message, data: { access_token } }`.
3. En ese mismo `auth.ts`, para probar en HTTP localhost, sustituye `secure: process.env.NODE_ENV === 'production'` por `secure: process.env.COOKIE_SECURE === 'true'`. SOLO en HTTP local usar `COOKIE_SECURE=false`; al desplegar con HTTPS exigir `COOKIE_SECURE=true`.
4. En `src/app/register/page.tsx`, corrige `/api/register` a `/api/auth/register`.
5. En `src/actions/orders.ts`, cambia `data.id || data.order_id` por `data.data?.id` (la API envuelve el recurso en `data`).
6. En `src/app/orders/page.tsx` el backend devuelve `total_amount`, no `total`. Ajusta la interfaz y el renderizado.
7. Actualmente la UI `src/app/checkout/page.tsx` crea la orden pero no llama a `/api/orders/{order}/pay`; no la presentes como prueba de cobro exitoso si no implementas esa llamada. Puedes probar Stripe por Swagger de manera independiente usando credenciales de prueba.

IMPORTANTE: Hay diferencias funcionales entre el código de frontend y backend independientes; contenerizarlos no las corrige por sí solo. La meta de esta práctica es verificar catálogo, autenticación, órdenes y servicios; la integración del pago completo puede ser una tarea adicional si el profesor no la exige.
