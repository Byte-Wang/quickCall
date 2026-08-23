import { request } from './http'
import type { AuthPayload, Contact, DialPage, UploadPayload, User } from '@/types'

export interface PagePayload {
  name?: string
  bg_type?: 'color' | 'image'
  bg_color?: string
  bg_image?: string
  font_size?: number
  avatar_size?: number
  phone_size?: number
  show_name?: boolean
}

export interface ContactPayload {
  name?: string
  phone?: string
  avatar?: string
  bg_color?: string
  font_size?: number | null
}

export const api = {
  register(phone: string, password: string) {
    return request<AuthPayload>('/api/auth/register', {
      method: 'POST',
      body: JSON.stringify({ phone, password }),
    })
  },

  login(phone: string, password: string) {
    return request<AuthPayload>('/api/auth/login', {
      method: 'POST',
      body: JSON.stringify({ phone, password }),
    })
  },

  me() {
    return request<User>('/api/auth/me')
  },

  listPages() {
    return request<DialPage[]>('/api/dial-pages')
  },

  createPage(name: string) {
    return request<DialPage>('/api/dial-pages', {
      method: 'POST',
      body: JSON.stringify({ name }),
    })
  },

  getPage(id: number) {
    return request<DialPage>(`/api/dial-pages/${id}`)
  },

  updatePage(id: number, payload: PagePayload) {
    return request<DialPage>(`/api/dial-pages/${id}`, {
      method: 'PUT',
      body: JSON.stringify(payload),
    })
  },

  deletePage(id: number) {
    return request<null>(`/api/dial-pages/${id}`, { method: 'DELETE' })
  },

  addContact(pageId: number, payload: ContactPayload) {
    return request<Contact>(`/api/dial-pages/${pageId}/contacts`, {
      method: 'POST',
      body: JSON.stringify(payload),
    })
  },

  updateContact(id: number, payload: ContactPayload) {
    return request<Contact>(`/api/contacts/${id}`, {
      method: 'PUT',
      body: JSON.stringify(payload),
    })
  },

  deleteContact(id: number) {
    return request<null>(`/api/contacts/${id}`, { method: 'DELETE' })
  },

  reorderContacts(pageId: number, ids: number[]) {
    return request<null>(`/api/dial-pages/${pageId}/contacts/reorder`, {
      method: 'PUT',
      body: JSON.stringify({ ids }),
    })
  },

  upload(file: File) {
    const fd = new FormData()
    fd.append('file', file)
    return request<UploadPayload>('/api/upload', { method: 'POST', body: fd })
  },

  getPublicPage(slug: string) {
    return request<DialPage>(`/api/public/dial-page/${slug}`)
  },
}
