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
    <AppNavbar v-if="usesSharedLayout" />
    <router-view />
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
</style>
