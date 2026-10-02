/// <reference types="vite/client" />

declare module 'vconsole' {
  export interface VConsoleOptions {
    defaultPlugins?: string[]
    theme?: string
    target?: string
    maxLogNumber?: number
    disableLogScrolling?: boolean
    onReady?: () => void
    onClearLog?: () => void
  }

  export default class VConsole {
    constructor(options?: VConsoleOptions)
    destroy(): void
    show(): void
    hide(): void
    showSwitch(): void
    hideSwitch(): void
  }
}
