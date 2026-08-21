import { createRouter, createWebHashHistory, type RouteRecordRaw } from 'vue-router'
import { getToken } from '@/lib/http'
import LoginPage from '@/pages/LoginPage.vue'
import RegisterPage from '@/pages/RegisterPage.vue'
import DashboardPage from '@/pages/DashboardPage.vue'
import DialPageEditorPage from '@/pages/DialPageEditorPage.vue'
import DialViewPage from '@/pages/DialViewPage.vue'

const routes: RouteRecordRaw[] = [
  { path: '/', redirect: '/admin' },
  {
    path: '/login',
    name: 'login',
    component: LoginPage,
    meta: { guest: true },
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterPage,
    meta: { guest: true },
  },
  {
    path: '/admin',
    name: 'dashboard',
    component: DashboardPage,
    meta: { auth: true },
  },
  {
    path: '/admin/page/:id',
    name: 'editor',
    component: DialPageEditorPage,
    meta: { auth: true },
  },
  {
    path: '/d/:slug',
    name: 'dial',
    component: DialViewPage,
  },
  {
    path: '/d/:slug/:contactId',
    name: 'dial-contact',
    component: DialViewPage,
  },
]

const router = createRouter({
  history: createWebHashHistory(),
  routes,
})

router.beforeEach((to) => {
  const token = getToken()

  if (to.meta.auth && !token) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.guest && token) {
    return { name: 'dashboard' }
  }
})

export default router
