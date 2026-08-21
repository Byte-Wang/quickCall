<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '@/lib/api'
import type { Contact, DialPage } from '@/types'
import { avatarSizeFor, columnCountFor, readableTextColor } from '@/lib/avatar'
import { apiUrl } from '@/lib/config'
import AvatarBadge from '@/components/AvatarBadge.vue'
import { Phone } from 'lucide-vue-next'

const route = useRoute()
const page = ref<DialPage | null>(null)
const loading = ref(true)
const error = ref('')
const dialing = ref<Contact | null>(null)

const containerRef = ref<HTMLElement | null>(null)
const containerWidth = ref(0)
let observer: ResizeObserver | null = null

const slug = computed(() => String(route.params.slug ?? ''))
const contactId = computed(() => {
  const id = Number(route.params.contactId)
  return Number.isFinite(id) && id > 0 ? id : null
})

const columns = computed(() =>
  columnCountFor(containerWidth.value, page.value?.font_size ?? 20),
)

const isSingleColumn = computed(() => columns.value === 1)

const bgStyle = computed(() => {
  const p = page.value
  if (!p) return {}
  if (p.bg_type === 'image' && p.bg_image) {
    return {
      backgroundImage: `url(${apiUrl(p.bg_image)})`,
      backgroundSize: 'cover',
      backgroundPosition: 'center',
    }
  }
  return { backgroundColor: p.bg_color || '#0f172a' }
})

const textColor = computed(() => {
  const p = page.value
  if (!p) return '#201a17'
  if (p.bg_type === 'image') return '#ffffff'
  return readableTextColor(p.bg_color)
})

const needsShadow = computed(() => textColor.value === '#ffffff')

function effectiveFont(c: Contact): number {
  return c.font_size ?? page.value?.font_size ?? 20
}

function contactTextColor(c: Contact): string {
  return c.bg_color ? readableTextColor(c.bg_color) : textColor.value
}

function dial(c: Contact) {
  window.location.href = `tel:${c.phone}`
}

function setIconLink(rel: string, href: string) {
  let link = document.querySelector<HTMLLinkElement>(`link[rel="${rel}"]`)
  if (!href) {
    link?.remove()
    return
  }
  if (!link) {
    link = document.createElement('link')
    link.rel = rel
    document.head.appendChild(link)
  }
  link.href = href
  link.removeAttribute('type')
}

function setMetaName(name: string, content: string) {
  let meta = document.querySelector<HTMLMetaElement>(`meta[name="${name}"]`)
  if (!meta) {
    meta = document.createElement('meta')
    meta.name = name
    document.head.appendChild(meta)
  }
  meta.content = content
}

function applyTitle(name: string) {
  const title = name?.trim() || '快拨通讯录'
  document.title = title
  setMetaName('apple-mobile-web-app-title', title)
}

// 将页面图标与标题设置为对应号码的头像与名字（用于添加到主屏幕）
function applyContactMeta(contact: Contact | null) {
  const href = contact?.avatar ? apiUrl(contact.avatar) : ''
  setIconLink('icon', href)
  setIconLink('apple-touch-icon', href)
  applyTitle(contact?.name ?? '')
}

// 普通拨号页：标题用拨号页名称，图标使用默认 icon.png
function applyPageMeta(name: string) {
  applyTitle(name)
  setIconLink('icon', '/icon.png')
  setIconLink('apple-touch-icon', '/icon.png')
}

async function load() {
  loading.value = true
  error.value = ''
  try {
    page.value = await api.getPublicPage(slug.value)

    const cid = contactId.value
    const target = cid != null ? page.value.contacts?.find((c) => c.id === cid) : null

    if (target) {
      applyContactMeta(target)
      dialing.value = target
      setTimeout(() => dial(target), 500)
    } else {
      applyPageMeta(page.value.name)
    }
  } catch (e) {
    error.value = (e as Error).message
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  load()

  if (containerRef.value) {
    observer = new ResizeObserver((entries) => {
      const entry = entries[0]
      if (entry) containerWidth.value = entry.contentRect.width
    })
    observer.observe(containerRef.value)
  }
})

