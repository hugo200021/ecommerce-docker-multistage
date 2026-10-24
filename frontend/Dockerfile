# 1. Dependencias completas para compilar (capa cacheable)
FROM node:22-alpine AS deps
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci

# 2. Compilación Next.js
FROM deps AS builder
WORKDIR /app
COPY . .
ARG NEXT_PUBLIC_API_URL=http://localhost:8000
ARG NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY=
ENV NEXT_PUBLIC_API_URL=$NEXT_PUBLIC_API_URL
ENV NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY=$NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY
RUN npm run build

# 3. Dependencias de producción (sin paquetes dev)
FROM node:22-alpine AS production_deps
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --omit=dev && npm cache clean --force


# 4. Imagen final de producción optimizada
FROM node:22-alpine AS runner

WORKDIR /app
ENV NODE_ENV=production

# Copiar archivos asignando el propietario directamente
COPY --chown=node:node --from=production_deps /app/node_modules ./node_modules
COPY --chown=node:node --from=builder /app/.next ./.next
COPY --chown=node:node --from=builder /app/public ./public
COPY --chown=node:node --from=builder /app/package.json ./package.json
COPY --chown=node:node --from=builder /app/next.config.ts ./next.config.ts

# Ejecutar con usuario sin privilegios
USER node

EXPOSE 3000

CMD ["npm", "run", "start"]

