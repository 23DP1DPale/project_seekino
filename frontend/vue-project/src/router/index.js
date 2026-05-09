import { createRouter, createWebHistory } from 'vue-router'
import Home from '../views/home.vue'
import Filmas from '@/views/filmas.vue'
import FilmasDetalizeti from '@/views/filmas-detalizeti.vue'
import Reservation from '@/views/reservation.vue'
import Seansi from '@/views/seansi.vue'
import Kontakti from '@/views/kontakti.vue'
import Login from '@/views/login.vue'
import Register from '@/views/register.vue'

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
    path: '/filmas/:id',
    name: 'FilmasDetalizeti',
    component: FilmasDetalizeti,
  },
  {
    path: '/reservation/:screeningId',
    name: 'Reservation',
    component: Reservation,
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
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})

export default router
