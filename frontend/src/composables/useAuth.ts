import { computed, ref } from 'vue'
import { api } from '@/lib/api'
import { clearToken, getToken, setToken } from '@/lib/http'
import type { User } from '@/types'

const user = ref<User | null>(null)
const ready = ref(false)

export function useAuth() {
  const isAuthenticated = computed(() => !!user.value)

  async function loadMe() {
    if (!getToken()) {
      ready.value = true
      return
    }
    try {
      user.value = await api.me()
    } catch {
      clearToken()
      user.value = null
    } finally {
      ready.value = true
    }
  }

  async function login(phone: string, password: string) {
    const data = await api.login(phone, password)
    setToken(data.token)
    user.value = data.user
    return data
  }

  async function register(phone: string, password: string) {
    const data = await api.register(phone, password)
    setToken(data.token)
    user.value = data.user
    return data
  }

  function logout() {
    clearToken()
    user.value = null
  }

  return {
    user,
    ready,
    isAuthenticated,
    loadMe,
    login,
    register,
    logout,
  }
}
