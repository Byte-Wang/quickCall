/** 复制文本到剪贴板。
 *  优先使用异步 Clipboard API（仅 HTTPS / localhost 可用）；
 *  在 HTTP 等非安全上下文下降级为 document.execCommand('copy')。
 */
export async function copyText(text: string): Promise<void> {
  if (navigator.clipboard && window.isSecureContext) {
    await navigator.clipboard.writeText(text)
    return
  }

  const ta = document.createElement('textarea')
  ta.value = text
  ta.setAttribute('readonly', '')
  ta.style.position = 'fixed'
  ta.style.top = '-9999px'
  ta.style.opacity = '0'
  document.body.appendChild(ta)
  ta.select()
  ta.setSelectionRange(0, text.length)

  let ok = false
  try {
    ok = document.execCommand('copy')
  } finally {
    document.body.removeChild(ta)
  }

  if (!ok) {
    throw new Error('复制失败')
  }
}
