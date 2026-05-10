<template>
    <v-app class="home-page">
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
                    <v-img
                        src="/img/logo_seekino.png"
                        width="160"
                        height="52"
                        class="logo brand-logo"
                    />
                </RouterLink>

                <v-spacer />

                <div class="ga-2 mr-2 nav-pages">
                    <v-btn variant="text" class="text-none nav-link-btn" to="/filmas">Filmas</v-btn>
                    <v-btn variant="text" class="text-none nav-link-btn" to="/seansi">Seansi</v-btn>
                </div>

                <template v-if="isAuthenticated">
                    <v-chip class="user-chip mr-2" prepend-icon="mdi-account-circle-outline" to="/profile" link>
                        {{ user?.nickname }}
                    </v-chip>
                    <v-btn
                        rounded="xl"
                        class="text-none login-btn"
                        prepend-icon="mdi-logout"
                        :loading="navAuthLoading"
                        @click="logout"
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
                        :prepend-icon="item.icon"
                        :title="item.title"
                        link
                        rounded="lg"
                        class="drawer-list-item"
                        @click="drawer = false"
                    />
                    <v-divider
                        v-if="!group.isLast"
                        class="drawer-group-divider my-4"
                    />
                </template>
            </v-list>
        </v-navigation-drawer>

        <v-dialog v-model="authDialog" max-width="500">
            <v-card class="auth-dialog-card rounded-xl">
                <v-card-title class="d-flex align-center justify-space-between">
                    <span>{{ authMode === 'login' ? 'Pieslēgties kontam' : 'Izveidot kontu' }}</span>
                    <v-btn icon="mdi-close" variant="text" @click="closeAuth" />
                </v-card-title>

                <v-card-text class="pt-2">
                    <v-alert
                        v-if="authError"
                        type="error"
                        density="comfortable"
                        variant="tonal"
                        class="mb-3"
                    >
                        {{ authError }}
                    </v-alert>

                    <v-alert
                        v-if="authSuccess"
                        type="success"
                        density="comfortable"
                        variant="tonal"
                        class="mb-3"
                    >
                        {{ authSuccess }}
                    </v-alert>

                    <v-form @submit.prevent="submitAuth">
                        <transition name="auth-switch" mode="out-in">
                            <div :key="authMode">
                                <template v-if="authMode === 'login'">
                                    <v-text-field
                                        v-model="loginForm.email"
                                        label="E-pasts"
                                        type="email"
                                        variant="outlined"
                                        prepend-inner-icon="mdi-email-outline"
                                        class="mb-3"
                                    />
                                    <v-text-field
                                        v-model="loginForm.password"
                                        label="Parole"
                                        type="password"
                                        variant="outlined"
                                        prepend-inner-icon="mdi-lock-outline"
                                        class="mb-2"
                                    />
                                </template>

                                <template v-else>
                                    <v-text-field
                                        v-model="registerForm.name"
                                        label="Vārds"
                                        variant="outlined"
                                        prepend-inner-icon="mdi-account-outline"
                                        class="mb-3"
                                    />
                                    <v-text-field
                                        v-model="registerForm.email"
                                        label="E-pasts"
                                        type="email"
                                        variant="outlined"
                                        prepend-inner-icon="mdi-email-outline"
                                        class="mb-3"
                                    />
                                    <v-text-field
                                        v-model="registerForm.password"
                                        label="Parole"
                                        type="password"
                                        variant="outlined"
                                        prepend-inner-icon="mdi-lock-outline"
                                        class="mb-3"
                                    />
                                    <v-text-field
                                        v-model="registerForm.confirmPassword"
                                        label="Atkārto paroli"
                                        type="password"
                                        variant="outlined"
                                        prepend-inner-icon="mdi-lock-check-outline"
                                        class="mb-2"
                                    />
                                </template>
                            </div>
                        </transition>

                        <v-btn
                            type="submit"
                            color="#E50914"
                            block
                            rounded="lg"
                            class="text-none mt-2"
                            :loading="authLoading"
                        >
                            {{ authMode === 'login' ? 'Pieslēgties' : 'Izveidot kontu' }}
                        </v-btn>
                    </v-form>

                    <p class="text-caption mt-4 mb-0">
                        {{ authMode === 'login' ? 'Nav konta?' : 'Jau ir konts?' }}
                        <a href="#" class="auth-switch-link" @click.prevent="switchAuth(authMode === 'login' ? 'register' : 'login')">
                            {{ authMode === 'login' ? 'Reģistrējies' : 'Pieslēdzies' }}
                        </a>
                    </p>
                </v-card-text>
            </v-card>
        </v-dialog>

        <v-main class="main-content">
            <section class="hero-section">
                <v-container class="py-14 py-md-16">
                    <div class="hero-panel pa-6 pa-md-10">
                        <p class="hero-badge">SEEKINO Kino apmeklējumu un biļešu tiešsaistes rezervēšanas sistēma</p>
                        <h1 class="hero-title mb-3">Rezervē biļetes, atrodi filmas un dalies atsauksmēs</h1>
                        <p class="hero-subtitle mb-6">
                            Vienuviet pieejami aktuālie seansi, sēdvietu izvēle, populāras filmas un lietotāju vērtējumi.
                        </p>
                        <div class="d-flex flex-wrap ga-3">
                            <v-btn color="#E50914" size="large" rounded="lg" class="text-none">Apskatīt seansus</v-btn>
                        </div>
                    </div>
                </v-container>
            </section>

            <v-container class="py-8">
                <div class="d-flex align-center justify-space-between mb-4 flex-wrap ga-3">
                    <h2 class="section-title">Populārākās filmas</h2>
                </div>

                <div v-if="moviesLoading" class="py-8 text-center">
                    <v-progress-circular indeterminate color="#E50914" />
                    <p class="movie-state-text mt-3 mb-0">Ielādē populārākās filmas...</p>
                </div>

                <v-alert
                    v-else-if="moviesError"
                    type="error"
                    variant="tonal"
                    class="mb-4"
                >
                    <div class="d-flex align-center justify-space-between flex-wrap ga-3">
                        <span>{{ moviesError }}</span>
                        <v-btn
                            variant="outlined"
                            rounded="lg"
                            class="text-none"
                            @click="fetchPopularMovies"
                        >
                            Mēģināt vēlreiz
                        </v-btn>
                    </div>
                </v-alert>

                <v-alert
                    v-else-if="displayedMovies.length === 0"
                    type="info"
                    variant="tonal"
                    class="mb-4"
                >
                    Populārākās filmas šobrīd nav pieejamas.
                </v-alert>

                <v-row v-else>
                    <v-col
                        v-for="movie in displayedMovies"
                        :key="movie.id"
                        cols="12"
                        sm="6"
                        lg="4"
                    >
                            <v-card class="movie-card h-100 rounded-xl">
                                <v-img :src="movie.poster" height="230" cover />
                                <v-card-text>
                                <div class="d-flex justify-space-between align-center mb-2">
                                    <h3 class="movie-title">{{ movie.title }}</h3>
                                    <v-chip size="small" prepend-icon="mdi-cash" class="movie-price-chip">
                                        no {{ movie.price }}€
                                    </v-chip>
                                </div>
                                <p class="text-caption movie-meta mb-2">
                                    Režisors: {{ movie.director }} | {{ movie.duration }} min
                                </p>
                                <div class="d-flex flex-wrap ga-2 mb-2">
                                    <v-chip size="small" variant="outlined">{{ movie.genre }}</v-chip>
                                </div>
                                <v-rating
                                    :model-value="movie.rating"
                                    half-increments
                                    readonly
                                    density="compact"
                                    color="#FFD166"
                                />
                            </v-card-text>
                            <v-card-actions class="px-4 pb-4">
                                <v-btn
                                    color="#E50914"
                                    block
                                    rounded="lg"
                                    class="text-none reserve-btn"
                                    append-icon="mdi-arrow-right"
                                >
                                    Rezervēt biļetes
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-col>
                </v-row>

                <div class="mt-10">
                    <div class="d-flex align-center justify-space-between mb-4 flex-wrap ga-3">
                        <h2 class="section-title mb-0">Tuvākie seansi</h2>
                        <v-btn variant="outlined" rounded="lg" class="text-none" to="/seansi">
                            Skatīt visus seansus
                        </v-btn>
                    </div>

                    <v-row>
                        <v-col
                            v-for="show in featuredShows"
                            :key="show.id"
                            cols="12"
                            sm="6"
                            lg="3"
                        >
                            <v-card class="show-card h-100 rounded-xl pa-4">
                                <div class="d-flex align-center justify-space-between mb-3">
                                    <v-chip size="small" color="#E50914" variant="flat">{{ show.date }}</v-chip>
                                    <v-chip size="small" variant="outlined" class="show-price">{{ show.price }}€</v-chip>
                                </div>
                                <h3 class="show-movie mb-2">{{ show.movie }}</h3>
                                <p class="show-meta mb-1">
                                    <v-icon size="16" class="mr-1">mdi-clock-outline</v-icon>{{ show.time }}
                                </p>
                                <p class="show-meta mb-4">
                                    <v-icon size="16" class="mr-1">mdi-sofa-outline</v-icon>{{ show.hall }} zāle |
                                    {{ show.freeSeats }} brīvas vietas
                                </p>
                                <v-btn color="#E50914" block rounded="lg" class="text-none">Izvēlēties vietas</v-btn>
                            </v-card>
                        </v-col>
                    </v-row>
                </div>
            </v-container>
        </v-main>

        <v-footer class="site-footer pa-0">
            <v-container class="py-10">
                <v-row class="ga-0">
                    <v-col cols="12" md="4" class="pr-md-8">
                        <v-img
                            src="/img/logo_seekino.png"
                            max-width="160"
                            height="52"
                            class="logo mb-3"
                        />
                        <p class="footer-text mb-4">
                            Mūsdienīga kino biļešu platforma ar ērtu rezervāciju, seansu pārskatu un filmu atlasi pēc
                            tavām vēlmēm.
                        </p>
                        <div class="d-flex ga-2">
                            <v-btn
                                v-for="icon in socialIcons"
                                :key="icon"
                                :icon="icon"
                                size="small"
                                variant="outlined"
                                class="footer-social-btn"
                            />
                        </div>
                    </v-col>

                    <v-col cols="6" md="2">
                        <h4 class="footer-heading mb-3">Navigācija</h4>
                        <v-list density="compact" class="footer-list pa-0">
                            <v-list-item
                                v-for="link in footerNavLinks"
                                :key="link.title"
                                :to="link.to"
                                :title="link.title"
                                class="px-0"
                            />
                        </v-list>
                    </v-col>

                    <v-col cols="6" md="3">
                        <h4 class="footer-heading mb-3">Lietotājiem</h4>
                        <v-list density="compact" class="footer-list pa-0">
                            <v-list-item
                                v-for="link in footerUserLinks"
                                :key="link.title"
                                :to="link.to || undefined"
                                :title="link.title"
                                class="px-0"
                            />
                        </v-list>
                    </v-col>

                    <v-col cols="12" md="3">
                        <h4 class="footer-heading mb-3">Kontakti</h4>
                        <p class="footer-text mb-2">Brīvības iela 100, Rīga</p>
                        <p class="footer-text mb-2">+371 2000 1234</p>
                        <p class="footer-text mb-0">info@seekino.lv</p>
                    </v-col>
                </v-row>
            </v-container>

            <div class="footer-bottom w-100 py-3 px-4 text-center">
                Copyright &copy; 2026 SEEKINO. Visas tiesības aizsargātas.
            </div>
        </v-footer>
    </v-app>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuth } from '@/services/auth'

