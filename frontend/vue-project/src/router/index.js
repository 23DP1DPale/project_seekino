import { createRouter, createWebHistory } from 'vue-router'
import Home from '../views/home.vue'
import Filmas from '@/views/filmas.vue'
import Seansi from '@/views/seansi.vue'
import Kontakti from '@/views/kontakti.vue'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home,
  },
  {
    path: '/filmas',
    name: 'Filmas',
    component: Filmas,
  },
  {
    path: '/seansi',
    name: 'Seansi',
    component: Seansi
  },
  {
    path: '/kontakti',
    name: "Kontakti",
    component: Kontakti,
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
