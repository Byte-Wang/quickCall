<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { api } from '@/lib/api'
import type { Contact, DialPage } from '@/types'
import { toast } from '@/composables/useToast'
import { apiUrl } from '@/lib/config'
import AvatarBadge from '@/components/AvatarBadge.vue'
import ContactFormModal from '@/components/ContactFormModal.vue'
import ShareModal from '@/components/ShareModal.vue'
import {
  ArrowLeft,
  Pencil,
  Plus,
  Share2,
  Trash2,
  Upload,
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()

const pageId = Number(route.params.id)
const page = ref<DialPage | null>(null)
const loading = ref(true)

const form = ref({
  name: '',
  bg_type: 'color' as 'color' | 'image',
  bg_color: '#0f172a',
  bg_image: '',
  font_size: 20,
})

const showContactModal = ref(false)
const editingContact = ref<Contact | null>(null)
const showShare = ref(false)
const shareContact = ref<Contact | null>(null)
const bgInput = ref<HTMLInputElement | null>(null)
const saving = ref(false)

async function load() {
  loading.value = true
  try {
    page.value = await api.getPage(pageId)
    form.value = {
      name: page.value.name,
      bg_type: page.value.bg_type,
      bg_color: page.value.bg_color,
      bg_image: page.value.bg_image,
      font_size: page.value.font_size,
    }
  } catch (e) {
    toast((e as Error).message, 'error')
  } finally {
    loading.value = false
  }
}

async function savePage() {
  saving.value = true
  try {
    page.value = await api.updatePage(pageId, { ...form.value })
    toast('已保存', 'success')
  } catch (e) {
    toast((e as Error).message, 'error')
  } finally {
    saving.value = false
  }
}

function openAdd() {
  editingContact.value = null
  showContactModal.value = true
}

function openEdit(contact: Contact) {
  editingContact.value = contact
  showContactModal.value = true
}

async function onSaveContact(payload: {
  name: string
  phone: string
  avatar: string
  bg_color: string
  font_size: number | null
}) {
  try {
    if (editingContact.value) {
      await api.updateContact(editingContact.value.id, payload)
      toast('号码已更新', 'success')
    } else {
      await api.addContact(pageId, payload)
      toast('号码已添加', 'success')
    }
    showContactModal.value = false
    await load()
  } catch (e) {
    toast((e as Error).message, 'error')
  }
}

async function removeContact(contact: Contact) {
  if (!window.confirm(`确定删除「${contact.name}」吗？`)) return

  try {
    await api.deleteContact(contact.id)
    toast('已删除', 'success')
    await load()
  } catch (e) {
    toast((e as Error).message, 'error')
  }
}

async function onPickBg(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file) return

  try {
    const data = await api.upload(file)
    form.value.bg_image = data.url
    form.value.bg_type = 'image'
    toast('背景图上传成功', 'success')
  } catch (err) {
    toast((err as Error).message, 'error')
  } finally {
    input.value = ''
  }
}

onMounted(load)
</script>