const drawer = ref(false)
const { user, isAuthenticated, authLoading: navAuthLoading, logout } = useAuth()
const authDialog = ref(false)
const authMode = ref('login')
const authLoading = ref(false)
const authError = ref('')
const authSuccess = ref('')
const loginForm = ref({ email: '', password: '' })
const registerForm = ref({ name: '', email: '', password: '', confirmPassword: '' })
const menuGroups = [
    {
        title: 'Kino',
        items: [
            { title: 'Sākums', icon: 'mdi-home-variant-outline', to: '/' },
            { title: 'Filmas', icon: 'mdi-movie-open-outline', to: '/filmas' },
            { title: 'Seansi', icon: 'mdi-calendar-clock-outline', to: '/seansi' },
        ],
    },
    {
        title: 'Lietotājs',
        isLast: true,
        items: [
            { title: 'Mans profils', icon: 'mdi-account-outline', to: '/profile' },
            { title: 'Manas rezervācijas', icon: 'mdi-ticket-confirmation-outline', to: '/profile' },
            { title: 'Kontakti', icon: 'mdi-phone-outline', to: '/kontakti' },
        ],
    },
]
const footerNavLinks = [
    { title: 'Sākums', to: '/' },
    { title: 'Filmas', to: '/filmas' },
    { title: 'Seansi', to: '/seansi' },
    { title: 'Kontakti', to: '/kontakti' },
]
const footerUserLinks = [
    { title: 'Mans profils', to: '/profile' },
    { title: 'Rezervācijas', to: '/profile' },
    { title: 'Atbalsts' },
    { title: 'Privātuma politika' },
]
const socialIcons = ['mdi-facebook', 'mdi-instagram', 'mdi-youtube', 'mdi-twitter']

