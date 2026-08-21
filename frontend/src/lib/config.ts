// 后端 API 入口地址。
// - 生产环境（如宝塔默认站点，后端代码放在站点根目录的 backend 目录下）：
//   使用 `/backend/public/index.php?r=` 方式转发路由，无需额外配置服务器反向代理。
// - 开发环境：留空，走 vite.config.ts 的 /api、/uploads 代理。
// 也可通过环境变量 VITE_API_BASE 覆盖。
const envBase = import.meta.env.VITE_API_BASE as string | undefined

export const API_BASE: string =
  envBase ?? (import.meta.env.PROD ? '/backend/public/index.php' : '')

/** 把后端返回的相对路径（/api/...、/uploads/...）转成可直接请求的地址 */
export function apiUrl(path: string): string {
  if (API_BASE) {
    return `${API_BASE}?r=${encodeURIComponent(path)}`
  }
  return path
}
