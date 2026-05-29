<template>
    <v-app-bar
        color="#101114"
        flat
        location="top"
        height="76"
        scroll-behavior="elevate"
        class="sticky-app-bar app-bar-shell"
    >
        <v-container class="d-flex align-center px-2 px-md-6 app-bar-inner">
            <v-app-bar-nav-icon class="nav-btn mr-1" variant="text" @click.stop="drawer = !drawer" />

            <RouterLink to="/" class="brand-link ml-2">
                <v-img src="/img/logo_seekino.png" width="160" height="52" class="logo brand-logo" />
            </RouterLink>

            <v-spacer />

            <div class="ga-2 mr-2 nav-pages desktop-nav">
                <v-btn variant="text" class="text-none nav-link-btn" to="/filmas">Filmas</v-btn>
                <v-btn variant="text" class="text-none nav-link-btn" to="/seansi">Seansi</v-btn>
            </div>

            <v-btn
                v-if="isAuthenticated"
                icon="mdi-logout"
                variant="text"
                class="mobile-logout-btn"
                aria-label="Izrakstīties"
                :loading="navAuthLoading"
                @click="handleLogout"
            />

            <div class="desktop-actions">
                <template v-if="isAuthenticated">
                    <v-chip class="user-chip mr-2" prepend-icon="mdi-account-circle-outline" to="/profile" link>
                        {{ user?.nickname }}
                    </v-chip>
                    <v-btn
                        rounded="xl"
                        class="text-none login-btn"
                        prepend-icon="mdi-logout"
                        :loading="navAuthLoading"
                        @click="handleLogout"
                    >
                        Izrakstīties
                    </v-btn>
                </template>
                <template v-else>
                    <v-btn variant="text" class="text-none nav-link-btn" to="/register">Reģistrēties</v-btn>
                    <v-btn
                        rounded="xl"
                        class="text-none login-btn"
                        prepend-icon="mdi-account-circle-outline"
                        to="/login"
                    >
                        Pieslēgties
                    </v-btn>
                </template>
            </div>
        </v-container>
    </v-app-bar>

    <v-navigation-drawer
        v-model="drawer"
        temporary
        scrim="rgba(0, 0, 0, 0.82)"
        color="#101114"
        location="left"
        width="320"
        class="position-fixed app-drawer"
    >
        <div class="drawer-header px-4 py-5">
            <v-btn icon="mdi-close" variant="text" size="small" class="drawer-close-btn" @click="drawer = false" />
        </div>

        <v-divider />

        <v-list nav class="drawer-list px-3 py-4">
            <template v-for="group in menuGroups" :key="group.title">
                <v-list-subheader class="drawer-group-label px-2">
                    {{ group.title }}
                </v-list-subheader>
                <v-list-item
                    v-for="item in group.items"
                    :key="item.title"
                    :to="item.to || undefined"
                    :active="isMenuItemActive(item)"
                    :prepend-icon="item.icon"
                    :title="item.title"
                    link
                    rounded="lg"
                    class="drawer-list-item"
                    @click="drawer = false"
                />
                <v-divider v-if="!group.isLast" class="drawer-group-divider my-4" />
            </template>
        </v-list>

        <template v-if="isAuthenticated" #append>
            <div class="drawer-actions pa-3">
                <v-btn
                    block
                    rounded="lg"
                    class="text-none login-btn"
                    prepend-icon="mdi-logout"
                    :loading="navAuthLoading"
                    @click="handleLogout"
                >
                    Izrakstīties
                </v-btn>
            </div>
        </template>
    </v-navigation-drawer>
</template>

<script setup>
import { computed, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useAuth } from '@/services/auth'

const drawer = ref(false)
const route = useRoute()
const router = useRouter()
const { user, isAuthenticated, authLoading: navAuthLoading, logout } = useAuth()

const isAdmin = computed(() => user.value?.role === 'admin')
const profilePath = '/profile'

const menuGroups = computed(() => {
    const groups = [
        {
            title: 'Kino',
            items: [
                { title: 'Sākums', icon: 'mdi-home-variant-outline', to: '/' },
                { title: 'Filmas', icon: 'mdi-movie-open-outline', to: '/filmas' },
                { title: 'Seansi', icon: 'mdi-calendar-clock-outline', to: '/seansi' },
                { title: 'Kontakti', icon: 'mdi-phone-outline', to: '/kontakti' },
            ],
        },
        {
            title: 'Lietotājs',
            items: isAuthenticated.value
                ? [
                    { title: 'Mans profils', icon: 'mdi-account-outline', to: profilePath, activeOn: [profilePath] },
                ]
                : [
                    { title: 'Pieslēgties', icon: 'mdi-login', to: '/login' },
                    { title: 'Reģistrēties', icon: 'mdi-account-plus-outline', to: '/register' },
                ],
        },
    ]

    if (isAdmin.value) {
        groups.push({
            title: 'Administrācija',
            items: [
                { title: 'Filmu pārvaldība', icon: 'mdi-movie-edit-outline', to: '/admin/movies' },
                { title: 'Seansu pārvaldība', icon: 'mdi-calendar-edit-outline', to: '/admin/screenings' },
                { title: 'Lietotāji', icon: 'mdi-account-group-outline', to: '/admin/users' },
            ],
        })
    }

    return groups.map((group, index) => ({
        ...group,
        isLast: index === groups.length - 1,
    }))
})