const movies = ref([])
const moviesLoading = ref(false)
const moviesError = ref('')

const upcomingShows = ref([
    { id: 1, movie: 'Klusuma kods', date: '17.02.2026', time: '18:30', price: 8, hall: '1.', freeSeats: 42 },
    { id: 2, movie: 'Orbīta 9', date: '17.02.2026', time: '20:40', price: 10, hall: 'IMAX', freeSeats: 16 },
    { id: 3, movie: 'Vasaras logs', date: '18.02.2026', time: '17:20', price: 7, hall: '2.', freeSeats: 58 },
    { id: 4, movie: 'Smieklu terapija', date: '18.02.2026', time: '19:00', price: 6, hall: '3.', freeSeats: 64 },
])

const displayedMovies = computed(() => movies.value.slice(0, 3))
const featuredShows = computed(() => upcomingShows.value.slice(0, 4))
const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000'

const normalizeMovie = (movie, index) => ({
    id: movie.id ?? `${movie.title || 'movie'}-${index}`,
    title: movie.title || 'Filmas nosaukums nav pieejams',
    director: movie.director || 'Nav norādīts',
    duration: movie.duration ?? '-',
    genre: movie.genre || 'Nav norādīts',
    rating: Number(movie.rating) || 0,
    price: movie.price ?? '-',
    poster: movie.poster || '',
})