<template>
  <div class="min-h-screen">
    <header class="sticky top-0 z-30 bg-paper/80 backdrop-blur-md border-b border-line">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <button
            type="button"
            class="w-9 h-9 grid place-items-center rounded-xl text-muted hover:bg-surface hover:text-ink transition-colors"
            @click="router.push('/admin')"
          >
            <ArrowLeft class="w-5 h-5" />
          </button>
          <span class="font-semibold">编辑拨号页</span>
        </div>

        <div class="flex items-center gap-2">
          <button
            type="button"
            class="flex items-center gap-1.5 rounded-xl border border-line px-3.5 py-2 text-sm font-medium hover:bg-surface transition-colors"
            @click="showShare = true"
          >
            <Share2 class="w-4 h-4" />
            分享
          </button>
          <button
            type="button"
            class="rounded-xl bg-accent text-white px-4 py-2 text-sm font-semibold hover:bg-accent-dark transition-colors disabled:opacity-60"
            :disabled="saving"
            @click="savePage"
          >
            {{ saving ? '保存中…' : '保存' }}
          </button>
        </div>
      </div>
    </header>

    <main v-if="page" class="max-w-5xl mx-auto px-4 sm:px-6 py-8 grid lg:grid-cols-[1fr_320px] gap-6">
      <!-- 号码管理 -->
      <section class="order-2 lg:order-1">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-semibold text-lg">号码</h2>
          <button
            type="button"
            class="flex items-center gap-1.5 rounded-xl bg-ink text-white px-3.5 py-2 text-sm font-medium hover:bg-black transition-colors"
            @click="openAdd"
          >
            <Plus class="w-4 h-4" />
            添加号码
          </button>
        </div>

        <div v-if="loading" class="space-y-3">
          <div v-for="i in 3" :key="i" class="h-20 rounded-2xl bg-surface animate-pulse"></div>
        </div>

        <div v-else-if="!page.contacts?.length" class="rounded-2xl bg-surface border border-dashed border-line py-16 text-center">
          <p class="text-muted text-sm">还没有号码，点击「添加号码」开始</p>
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="contact in page.contacts"
            :key="contact.id"
            class="flex items-center gap-4 rounded-2xl bg-surface shadow-card p-4"
          >
            <AvatarBadge
              :name="contact.name"
              :phone="contact.phone"
              :avatar="contact.avatar"
              :size="48"
              :font-size="16"
            />

            <div class="flex-1 min-w-0">
              <p class="font-semibold truncate">{{ contact.name }}</p>
              <p class="text-sm text-muted truncate">{{ contact.phone }}</p>
            </div>

            <div class="flex items-center gap-1">
              <button
                type="button"
                class="w-9 h-9 grid place-items-center rounded-lg text-muted hover:bg-paper hover:text-ink transition-colors"
                title="分享单号码"
                @click="shareContact = contact"
              >
                <Share2 class="w-4 h-4" />
              </button>
              <button
                type="button"
                class="w-9 h-9 grid place-items-center rounded-lg text-muted hover:bg-paper hover:text-ink transition-colors"
                title="编辑"
                @click="openEdit(contact)"
              >
                <Pencil class="w-4 h-4" />
              </button>
              <button
                type="button"
                class="w-9 h-9 grid place-items-center rounded-lg text-muted hover:bg-accent/10 hover:text-accent transition-colors"
                title="删除"
                @click="removeContact(contact)"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- 基本信息 -->
      <section class="order-1 lg:order-2">
        <div class="rounded-2xl bg-surface shadow-card p-5 grid gap-5">
          <h2 class="font-semibold text-lg">页面设置</h2>

          <label class="grid gap-1.5">
            <span class="text-sm text-muted">页面名称</span>
            <input
              v-model="form.name"
              type="text"
              class="rounded-xl border border-line px-3 py-2.5 text-sm outline-none focus:border-accent focus:ring-2 focus:ring-accent/20 transition"
            />
          </label>

          <div class="grid gap-2">
            <span class="text-sm text-muted">页面背景</span>
            <div class="grid grid-cols-2 gap-2">
              <button
                type="button"
                class="rounded-xl border px-3 py-2.5 text-sm font-medium transition-colors"
                :class="form.bg_type === 'color' ? 'border-accent text-accent bg-accent/5' : 'border-line text-muted hover:bg-paper'"
                @click="form.bg_type = 'color'"
              >
                纯色
              </button>
              <button
                type="button"
                class="rounded-xl border px-3 py-2.5 text-sm font-medium transition-colors"
                :class="form.bg_type === 'image' ? 'border-accent text-accent bg-accent/5' : 'border-line text-muted hover:bg-paper'"
                @click="form.bg_type = 'image'"
              >
                图片
              </button>
            </div>

            <label v-if="form.bg_type === 'color'" class="flex items-center gap-3 mt-1">
              <input
                v-model="form.bg_color"
                type="color"
                class="w-14 h-10 rounded-lg border border-line cursor-pointer bg-transparent"
              />
              <span class="text-sm text-muted">{{ form.bg_color }}</span>
            </label>

            <div v-else class="mt-1 grid gap-2">
              <input
                ref="bgInput"
                type="file"
                accept="image/jpeg,image/png,image/webp,image/gif"
                class="hidden"
                @change="onPickBg"
              />
              <div
                v-if="form.bg_image"
                class="h-24 rounded-xl bg-cover bg-center border border-line"
                :style="{ backgroundImage: `url(${apiUrl(form.bg_image)})` }"
              ></div>
              <button
                type="button"
                class="flex items-center justify-center gap-2 rounded-xl border border-dashed border-line py-3 text-sm text-muted hover:bg-paper transition-colors"
                @click="bgInput?.click()"
              >
                <Upload class="w-4 h-4" />
                {{ form.bg_image ? '更换背景图' : '上传背景图' }}
              </button>
            </div>
          </div>

          <div class="grid gap-2">
            <div class="flex items-center justify-between">
              <span class="text-sm text-muted">默认字号</span>
              <span class="text-sm font-semibold">{{ form.font_size }}px</span>
            </div>
            <input
              v-model.number="form.font_size"
              type="range"
              min="12"
              max="48"
              step="1"
              class="w-full accent-[var(--c-accent)]"
            />
            <p class="text-xs text-muted">字号会影响号码名称大小与每行列数</p>
          </div>
        </div>
      </section>
    </main>

    <div v-else-if="loading" class="py-24 text-center text-muted">加载中…</div>

    <ContactFormModal
      v-if="showContactModal"
      :contact="editingContact"
      @close="showContactModal = false"
      @save="onSaveContact"
    />

    <ShareModal v-if="showShare && page" :page="page" @close="showShare = false" />

    <ShareModal
      v-if="shareContact && page"
      :page="page"
      :contact-id="shareContact.id"
      @close="shareContact = null"
    />
  </div>
</template>
