// 依据手机号计算一个稳定的头像背景色（HSL）
export function phoneToColor(phone: string): string {
  let hash = 0
  for (let i = 0; i < phone.length; i++) {
    hash = (hash << 5) - hash + phone.charCodeAt(i)
    hash |= 0
  }
  const hue = Math.abs(hash) % 360
  return `hsl(${hue}, 58%, 52%)`
}

// 取名称最后 2 个字作为头像兜底文字
export function nameInitials(name: string): string {
  const trimmed = name.trim()
  if (!trimmed) return '·'
  return trimmed.slice(-2)
}

// 根据背景色亮度决定文字颜色，保证对比度
export function readableTextColor(bg: string): string {
  if (!bg) return '#ffffff'
  if (bg.startsWith('hsl')) {
    return '#ffffff'
  }

  const hex = bg.replace('#', '')
  if (!/^[0-9a-fA-F]{6}$/.test(hex)) return '#ffffff'

  const r = parseInt(hex.slice(0, 2), 16)
  const g = parseInt(hex.slice(2, 4), 16)
  const b = parseInt(hex.slice(4, 6), 16)
  const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255
  return luminance > 0.6 ? '#201a17' : '#ffffff'
}

// 根据字号计算头像尺寸（px）
export function avatarSizeFor(fontSize: number): number {
  return Math.round(Math.min(120, Math.max(44, fontSize * 2.4)))
}

// 根据容器宽度与字号计算列数（1~3 列）
export function columnCountFor(width: number, fontSize: number): number {
  if (width <= 0) return 1
  const minColumn = fontSize * 5.6
  const cols = Math.floor(width / minColumn)
  return Math.max(1, Math.min(3, cols))
}