const extractMovies = (payload) => {
    if (Array.isArray(payload)) {
        return payload
    }

    if (Array.isArray(payload?.data)) {
        return payload.data
    }

    return []
}

const fetchPopularMovies = async () => {
    moviesLoading.value = true
    moviesError.value = ''

    try {
        const response = await fetch(`${apiBaseUrl}/api/movies?sort=rating&direction=desc`)

        if (!response.ok) {
            throw new Error('Filmas neizdevās ielādēt.')
        }

        const payload = await response.json()
        movies.value = extractMovies(payload).slice(0, 3).map(normalizeMovie)
    } catch {
        movies.value = []
        moviesError.value = 'Populārākās filmas šobrīd neizdevās ielādēt.'
    } finally {
        moviesLoading.value = false
    }
}

const isEmailValid = (value) => /^\S+@\S+\.\S+$/.test(value)

const openAuth = (mode = 'login') => {
    authMode.value = mode
    authDialog.value = true
    authError.value = ''
    authSuccess.value = ''
}

const closeAuth = () => {
    authDialog.value = false
    authLoading.value = false
}

const switchAuth = (mode) => {
    authMode.value = mode
    authError.value = ''
    authSuccess.value = ''
}

const submitAuth = async () => {
    authError.value = ''
    authSuccess.value = ''

    if (authMode.value === 'login') {
        if (!loginForm.value.email || !loginForm.value.password) {
            authError.value = 'Lūdzu aizpildi e-pastu un paroli.'
            return
        }
        if (!isEmailValid(loginForm.value.email)) {
            authError.value = 'E-pasta adrese nav pareiza.'
            return
        }
    } else {
        if (
            !registerForm.value.name ||
            !registerForm.value.email ||
            !registerForm.value.password ||
            !registerForm.value.confirmPassword
        ) {
            authError.value = 'Lūdzu aizpildi visus reģistrācijas laukus.'
            return
        }
        if (!isEmailValid(registerForm.value.email)) {
            authError.value = 'E-pasta adrese nav pareiza.'
            return
        }
        if (registerForm.value.password.length < 6) {
            authError.value = 'Parolei jābūt vismaz 6 simbolus garai.'
            return
        }
        if (registerForm.value.password !== registerForm.value.confirmPassword) {
            authError.value = 'Paroles nesakrīt.'
            return
        }
    }

    authLoading.value = true
    await new Promise((resolve) => setTimeout(resolve, 500))
    authLoading.value = false
    authSuccess.value =
        authMode.value === 'login'
            ? 'Pieslēgšanās forma gatava. Nākamais solis: savienot ar Laravel API.'
            : 'Reģistrācijas forma gatava. Nākamais solis: savienot ar Laravel API.'
}

