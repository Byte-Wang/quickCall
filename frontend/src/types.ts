export interface User {
  id: number
  phone: string
}

export interface Contact {
  id: number
  dial_page_id?: number
  name: string
  phone: string
  avatar: string
  bg_color: string
  font_size: number | null
  sort_order?: number
}

export interface DialPage {
  id: number
  user_id?: number
  name: string
  slug: string
  bg_type: 'color' | 'image'
  bg_color: string
  bg_image: string
  font_size: number
  show_name?: boolean
  avatar_size?: number
  phone_size?: number
  contact_count?: number
  contacts?: Contact[]
}

export interface AuthPayload {
  token: string
  user: User
}

export interface UploadPayload {
  url: string
}
