<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import AppFooter from '@/components/AppFooter.vue'
import AppNavbar from '@/components/AppNavbar.vue'

const route = useRoute()
const usesSharedLayout = computed(() => true)
const footerWithoutTopGap = computed(() =>
  route.path === '/profile' ||
  route.path === '/kontakti' ||
  String(route.path || '').startsWith('/reservation/') ||
  String(route.path || '').startsWith('/admin/')
)
</script>

<template>
  <v-app class="seekino-app public-app-shell">
    <a class="skip-link" href="#main-content">Pāriet uz saturu</a>
    <AppNavbar v-if="usesSharedLayout" />
    <div id="main-content" tabindex="-1">
      <router-view />
    </div>
    <AppFooter v-if="usesSharedLayout" :without-top-gap="footerWithoutTopGap" />
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
</style>
