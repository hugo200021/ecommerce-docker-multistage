# E-commerce Secure API

API REST desarrollada con **Laravel 12** para gestionar clientes, catálogo de productos, órdenes de compra y pagos mediante **Stripe**. La autenticación utiliza **JWT** y todos los endpoints están documentados con **Swagger/OpenAPI**.

## Requisitos

- PHP 8.3 o superior
- Composer 2
- MySQL 8 o compatible
- Laravel 12
- Cuenta de Stripe en modo de prueba

> Laravel 12 requiere PHP 8.2 o superior. Este proyecto utiliza PHP 8.3 porque la versión actual de `php-open-source-saver/jwt-auth` utilizada por el proyecto lo requiere.

## Instalación

```bash
git clone <URL_DEL_REPOSITORIO>
cd ecommerce-api
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

Crea una base de datos MySQL y configura estas variables en `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce_api
DB_USERNAME=root
DB_PASSWORD=
```

Configura las credenciales de Stripe de prueba:

```env
STRIPE_KEY=pk_test_xxxxxxxxxxxxx
STRIPE_SECRET=sk_test_xxxxxxxxxxxxx
STRIPE_CURRENCY=usd
```

Para disponer de un usuario administrador al ejecutar los seeders, define también:

```env
ADMIN_NAME=Administrador
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=UnaClaveSegura2026
```

La contraseña de administrador solo se utiliza desde el entorno local y **no debe subirse al repositorio**.

Ejecuta migraciones y seeders:

```bash
php artisan migrate --seed
```

Genera la documentación Swagger:

```bash
php artisan l5-swagger:generate
```

Inicia el servidor:

```bash
php artisan serve
```

Swagger UI quedará disponible en:

```text
http://localhost:8000/api/documentation
```

## Autenticación JWT

Los clientes se registran mediante `POST /api/auth/register`. El registro público siempre crea usuarios con rol `client`; el rol de administrador no puede ser enviado desde la API pública.

Después del login, utiliza el token recibido como Bearer Token:

```http
Authorization: Bearer <TOKEN_JWT>
Accept: application/json
```

## Endpoints principales

| Método | Endpoint | Acceso | Descripción |
|---|---|---|---|
| POST | `/api/auth/register` | Público | Registrar cliente |
| POST | `/api/auth/login` | Público | Iniciar sesión |
| GET | `/api/me` | JWT | Consultar usuario autenticado |
| POST | `/api/auth/logout` | JWT | Invalidar token actual |
| GET | `/api/products` | Público | Listar y filtrar productos |
| GET | `/api/products/{product}` | Público | Consultar producto |
| POST | `/api/products` | Admin + JWT | Crear producto |
| PUT | `/api/products/{product}` | Admin + JWT | Actualizar producto |
| DELETE | `/api/products/{product}` | Admin + JWT | Eliminar producto de forma lógica |
| POST | `/api/orders` | JWT | Crear orden de compra |
| GET | `/api/orders` | JWT | Historial del usuario autenticado |
| POST | `/api/orders/{order}/pay` | JWT | Procesar pago de la orden con Stripe |

## Flujo de compra

1. El cliente se registra o inicia sesión.
2. Consulta el catálogo público.
3. Envía una orden indicando únicamente `product_id` y `quantity`.
4. El servidor bloquea las filas de productos mientras valida y descuenta stock.
5. El total se calcula con los precios almacenados en MySQL; el cliente no puede definir el importe.
6. El cliente paga la orden enviando un `payment_method` de Stripe.
7. La transacción se registra en `payments` y la orden cambia a `paid` cuando Stripe confirma el pago.

Ejemplo de orden:

```json
{
  "items": [
    {"product_id": 1, "quantity": 2},
    {"product_id": 3, "quantity": 1}
  ]
}
```

Ejemplo de pago en modo de prueba:

```json
{
  "payment_method": "pm_card_visa"
}
```

## Seguridad aplicada

- JWT con expiración e invalidación al cerrar sesión.
- Rate limiting en registro, login, creación de órdenes y pagos.
- Form Requests para validar entradas.
- Registro público sin posibilidad de elevar privilegios.
- Autorización de administrador para modificar el catálogo.
- Historial de órdenes limitado al usuario autenticado.
- El monto del pago se obtiene exclusivamente de la orden almacenada.
- Bloqueo pesimista de stock con `lockForUpdate()` para reducir condiciones de carrera.
- Transacciones de base de datos al crear órdenes y registrar pagos.
- Uso de idempotencia de Stripe para evitar cobros duplicados por reintentos.
- Eliminación lógica de productos para conservar el historial de compras.
- Errores JSON consistentes sin exponer rutas internas, consultas ni stack traces.
- `.env` excluido del repositorio; solo se incluye `.env.example`.

## Respuestas JSON

Respuesta correcta:

```json
{
  "success": true,
  "message": "Operación realizada correctamente.",
  "data": {}
}
```

Error de validación:

```json
{
  "success": false,
  "message": "Los datos enviados no son válidos.",
  "errors": {
    "campo": ["Descripción del error"]
  }
}
```

## Base de datos

Tablas principales:

- `users`
- `products`
- `orders`
- `order_items`
- `payments`

Los seeders incluyen productos de ejemplo. El usuario administrador se crea únicamente cuando `ADMIN_EMAIL` y `ADMIN_PASSWORD` están configurados en `.env`.

## Swagger

La configuración utiliza `darkaonline/l5-swagger`. Después de modificar anotaciones o atributos OpenAPI, regenera la especificación con:

```bash
php artisan l5-swagger:generate
```

La interfaz Swagger permite probar los endpoints protegidos usando el botón **Authorize** e ingresando el JWT obtenido en el login.