onUnmounted(() => {
  observer?.disconnect()
})
</script>

<template>
  <div ref="containerRef" class="min-h-screen" :style="bgStyle">
    <div v-if="loading" class="min-h-screen grid place-items-center">
      <p class="text-white/80 text-sm">加载中…</p>
    </div>

    <div v-else-if="error" class="min-h-screen grid place-items-center px-6">
      <div class="bg-white/95 text-ink rounded-2xl px-6 py-8 text-center max-w-sm shadow-pop">
        <p class="font-semibold">无法打开拨号页</p>
        <p class="mt-2 text-sm text-muted">{{ error }}</p>
      </div>
    </div>

    <div
      v-else-if="page"
      class="mx-auto max-w-5xl px-5 py-10 sm:py-14"
      :class="{ 'text-shadow-soft': needsShadow }"
    >
      <header class="text-center mb-10">
        <h1 class="text-2xl sm:text-3xl font-bold" :style="{ color: textColor }">
          {{ page.name }}
        </h1>
        <p class="mt-2 text-sm opacity-70" :style="{ color: textColor }">
          点击头像即可拨号
        </p>
      </header>

      <div
        class="grid gap-4 sm:gap-6"
        :style="{ gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))` }"
      >
        <button
          v-for="contact in page.contacts"
          :key="contact.id"
          type="button"
          class="flex rounded-2xl transition-transform active:scale-95"
          :class="[
            isSingleColumn
              ? 'flex-row items-center gap-4 px-5 py-4'
              : 'flex-col items-center justify-center gap-3 py-6 px-2',
            contact.bg_color
              ? ''
              : textColor === '#ffffff'
                ? 'hover:bg-white/10'
                : 'hover:bg-ink/5',
          ]"
          :style="contact.bg_color ? { backgroundColor: contact.bg_color } : {}"
          @click="dial(contact)"
        >
          <AvatarBadge
            :name="contact.name"
            :phone="contact.phone"
            :avatar="contact.avatar"
            :size="avatarSizeFor(effectiveFont(contact))"
            :font-size="Math.round(avatarSizeFor(effectiveFont(contact)) * 0.4)"
          />

          <div
            class="flex flex-col min-w-0"
            :class="isSingleColumn ? 'items-start' : 'items-center'"
          >
            <span
              class="font-semibold leading-tight"
              :style="{
                color: contactTextColor(contact),
                fontSize: `${effectiveFont(contact)}px`,
              }"
            >
              {{ contact.name }}
            </span>
            <span
              class="mt-1 flex items-center gap-1 opacity-70"
              :style="{
                color: contactTextColor(contact),
                fontSize: `${Math.max(12, Math.round(effectiveFont(contact) * 0.6))}px`,
              }"
            >
              <Phone class="w-3 h-3" />
              {{ contact.phone }}
            </span>
          </div>
        </button>
      </div>

      <div
        v-if="!page.contacts?.length"
        class="text-center py-20 text-sm opacity-70"
        :style="{ color: textColor }"
      >
        该拨号页还没有号码
      </div>
    </div>

    <div v-if="dialing" class="fixed inset-0 z-50 grid place-items-center bg-black/50">
      <div class="bg-white rounded-2xl px-8 py-6 text-center animate-pop">
        <AvatarBadge
          :name="dialing.name"
          :phone="dialing.phone"
          :avatar="dialing.avatar"
          :size="64"
          :font-size="22"
        />
        <p class="mt-3 font-semibold">正在拨打 {{ dialing.name }}</p>
        <p class="mt-1 text-sm text-muted">{{ dialing.phone }}</p>
      </div>
    </div>
  </div>
</template>