onMounted(fetchPopularMovies)

</script>

<style scoped>
.home-page {
    background:
        radial-gradient(circle at 20% 15%, #243558 0%, transparent 30%),
        radial-gradient(circle at 80% 10%, #531d2c 0%, transparent 35%),
        #0a0c12;
    color: #f4f6fb;
}

.hero-panel,
.movie-card,
.show-card {
    animation: subtle-fade-in 0.42s ease both;
}

.sticky-app-bar {
    position: sticky !important;
    top: 0;
    z-index: 1100;
}

.app-bar-shell {
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(11, 14, 22, 0.82) !important;
    backdrop-filter: blur(10px);
}

.app-bar-inner {
    min-height: 76px;
}

.main-content {
    padding-top: 0;
}

.logo {
    filter: invert(1);
}

.brand-link {
    display: inline-flex;
    align-items: center;
    text-decoration: none;
    width: 160px;
    min-width: 160px;
    transition: opacity 0.16s ease, transform 0.16s ease;
}

.brand-link:hover {
    opacity: 0.92;
    transform: translateY(-1px);
}

.brand-logo {
    width: 160px;
    flex: 0 0 160px;
}

.nav-btn {
    color: #f4f6fb;
}

.nav-link-btn {
    color: #d7dff2;
    border: 1px solid transparent;
    transition: color 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, transform 0.2s ease,
        box-shadow 0.2s ease;
}

.nav-link-btn:hover {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.16);
    transform: scale(1.03);
    box-shadow: 0 0 18px rgba(76, 114, 255, 0.12);
}

.login-btn {
    margin-left: 5px;
    background: linear-gradient(135deg, #ff5a44, #e50914);
    color: #ffffff !important;
    font-weight: 700;
    letter-spacing: 0.01em;
    box-shadow: 0 5px 26px rgba(229, 9, 20, 0.38);
    border: 1px solid rgba(255, 255, 255, 0.24);
    transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
}

.login-btn :deep(.v-btn__content),
.login-btn :deep(.v-icon) {
    color: #ffffff !important;
}

.login-btn:hover {
    transform: scale(1.03);
    box-shadow: 0 8px 30px rgba(229, 9, 20, 0.5), 0 0 24px rgba(108, 132, 255, 0.14);
    filter: brightness(1.07);
}

.nav-pages {
    display: none;
}

@media (min-width: 700px) {
    .nav-pages {
        display: flex;
    }
}

.auth-dialog-card {
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: linear-gradient(165deg, #121724, #10131c);
    color: #edf2ff;
}

.auth-dialog-card :deep(.v-field),
.auth-dialog-card :deep(.v-label),
.auth-dialog-card :deep(.v-field__input),
.auth-dialog-card :deep(.v-icon) {
    color: #edf2ff;
}

.auth-dialog-card :deep(.v-card-title),
.auth-dialog-card :deep(.v-card-text),
.auth-dialog-card :deep(.v-btn),
.auth-dialog-card :deep(.v-alert__content),
.auth-dialog-card :deep(.text-caption) {
    color: #edf2ff;
}

.auth-dialog-card :deep(.v-label.v-field-label) {
    opacity: 0.85;
}

.auth-switch-link {
    color: #ff5a44;
    text-decoration: none;
    font-weight: 600;
}

.auth-switch-link:hover {
    text-decoration: underline;
}

.auth-switch-enter-active,
.auth-switch-leave-active {
    transition: opacity 0.24s ease, transform 0.24s ease;
}

.auth-switch-enter-from {
    opacity: 0;
    transform: translateY(8px);
}

.auth-switch-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}

.hero-section {
    position: relative;
    overflow: hidden;
    background:
        radial-gradient(circle at 12% 18%, rgba(68, 111, 203, 0.34), transparent 42%),
        radial-gradient(circle at 82% 14%, rgba(220, 54, 88, 0.3), transparent 38%),
        radial-gradient(circle at 56% 86%, rgba(66, 141, 106, 0.22), transparent 36%),
        linear-gradient(130deg, #0f1628 0%, #17172a 45%, #2a141d 100%);
}

.hero-section::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        repeating-linear-gradient(
            120deg,
            rgba(255, 255, 255, 0.03) 0,
            rgba(255, 255, 255, 0.03) 1px,
            transparent 1px,
            transparent 18px
        );
    opacity: 0.35;
    pointer-events: none;
}

.hero-section::after {
    content: '';
    position: absolute;
    width: 440px;
    height: 440px;
    right: -120px;
    bottom: -220px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255, 76, 100, 0.3) 0%, rgba(255, 76, 100, 0) 70%);
    filter: blur(14px);
    pointer-events: none;
}

