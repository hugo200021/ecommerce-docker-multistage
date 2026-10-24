# E-commerce Docker Multi-stage — KODIGO

Orquestación de un frontend Next.js 16+, una API REST Laravel 12 con PHP 8.3-FPM y Nginx, y MySQL 8.0. Se parte de dos proyectos existentes.

## Arquitectura

Navegador -> Next.js (localhost:3000). Next.js (API_INTERNAL_URL) -> Nginx (nombre DNS `nginx`:80) -> PHP-FPM (`backend`:9000) -> MySQL (`mysql`:3306). La API también se consulta desde el navegador en localhost:8000. MySQL no publica puertos al host.

## Requisitos

Docker Desktop con Compose v2 y Git. No hace falta instalar PHP, Composer o MySQL en el host.

## Instalación

1. Colocar el proyecto Next.js original dentro de `frontend/` y la API Laravel original dentro de `backend/`, junto a los Dockerfiles de este repositorio.
2. Corregir en Server Components / Server Actions que usen `NEXT_PUBLIC_API_URL` la preferencia por `process.env.API_INTERNAL_URL` (sin cambiar el uso del URL público en Client Components). Ver instrucciones detalladas de la guía de entrega.
3. Copiar `.env.example` a `.env`, configurar las contraseñas y, opcionalmente, las credenciales de Stripe de prueba.
4. `docker compose config` y `docker compose build`.
5. Generar las claves con los siguientes comandos y colocar los valores resultantes en el `.env` raíz:
   - `docker compose run --rm --no-deps backend php artisan key:generate --show`
   - `docker compose run --rm --no-deps backend php artisan jwt:secret --show`
6. `docker compose up -d`.
7. `docker compose exec backend php artisan package:discover --ansi`.
8. `docker compose exec backend php artisan migrate --seed --force` (solo si se desea cargar datos de ejemplo).
9. `docker compose exec backend php artisan l5-swagger:generate`.
10. `docker compose ps`.

## URLs

- Tienda: http://localhost:3000
- API: http://localhost:8000/api/products
- Documentación Swagger: http://localhost:8000/api/documentation

## Optimizaciones

- **Next.js**: `deps -> builder -> production_deps -> runner`. `npm ci` aprovecha la caché del lockfile. La imagen final contiene `.next`, `public`, archivos de configuración y dependencias de producción; usa un usuario no root. Se mantiene `next start` como exige la actividad.
- **Laravel**: `php_base -> dependencies -> builder -> runtime`. Composer y Git quedan fuera de runtime. El directorio `vendor/` compilado en la etapa builder se copia a la imagen final. PHP 8.3 cumple la exigencia de este proyecto.
- **Nginx**: sirve los archivos públicos y delega las solicitudes PHP a FPM mediante FastCGI.
- **MySQL**: imagen oficial 8.0, volumen persistente, sin exposición del puerto 3306 al host.
- **Compose**: espera `mysql` sano antes de iniciar PHP-FPM; un comando levanta el conjunto.

## Verificación

`docker compose ps`
`docker compose logs --tail 100 backend`
`docker image ls`
`docker history ecommerce-docker-multistage-frontend`

Para apagar sin borrar los datos: `docker compose down`. **Evita** `docker compose down -v` si deseas conservar MySQL.

## Advertencia local

Las claves/credenciales nunca se suben a GitHub. La demo HTTP localhost requiere una configuración de cookie distinta a HTTPS productivo: el frontend actual configura `secure=true` automáticamente si `NODE_ENV=production`; en pruebas locales HTTP debe ajustarse de manera explícita (`COOKIE_SECURE=false`) y HTTPS real debe exigir `secure=true`. Verificar además que el login llame `/api/auth/login`, registro `/api/auth/register`, que el token esté en `data.access_token` y que la orden devuelva `data.id` en el sobre JSON.

No se ha comprobado una ejecución completa contra los repositorios originales en esta plantilla; se deben completar ajustes y realizar las pruebas de aceptación antes de entregar.
