<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import AppFooter from '@/components/AppFooter.vue'
import AppNavbar from '@/components/AppNavbar.vue'
import { useAuth } from '@/services/auth'

const route = useRoute()
const { isAuthenticated } = useAuth()
const usesSharedLayout = computed(() => true)
const footerWithoutTopGap = computed(() =>
  route.path === '/profile' ||
  route.path === '/kontakti' ||
  String(route.path || '').startsWith('/reservation/') ||
  String(route.path || '').startsWith('/admin/')
)
const mobileNavItems = computed(() => [
  { title: 'Sākums', icon: 'mdi-home-variant-outline', to: '/' },
  { title: 'Filmas', icon: 'mdi-movie-open-outline', to: '/filmas' },
  { title: 'Seansi', icon: 'mdi-calendar-clock-outline', to: '/seansi' },
  {
    title: isAuthenticated.value ? 'Profils' : 'Login',
    icon: isAuthenticated.value ? 'mdi-account-outline' : 'mdi-login',
    to: isAuthenticated.value ? '/profile' : '/login',
  },
])
</script>

<template>
  <v-app class="seekino-app public-app-shell">
    <a class="skip-link" href="#main-content">Pāriet uz saturu</a>
    <AppNavbar v-if="usesSharedLayout" />
    <div id="main-content" tabindex="-1" class="main-content-shell">
      <router-view />
    </div>
    <AppFooter v-if="usesSharedLayout" :without-top-gap="footerWithoutTopGap" />

    <nav v-if="usesSharedLayout" class="mobile-bottom-nav" aria-label="Mobilā galvenā navigācija">
      <RouterLink
        v-for="item in mobileNavItems"
        :key="item.to"
        :to="item.to"
        class="mobile-bottom-link"
        :class="{ 'mobile-bottom-link--active': route.path === item.to || (item.to !== '/' && route.path.startsWith(`${item.to}/`)) }"
      >
        <v-icon :icon="item.icon" size="22" />
        <span>{{ item.title }}</span>
      </RouterLink>
    </nav>
  </v-app>
</template>

<style scoped>
.public-app-shell {
  background:
    radial-gradient(circle at 20% 15%, #243558 0%, transparent 30%),
    radial-gradient(circle at 80% 10%, #531d2c 0%, transparent 35%),
    #0a0c12;
}

.public-app-shell :deep(.v-application__wrap) {
  background: transparent;
}

.skip-link {
  position: fixed;
  top: 12px;
  left: 12px;
  z-index: 10000;
  transform: translateY(-160%);
  border-radius: 8px;
  background: #ffffff;
  color: #111111;
  font-weight: 700;
  padding: 10px 14px;
  text-decoration: none;
  transition: transform 0.15s ease;
}

.skip-link:focus {
  transform: translateY(0);
  outline: 3px solid #e50914;
  outline-offset: 3px;
}

.mobile-bottom-nav {
  position: fixed;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 1200;
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  padding: 6px 8px calc(6px + env(safe-area-inset-bottom));
  border-top: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(10, 12, 18, 0.96);
  backdrop-filter: blur(14px);
  box-shadow: 0 -12px 28px rgba(0, 0, 0, 0.34);
}

.mobile-bottom-link {
  display: flex;
  min-width: 0;
  min-height: 54px;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 3px;
  border-radius: 8px;
  color: #cfd7ea;
  font-size: 0.72rem;
  font-weight: 700;
  text-decoration: none;
}

.mobile-bottom-link span {
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.mobile-bottom-link--active {
  background: rgba(229, 9, 20, 0.16);
  color: #ffffff;
}

@media (min-width: 700px) {
  .mobile-bottom-nav {
    display: none;
  }
}

@media (max-width: 699px) {
  .public-app-shell :deep(.v-application__wrap) {
    padding-bottom: calc(70px + env(safe-area-inset-bottom));
  }
}
</style>
