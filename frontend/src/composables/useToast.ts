import { reactive } from 'vue'

export interface ToastItem {
  id: number
  message: string
  type: 'success' | 'error' | 'info'
}

const toasts = reactive<ToastItem[]>([])
let seed = 0

export function toast(
  message: string,
  type: 'success' | 'error' | 'info' = 'info',
) {
  const id = ++seed
  toasts.push({ id, message, type })

  setTimeout(() => {
    const idx = toasts.findIndex((t) => t.id === id)
    if (idx !== -1) toasts.splice(idx, 1)
  }, 3000)
}

export function useToast() {
  return { toasts }
}
