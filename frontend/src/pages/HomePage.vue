<script setup lang="ts">
import { useRouter } from 'vue-router'
import { getToken } from '@/lib/http'
import {
  ArrowRight,
  Check,
  Heart,
  PhoneCall,
  QrCode,
  ShieldCheck,
  Smartphone,
  Users,
  Volume2,
} from 'lucide-vue-next'

const router = useRouter()

// 若已登录进入管理页，否则进入登录页（登录后再跳转到管理页）
function start() {
  if (getToken()) {
    router.push('/admin')
  } else {
    router.push('/login')
  }
}

const features = [
  {
    icon: Users,
    title: '大字头像，一眼认出',
    desc: '每个常用联系人用大大的头像和名字展示，不用认字也能看懂是谁。',
  },
  {
    icon: PhoneCall,
    title: '点开头像，直接打电话',
    desc: '长辈只要点一下头像，手机立即拨出电话，没有任何多余步骤。',
  },
  {
    icon: QrCode,
    title: '链接 + 二维码，随时分享',
    desc: '子女把拨号页做成链接或二维码发到老人手机，扫一下或点开即用。',
  },
  {
    icon: Volume2,
    title: '语音点名，听声音找人',
    desc: '支持为每个联系人配上声音，看不清字也能听名字找对人。',
  },
  {
    icon: Heart,
    title: '为长辈量身打造',
    desc: '专为不识字、用不惯智能手机的老人设计，界面简单不复杂。',
  },
  {
    icon: ShieldCheck,
    title: '无需安装，打开即用',
    desc: '不用下载 App，微信里点开链接就能用，不给长辈添麻烦。',
  },
]

const steps = [
  {
    title: '子女注册并创建拨号页',
    desc: '用手机号注册，创建一张专属于爸妈的拨号通讯录。',
  },
  {
    title: '添加联系人头像与号码',
    desc: '把常用的亲人号码放进去，配上清晰的头像或大头照片。',
  },
  {
    title: '长辈点开就能拨打电话',
    desc: '把链接或二维码发给老人，点开看到头像，点一下就打通电话。',
  },
]

const faqs = [
  {
    q: '老人不认识字，能自己打电话吗？',
    a: '可以。快拨通讯录用大号头像代替文字，老人只要记住亲人的样子，点一下头像就能拨出电话，全程不需要识字。',
  },
  {
    q: '需要给老人安装软件吗？',
    a: '不需要。拨号页是一个网页链接，子女设置好后把链接或二维码发到老人手机，微信里点开就能使用。',
  },
  {
    q: '怎么帮爸妈设置？',
    a: '子女用手机号注册后，创建拨号页，添加常用联系人的头像和电话，再把生成的链接或二维码保存到老人手机桌面即可。',
  },
  {
    q: '会不会误拨电话？',
    a: '可以控制号码是否显示、字号大小，也可以为每个号码单独生成链接，尽量减少误触和误拨的可能。',
  },
]
</script>

