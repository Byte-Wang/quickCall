// 头像本地缓存：用 IndexedDB 存储图片 Blob。
// 分享页二次打开时优先命中本地缓存，避免重复下载头像。
// 说明：浏览器 Cache API / Service Worker 在非 HTTPS 环境下不可用，因此这里用 IndexedDB。

const DB_NAME = 'quickdial_cache'
const STORE = 'images'

let dbPromise: Promise<IDBDatabase> | null = null

function openDb(): Promise<IDBDatabase> {
  if (dbPromise) return dbPromise
  dbPromise = new Promise((resolve, reject) => {
    const req = indexedDB.open(DB_NAME, 1)
    req.onupgradeneeded = () => {
      const db = req.result
      if (!db.objectStoreNames.contains(STORE)) {
        db.createObjectStore(STORE)
      }
    }
    req.onsuccess = () => resolve(req.result)
    req.onerror = () => reject(req.error)
  })
  return dbPromise
}

async function getBlob(key: string): Promise<Blob | null> {
  const db = await openDb()
  return new Promise((resolve, reject) => {
    const tx = db.transaction(STORE, 'readonly')
    const req = tx.objectStore(STORE).get(key)
    req.onsuccess = () => resolve((req.result as Blob) ?? null)
    req.onerror = () => reject(req.error)
  })
}

async function putBlob(key: string, blob: Blob): Promise<void> {
  const db = await openDb()
  return new Promise((resolve, reject) => {
    const tx = db.transaction(STORE, 'readwrite')
    tx.objectStore(STORE).put(blob, key)
    tx.oncomplete = () => resolve()
    tx.onerror = () => reject(tx.error)
  })
}

/** 返回可直接用于 <img src> 的地址：优先本地缓存，未命中则下载后缓存。 */
export async function cachedImageUrl(url: string): Promise<string> {
  if (!url) return ''

  try {
    const cached = await getBlob(url)
    if (cached) return URL.createObjectURL(cached)
  } catch {
    // 缓存读取失败则忽略，回退到网络加载
  }

  const res = await fetch(url)
  if (!res.ok) throw new Error('图片加载失败')
  const blob = await res.blob()

  try {
    await putBlob(url, blob)
  } catch {
    // 缓存写入失败不影响展示
  }

  return URL.createObjectURL(blob)
}