const isMenuItemActive = (item) => {
    const currentPath = route.path
    const activePaths = item.activeOn || [item.to]

    return activePaths.some((path) => currentPath === path || currentPath.startsWith(`${path}/`))
}

const handleLogout = async () => {
    await logout()
    drawer.value = false
    await router.push('/')
}
</script>

<style scoped>
.sticky-app-bar { position: sticky !important; top: 0; z-index: 1100; }
.app-bar-shell { border-bottom: 1px solid rgba(255, 255, 255, 0.12); background: rgba(11, 14, 22, 0.82) !important; backdrop-filter: blur(10px); }
.app-bar-inner { min-height: 76px; }
.logo { filter: invert(1); }
.brand-link { display: inline-flex; align-items: center; text-decoration: none; width: 160px; min-width: 160px; transition: opacity 0.16s ease, transform 0.16s ease; }
.brand-link:hover { opacity: 0.92; transform: translateY(-1px); }
.brand-logo { width: 160px; flex: 0 0 160px; }
.nav-btn { color: #f4f6fb; }
.nav-link-btn { color: #d7dff2; border: 1px solid transparent; transition: color 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease; }
.nav-link-btn:hover,
.nav-link-btn.router-link-active,
.nav-link-btn.v-btn--active { color: #ffffff; background: rgba(255, 255, 255, 0.08); border-color: rgba(255, 255, 255, 0.16); transform: scale(1.03); box-shadow: 0 0 18px rgba(76, 114, 255, 0.12); }
.login-btn { margin-left: 5px; background: linear-gradient(135deg, #ff5a44, #e50914); color: #ffffff !important; font-weight: 700; letter-spacing: 0.01em; border: 1px solid rgba(255, 255, 255, 0.24); box-shadow: 0 5px 26px rgba(229, 9, 20, 0.38); transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease; }
.login-btn :deep(.v-btn__content), .login-btn :deep(.v-icon) { color: #ffffff !important; }
.login-btn:hover { transform: scale(1.03); box-shadow: 0 8px 30px rgba(229, 9, 20, 0.5), 0 0 24px rgba(108, 132, 255, 0.14); filter: brightness(1.07); }
.nav-pages,
.desktop-actions { display: none; }
.mobile-logout-btn { color: #f4f6fb; }
.user-chip { color: #f4f6fb; border: 1px solid rgba(255, 255, 255, 0.16); background: rgba(255, 255, 255, 0.08); }
.app-drawer { width: min(320px, 92vw) !important; max-width: 92vw; border-right: 1px solid rgba(255, 255, 255, 0.12); }
.app-drawer :deep(.v-navigation-drawer__content) {
    overflow-x: hidden;
    overflow-y: auto;
    padding-inline-end: 4px;
    scrollbar-color: rgba(185, 194, 214, 0.42) rgba(255, 255, 255, 0.03);
    scrollbar-gutter: stable;
    scrollbar-width: thin;
}
.app-drawer :deep(.v-navigation-drawer__content::-webkit-scrollbar) {
    width: 8px;
}
.app-drawer :deep(.v-navigation-drawer__content::-webkit-scrollbar-track) {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 999px;
}
.app-drawer :deep(.v-navigation-drawer__content::-webkit-scrollbar-thumb) {
    min-height: 42px;
    border: 2px solid rgba(16, 17, 20, 0.96);
    border-radius: 999px;
    background: rgba(185, 194, 214, 0.42);
}
.app-drawer :deep(.v-navigation-drawer__content::-webkit-scrollbar-thumb:hover) {
    background: rgba(214, 222, 240, 0.62);
}
.drawer-actions .login-btn { margin-left: 0; }
.drawer-close-btn,
.drawer-list-item,
.drawer-group-label { color: #f4f6fb; }
.drawer-group-divider { border-color: rgba(255, 255, 255, 0.12); }
@media (min-width: 700px) {
    .nav-pages { display: flex; }
    .desktop-actions { display: flex; align-items: center; }
    .mobile-logout-btn { display: none; }
}
@media (max-width: 420px) {
    .brand-link { width: 132px; min-width: 132px; }
    .brand-logo { width: 132px; flex-basis: 132px; }
}
</style>
