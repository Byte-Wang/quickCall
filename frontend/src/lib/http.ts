import { apiUrl } from './config'

const TOKEN_KEY = 'quickdial_token'

export function getToken(): string | null {
  return localStorage.getItem(TOKEN_KEY)
}

export function setToken(token: string): void {
  localStorage.setItem(TOKEN_KEY, token)
}

export function clearToken(): void {
  localStorage.removeItem(TOKEN_KEY)
}

interface ApiEnvelope<T> {
  code: number
  message: string
  data: T
}

export async function request<T>(
  path: string,
  options: RequestInit = {},
): Promise<T> {
  const headers: Record<string, string> = {
    ...(options.headers as Record<string, string> | undefined),
  }

  const token = getToken()
  if (token) {
    headers.Authorization = `Bearer ${token}`
  }

  if (typeof options.body === 'string') {
    headers['Content-Type'] = 'application/json'
  }

  let res: Response
  try {
    res = await fetch(apiUrl(path), { ...options, headers })
  } catch {
    throw new Error('网络异常，请稍后重试')
  }

  let json: ApiEnvelope<T>
  try {
    json = (await res.json()) as ApiEnvelope<T>
  } catch {
    throw new Error('响应解析失败')
  }

  if (json.code !== 0) {
    if (json.code === 401) {
      clearToken()
    }
    throw new Error(json.message || '请求失败')
  }

  return json.data
}