<template>
  <div class="min-h-screen">
    <!-- 顶部导航 -->
    <header
      class="sticky top-0 z-30 bg-paper/80 backdrop-blur-md border-b border-line"
    >
      <div
        class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between"
      >
        <div class="flex items-center gap-2.5">
          <img
            src="/icon.png"
            alt="快拨通讯录"
            class="w-9 h-9 rounded-xl object-cover"
          />
          <span class="font-semibold text-lg">快拨通讯录</span>
        </div>

        <div class="flex items-center gap-3">
          <a
            href="#/login"
            class="hidden sm:inline-flex items-center rounded-xl px-4 py-2 text-sm text-muted hover:text-ink transition-colors"
          >
            登录
          </a>
          <button
            type="button"
            class="inline-flex items-center gap-1.5 rounded-xl bg-accent text-white px-4 py-2 text-sm font-semibold hover:bg-accent-dark transition-colors"
            @click="start"
          >
            开始使用
            <ArrowRight class="w-4 h-4" />
          </button>
        </div>
      </div>
    </header>

    <main>
      <!-- Hero -->
      <section class="relative overflow-hidden">
        <div
          class="absolute -right-24 -top-24 w-96 h-96 rounded-full bg-accent/10 blur-3xl"
        ></div>
        <div class="absolute -left-24 bottom-0 w-72 h-72 rounded-full bg-accent/5"></div>

        <div
          class="relative max-w-6xl mx-auto px-4 sm:px-6 py-16 sm:py-24 grid lg:grid-cols-2 gap-12 items-center"
        >
          <div class="animate-rise">
            <span
              class="inline-flex items-center gap-1.5 rounded-full bg-accent/10 text-accent text-sm font-medium px-3 py-1"
            >
              <Heart class="w-4 h-4" />
              专为不识字长辈设计
            </span>

            <h1
              class="mt-5 text-3xl sm:text-5xl font-bold leading-tight tracking-tight"
            >
              让不识字的爸妈，<br class="hidden sm:block" />也能自己打电话
            </h1>

            <p class="mt-5 text-muted text-lg leading-relaxed max-w-md">
              快拨通讯录把亲人的电话做成大字头像，长辈点一下头像就能直接拨出电话，
              不用认字、不用找号码，解决老人用智能手机的难题。
            </p>

            <div class="mt-8 flex flex-col sm:flex-row gap-3">
              <button
                type="button"
                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-accent text-white px-7 py-4 text-base font-semibold shadow-card hover:bg-accent-dark hover:shadow-pop transition-all"
                @click="start"
              >
                <PhoneCall class="w-5 h-5" />
                开始使用，免费创建
              </button>
              <a
                href="#features"
                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-line bg-surface px-7 py-4 text-base font-medium hover:bg-paper transition-colors"
              >
                了解怎么用
              </a>
            </div>

            <p class="mt-4 text-sm text-muted">无需下载 App · 打开即用 · 永久免费</p>
          </div>

          <!-- 手机拨号页示意 -->
          <div class="hidden lg:flex justify-center animate-rise">
            <div class="relative w-[300px] rounded-[2.5rem] bg-ink p-3 shadow-pop">
              <div class="rounded-[2rem] bg-paper overflow-hidden">
                <div class="px-5 py-6 text-center">
                  <p class="text-sm font-semibold text-ink">爸爸妈妈的通讯录</p>
                  <p class="mt-1 text-xs text-muted">点头像打电话</p>
                </div>
                <div class="px-5 pb-7 grid grid-cols-2 gap-4">
                  <div
                    v-for="(m, i) in [
                      { name: '儿子', bg: '#ff5a36' },
                      { name: '女儿', bg: '#3b82f6' },
                      { name: '老伴', bg: '#10b981' },
                      { name: '医生', bg: '#f59e0b' },
                    ]"
                    :key="i"
                    class="flex flex-col items-center gap-2"
                  >
                    <div
                      class="w-20 h-20 rounded-full grid place-items-center text-white text-xl font-semibold"
                      :style="{ backgroundColor: m.bg }"
                    >
                      {{ m.name[0] }}
                    </div>
                    <span class="text-sm text-ink">{{ m.name }}</span>
                  </div>
                </div>
              </div>

              <div
                class="absolute -right-4 bottom-10 bg-white rounded-2xl px-4 py-3 shadow-pop flex items-center gap-2"
              >
                <div class="w-8 h-8 rounded-full bg-accent grid place-items-center text-white">
                  <PhoneCall class="w-4 h-4" />
                </div>
                <div>
                  <p class="text-xs font-semibold text-ink">正在拨打…</p>
                  <p class="text-[10px] text-muted">点一下就打出去了</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- 痛点 -->
      <section class="max-w-6xl mx-auto px-4 sm:px-6 py-10 sm:py-16">
        <div class="text-center max-w-2xl mx-auto">
          <h2 class="text-2xl sm:text-3xl font-bold">老人用手机打电话，其实很难</h2>
          <p class="mt-3 text-muted leading-relaxed">
            找联系人要翻半天、拨号盘字太小、看不懂界面……快拨通讯录把这些都简化成一个动作。
          </p>
        </div>

        <div class="mt-10 grid sm:grid-cols-3 gap-5">
          <div
            v-for="(f, i) in [
              { t: '找不到联系人', d: '通讯录里人太多，老人翻来翻去找半天。' },
              { t: '字太小看不清', d: '拨号盘和名字的字号对老人很不友好。' },
              { t: '操作太复杂', d: '菜单、广告、弹窗，长辈一不小心就点错。' },
            ]"
            :key="i"
            class="p-6 rounded-2xl bg-surface shadow-card"
          >
            <PhoneCall class="w-5 h-5 text-accent" />
            <h3 class="mt-4 font-semibold text-lg">{{ f.t }}</h3>
            <p class="mt-2 text-sm text-muted leading-relaxed">{{ f.d }}</p>
          </div>
        </div>
      </section>

      <!-- 功能特点 -->
      <section id="features" class="bg-surface/60 border-y border-line">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16 sm:py-20">
          <div class="text-center max-w-2xl mx-auto">
            <h2 class="text-2xl sm:text-3xl font-bold">一键拨号，专为老人设计</h2>
            <p class="mt-3 text-muted leading-relaxed">
              用最简单的方式，让长辈随时联系到想联系的人。
            </p>
          </div>

          <div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div
              v-for="f in features"
              :key="f.title"
              class="p-6 rounded-2xl bg-surface shadow-card hover:shadow-pop transition-shadow"
            >
              <div
                class="w-11 h-11 rounded-xl bg-accent/10 text-accent grid place-items-center"
              >
                <component :is="f.icon" class="w-5 h-5" />
              </div>
              <h3 class="mt-4 font-semibold text-lg">{{ f.title }}</h3>
              <p class="mt-2 text-sm text-muted leading-relaxed">{{ f.desc }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- 使用步骤 -->
      <section class="max-w-6xl mx-auto px-4 sm:px-6 py-16 sm:py-20">
        <div class="text-center max-w-2xl mx-auto">
          <h2 class="text-2xl sm:text-3xl font-bold">三步就能帮爸妈用起来</h2>
          <p class="mt-3 text-muted leading-relaxed">
            子女花几分钟设置好，长辈以后打电话就再也不求人。
          </p>
        </div>

        <div class="mt-12 grid sm:grid-cols-3 gap-5">
          <div
            v-for="(s, i) in steps"
            :key="s.title"
            class="relative p-6 rounded-2xl bg-surface shadow-card"
          >
            <span
              class="w-9 h-9 rounded-full bg-accent text-white grid place-items-center font-bold"
            >
              {{ i + 1 }}
            </span>
            <h3 class="mt-4 font-semibold text-lg">{{ s.title }}</h3>
            <p class="mt-2 text-sm text-muted leading-relaxed">{{ s.desc }}</p>
          </div>
        </div>

        <div class="mt-12 text-center">
          <button
            type="button"
            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-accent text-white px-8 py-4 text-base font-semibold shadow-card hover:bg-accent-dark hover:shadow-pop transition-all"
            @click="start"
          >
            <Smartphone class="w-5 h-5" />
            开始使用，帮爸妈设置
          </button>
        </div>
      </section>

      <!-- 常见问题 -->
      <section class="bg-surface/60 border-y border-line">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 py-16 sm:py-20">
          <h2 class="text-2xl sm:text-3xl font-bold text-center">常见问题</h2>

          <div class="mt-10 grid gap-4">
            <div
              v-for="f in faqs"
              :key="f.q"
              class="p-5 rounded-2xl bg-surface shadow-card"
            >
              <h3 class="font-semibold flex items-start gap-2">
                <Check class="w-5 h-5 text-accent shrink-0 mt-0.5" />
                {{ f.q }}
              </h3>
              <p class="mt-2 text-sm text-muted leading-relaxed pl-7">{{ f.a }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- 底部 CTA -->
      <section class="max-w-6xl mx-auto px-4 sm:px-6 py-16 sm:py-20 text-center">
        <div class="max-w-2xl mx-auto">
          <h2 class="text-2xl sm:text-3xl font-bold">
            现在就开始，给爸妈一个更简单的电话
          </h2>
          <p class="mt-3 text-muted leading-relaxed">
            创建一张长辈专属的拨号通讯录，点开头像就能打电话。
          </p>
          <button
            type="button"
            class="mt-8 inline-flex items-center justify-center gap-2 rounded-2xl bg-accent text-white px-8 py-4 text-lg font-semibold shadow-card hover:bg-accent-dark hover:shadow-pop transition-all"
            @click="start"
          >
            免费开始使用
            <ArrowRight class="w-5 h-5" />
          </button>
        </div>
      </section>
    </main>

    <!-- 页脚 -->
    <footer class="border-t border-line">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
          <div class="flex items-center gap-2.5">
            <img src="/icon.png" alt="快拨通讯录" class="w-8 h-8 rounded-lg object-cover" />
            <span class="font-semibold">快拨通讯录</span>
          </div>
          <p class="text-sm text-muted text-center">
            专为不识字长辈设计的在线一键拨号通讯录，点开头像即可打电话。
          </p>
          <div class="text-sm text-muted">免费 · 无需安装</div>
        </div>
        <p class="mt-6 text-xs text-muted text-center">
          快拨通讯录 · 老人一键拨号 · 长辈电话簿 · 点开头像打电话
        </p>
      </div>
    </footer>
  </div>
</template>