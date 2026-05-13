import { createRouter, createWebHistory } from 'vue-router'
import AdminMovies from '@/views/admin-movies.vue'
import AdminScreenings from '@/views/admin-screenings.vue'
import AdminUsers from '@/views/admin-users.vue'
import Home from '../views/home.vue'
import Filmas from '@/views/filmas.vue'
import FilmasDetalizeti from '@/views/filmas-detalizeti.vue'
import Reservation from '@/views/reservation.vue'
import Seansi from '@/views/seansi.vue'
import Kontakti from '@/views/kontakti.vue'
import Login from '@/views/login.vue'
import Profile from '@/views/profile.vue'
import Register from '@/views/register.vue'
import { useAuth } from '@/services/auth'

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
    meta: { requiresAuth: true },
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
    path: '/profile',
    name: 'Profile',
    component: Profile,
  },
  {
    path: '/admin/movies',
    name: 'AdminMovies',
    component: AdminMovies,
  },
  {
    path: '/admin/screenings',
    name: 'AdminScreenings',
    component: AdminScreenings,
  },
  {
    path: '/admin/users',
    name: 'AdminUsers',
    component: AdminUsers,
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

router.beforeEach((to) => {
  const { isAuthenticated } = useAuth()

  if (!to.meta.requiresAuth || isAuthenticated.value) {
    return true
  }

  return {
    path: '/login',
    query: { redirect: to.fullPath },
  }
})

export default router