.hero-panel {
    position: relative;
    z-index: 1;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 24px;
    background: linear-gradient(150deg, rgba(10, 13, 21, 0.82), rgba(17, 20, 31, 0.76));
    backdrop-filter: blur(10px);
    box-shadow: 0 16px 48px rgba(6, 8, 13, 0.45);
}

.hero-badge {
    display: inline-block;
    margin-bottom: 10px;
    padding: 6px 12px;
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 999px;
    font-size: 12px;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #d7e2ff;
}

.hero-title {
    max-width: 16ch;
    font-size: clamp(2rem, 4vw, 3.6rem);
    line-height: 1.05;
    letter-spacing: -0.02em;
}

.hero-subtitle {
    max-width: 64ch;
    color: #d2d9e7;
}

.section-title {
    font-size: clamp(1.2rem, 2vw, 1.8rem);
    font-weight: 700;
}

.movie-card {
    border: 1px solid rgba(255, 255, 255, 0.09);
    background: linear-gradient(180deg, #141926, #0f131d);
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, filter 0.2s ease;
}

.movie-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 24px 42px rgba(0, 0, 0, 0.34);
    border-color: rgba(255, 255, 255, 0.18);
    filter: brightness(1.02);
}

.movie-title {
    font-size: 1.1rem;
    line-height: 1.2;
}

.movie-meta {
    color: #d6def0;
}

.movie-description {
    color: #d0d7e6;
    line-height: 1.55;
}

