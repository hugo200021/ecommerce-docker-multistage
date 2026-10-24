'use server';

import { cookies } from 'next/headers';
import { revalidatePath } from 'next/cache';
import { redirect } from 'next/navigation';

const API_URL = process.env.NEXT_PUBLIC_API_URL;

export async function createOrderAction(cartItems: any[]) {
  const cookieStore = await cookies();
  const token = cookieStore.get('auth_token')?.value;

  if (!token) {
    return { error: 'Debes iniciar sesión para realizar la compra' };
  }

  try {
    const res = await fetch(`${API_URL}/api/orders`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({ items: cartItems }),
    });

    const data = await res.json();

    if (!res.ok) {
      return { error: data.message || 'Error al procesar la orden' };
    }

    // Revalidación explícita exigida en los requisitos de rendimiento
    revalidatePath('/orders');
    return { success: true, orderId: data.id || data.order_id };
  } catch (err) {
    return { error: 'Error de comunicación con el servidor' };
  }
}