# 🛒 E-Commerce App - Frontend en Next.js

Aplicación de comercio electrónico desarrollada con **Next.js (App Router)**, **TypeScript** y **Tailwind CSS**, diseñada como caso de estudio para consumir una API REST segura desarrollada en **Laravel 12 + Swagger + Stripe**.

---

## ⚙️ Configuración de Variables de Entorno (.env.example)

Para conectar el frontend con la API en Laravel Herd o local, crea un archivo `.env.local` en la raíz del proyecto basándote en la siguiente plantilla:

NEXT_PUBLIC_API_URL=http://ecommerce-api-segura.test
NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY=pk_test_tu_llave_publica

---

## 🚀 Pasos para Ejecutar el Proyecto

### 1. Requisitos Previos
* **Node.js**: v18.0.0 o superior.
* **npm** o **yarn**.
* **Backend de Laravel**: Ejecutándose vía Laravel Herd en http://ecommerce-api-segura.test o mediante `php artisan serve` en http://localhost:8000.

### 2. Instalación de Dependencias
Clona el repositorio e instala los paquetes necesarios:

git clone https://github.com/hugo200021/frontend-ecommerce-nextjs.git
cd frontend-ecommerce-nextjs
npm install

### 3. Configuración del Entorno Local
Copia la plantilla de variables de entorno y ajusta los valores si es necesario:

cp .env.example .env.local

### 4. Iniciar el Servidor de Desarrollo
Ejecuta el servidor local de Next.js:

npm run dev

Abre tu navegador e ingresa a http://localhost:3000.

---

## 🗺️ Rutas Implementadas

| Ruta | Descripción | Tipo de Componente / Técnica |
| :--- | :--- | :--- |
| `/` | **Catálogo de Productos:** Consulta asíncrona a la API de Laravel (`GET /api/products`) con esqueletos de carga mediante `<Suspense>`. | Server Component (`ProductList`) |
| `/products/[id]` | **Detalle de Producto:** Vista individual dinámica para consultar especificaciones de un producto. | Dynamic Route (`/products/[id]`) |
| `/cart` | **Carrito de Compras:** Gestión local de artículos, cálculo de totales y persistencia de estado. | Client Component (`CartContext`) |
| `/login` | **Autenticación (Login):** Formulario de inicio de sesión e integración de sesión vía JWT. | Server Action / Form |
| `/register` | **Autenticación (Registro):** Formulario para registrar nuevos usuarios en la API de Laravel. | Server Action / Form |
| `/checkout` | **Proceso de Pago:** Confirmación de la orden e integración con pasarela de pago (Stripe). | Protected Route |
| `/orders` | **Historial de Compras:** Consulta y listado de órdenes procesadas y confirmadas del usuario. | Protected Route |

---

## 🛡️ Manejo de Errores y Rendimiento

* **`loading.tsx`:** Proporciona retroalimentación visual instantánea mientras los Server Components obtienen los datos.
* **`error.tsx`:** Captura excepciones de red o servidor de forma resiliente, permitiendo la recuperación sin romper la interfaz.
* **Revalidación:** Uso de `revalidatePath('/orders')` tras completar una compra para mantener los datos del historial actualizados.