.movie-price-chip {
    background: linear-gradient(135deg, #24b26b, #149e59);
    color: #ffffff;
    font-weight: 700;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 20px rgba(23, 167, 95, 0.35);
}

.reserve-btn {
    background: linear-gradient(135deg, #ff3b30, #e50914);
    color: #ffffff !important;
    font-weight: 700;
    letter-spacing: 0.02em;
    box-shadow: 0 10px 24px rgba(229, 9, 20, 0.4);
    transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
}

.reserve-btn :deep(.v-btn__content),
.reserve-btn :deep(.v-icon) {
    color: #ffffff !important;
}

.reserve-btn:hover {
    transform: scale(1.03);
    box-shadow: 0 14px 28px rgba(229, 9, 20, 0.5), 0 0 24px rgba(91, 112, 255, 0.12);
    filter: brightness(1.08);
}

.movie-card:hover .reserve-btn {
    box-shadow: 0 16px 30px rgba(229, 9, 20, 0.58), 0 0 28px rgba(91, 112, 255, 0.16);
    filter: brightness(1.1);
}

.drawer-list :deep(.v-list-item-title) {
    color: #f4f6fb;
}

.app-drawer {
    position: relative;
    border-right: 1px solid rgba(255, 255, 255, 0.12);
    box-shadow: 18px 0 42px rgba(0, 0, 0, 0.36);
}

.drawer-header {
    min-height: 72px;
    background: rgba(255, 255, 255, 0.02);
}

.drawer-group-label {
    min-height: 28px;
    color: #8994ac;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.drawer-group-divider {
    border-color: rgba(255, 255, 255, 0.08);
}

.drawer-list :deep(.v-list-item) {
    min-height: 44px;
    margin-bottom: 6px;
    color: #d7dff2;
    border: 1px solid transparent;
    transition: background-color 0.18s ease, border-color 0.18s ease, transform 0.18s ease, color 0.18s ease;
}

.drawer-list :deep(.v-list-item:hover) {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.06);
    border-color: rgba(255, 255, 255, 0.1);
    transform: translateX(2px);
}

.drawer-list :deep(.v-list-item:active) {
    background: rgba(229, 9, 20, 0.1);
    transform: translateX(1px);
}

.drawer-list :deep(.v-list-item--active) {
    background: rgba(229, 9, 20, 0.08);
    border-color: transparent;
    box-shadow: inset 3px 0 0 rgba(229, 9, 20, 0.72);
}

.drawer-list :deep(.v-list-item--active .v-list-item-title),
.drawer-list :deep(.v-list-item--active .v-icon) {
    color: #ffffff;
}

.drawer-list :deep(.v-icon) {
    color: #eef3ff;
    opacity: 1;
}

.drawer-close-btn {
    position: absolute;
    top: 14px;
    right: 14px;
    color: #f4f6fb;
    background: transparent;
    border: 0;
    opacity: 0.72;
    transition: background-color 0.18s ease, border-color 0.18s ease, transform 0.18s ease;
}

.drawer-close-btn:hover {
    background: rgba(255, 255, 255, 0.05);
    opacity: 1;
    transform: scale(1.02);
}

.movie-card :deep(.v-card-text),
.movie-card :deep(.v-chip) {
    color: #f4f6fb;
}

.movie-card :deep(.v-card-text) {
    padding: 20px 20px 12px;
}

.movie-card :deep(.v-card-actions) {
    padding: 0 20px 20px !important;
}

.show-card {
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: linear-gradient(180deg, #161b29, #111522);
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.show-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 22px 40px rgba(0, 0, 0, 0.3);
    border-color: rgba(255, 255, 255, 0.16);
}

.show-movie {
    font-size: 1.02rem;
    line-height: 1.3;
    color: #ffffff;
}

.show-meta {
    display: flex;
    align-items: center;
    color: #d9e1f0;
    font-size: 0.9rem;
}

.show-price {
    color: #9cf0bc;
    border-color: rgba(156, 240, 188, 0.35);
}

.site-footer {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    margin-top: 64px;
    border-top: 1px solid rgba(255, 255, 255, 0.12);
    background:
        linear-gradient(180deg, rgba(14, 16, 24, 0.95), rgba(9, 11, 16, 1)),
        radial-gradient(circle at 15% 0%, rgba(51, 76, 126, 0.2), transparent 40%);
}

.footer-heading {
    font-size: 0.95rem;
    color: #ffffff;
}

.footer-text {
    color: #c5cddd;
    line-height: 1.55;
    font-size: 0.92rem;
}

.footer-list {
    background: transparent;
}

.footer-list :deep(.v-list-item-title) {
    color: #d4ddf1;
    font-size: 0.9rem;
}

.footer-list :deep(.v-list-item:hover .v-list-item-title) {
    color: #ffffff;
}

.footer-social-btn {
    border-color: rgba(255, 255, 255, 0.24);
    color: #e7eeff;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.footer-social-btn:hover {
    transform: scale(1.03);
    box-shadow: 0 0 18px rgba(76, 114, 255, 0.12);
    border-color: rgba(255, 255, 255, 0.34);
}

.footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(6, 8, 12, 0.7);
    color: #9ea8bf;
    font-size: 0.82rem;
}

:deep(.v-btn) {
    transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease, background-color 0.2s ease,
        border-color 0.2s ease;
}

.hero-section,
.movie-card,
.show-card,
.site-footer {
    transition: box-shadow 0.2s ease, transform 0.2s ease, opacity 0.2s ease;
}

@keyframes subtle-fade-in {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
