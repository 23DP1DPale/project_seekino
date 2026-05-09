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
                    <v-chip class="user-chip mr-2" prepend-icon="mdi-account-circle-outline">
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
                <v-container class="py-8 py-md-10">
                    <div class="hero-panel pa-5 pa-md-7">
                        <v-btn
                            variant="text"
                            class="text-none back-link mb-4"
                            prepend-icon="mdi-arrow-left"
                            to="/filmas"
                        >
                            Atpakaļ uz filmām
                        </v-btn>

                        <div v-if="movieLoading" class="detail-state text-center py-10">
                            <v-progress-circular indeterminate color="#E50914" size="46" class="mb-4" />
                            <h1 class="section-title mb-2">Filma tiek ielādēta</h1>
                            <p class="detail-muted mb-0">Lūdzu uzgaidi, kamēr saņemam informāciju no servera.</p>
                        </div>

                        <v-alert
                            v-else-if="movieError"
                            type="error"
                            variant="tonal"
                            class="detail-alert rounded-xl"
                        >
                            <div class="d-flex align-center justify-space-between flex-wrap ga-3">
                                <span>{{ movieError }}</span>
                                <v-btn
                                    color="#E50914"
                                    rounded="lg"
                                    class="text-none"
                                    :loading="movieLoading"
                                    @click="fetchMovie"
                                >
                                    Mēģināt vēlreiz
                                </v-btn>
                            </div>
                        </v-alert>

                        <v-row v-else-if="movie" align="center" class="movie-detail-grid">
                            <v-col cols="12" md="5" lg="4">
                                <v-img
                                    :src="movie.poster"
                                    :alt="movie.title"
                                    aspect-ratio="0.72"
                                    cover
                                    class="detail-poster rounded-xl"
                                />
                            </v-col>

                            <v-col cols="12" md="7" lg="8">
                                <p class="hero-badge">SEEKINO filmas detaļas</p>
                                <h1 class="detail-title mb-4">{{ movie.title }}</h1>

                                <div class="d-flex flex-wrap ga-2 mb-4">
                                    <v-chip size="small" class="movie-price-chip">no {{ movie.price }}</v-chip>
                                    <v-chip size="small" variant="outlined" class="detail-chip">
                                        {{ movie.duration }} min
                                    </v-chip>
                                    <v-chip size="small" variant="outlined" class="detail-chip">
                                        {{ movie.ageRating }}
                                    </v-chip>
                                </div>

                                <p class="detail-description mb-5">{{ movie.description }}</p>

                                <v-row class="detail-facts">
                                    <v-col cols="12" sm="6">
                                        <span class="fact-label">Režisors</span>
                                        <strong>{{ movie.director }}</strong>
                                    </v-col>
                                    <v-col cols="12" sm="6">
                                        <span class="fact-label">Vērtējums</span>
                                        <div class="d-flex align-center ga-2">
                                            <v-rating
                                                :model-value="movie.rating"
                                                half-increments
                                                readonly
                                                density="compact"
                                                color="#FFD166"
                                            />
                                            <strong>{{ movie.rating }}</strong>
                                        </div>
                                    </v-col>
                                    <v-col cols="12" sm="6">
                                        <span class="fact-label">Žanri</span>
                                        <strong>{{ movie.genresLabel }}</strong>
                                    </v-col>
                                    <v-col cols="12" sm="6">
                                        <span class="fact-label">Nākamais seanss</span>
                                        <strong>{{ movie.nextScreeningLabel }}</strong>
                                    </v-col>
                                </v-row>

                                <div class="d-flex flex-wrap ga-3 mt-6">
                                    <v-btn
                                        color="#E50914"
                                        size="large"
                                        rounded="lg"
                                        class="text-none reserve-btn"
                                        append-icon="mdi-calendar-clock-outline"
                                        @click="scrollToScreenings"
                                    >
                                        Izvēlēties seansu
                                    </v-btn>
                                    <v-btn
                                        variant="outlined"
                                        size="large"
                                        rounded="lg"
                                        class="text-none outline-btn"
                                        to="/filmas"
                                    >
                                        Atpakaļ uz filmām
                                    </v-btn>
                                </div>

                            </v-col>
                        </v-row>
                    </div>
                </v-container>
            </section>

            <section v-if="movie && !movieLoading && !movieError" ref="screeningsSection" class="screenings-section">
                <v-container class="pt-4 pb-8">
                    <div class="d-flex align-center justify-space-between mb-4 flex-wrap ga-3">
                        <h2 class="section-title mb-0">Pieejamie seansi</h2>
                        <v-chip
                            v-if="selectedScreening"
                            size="small"
                            variant="outlined"
                            class="detail-chip"
                            prepend-icon="mdi-check-circle-outline"
                        >
                            Izvēlēts: {{ selectedScreening.date }} {{ selectedScreening.time }}
                        </v-chip>
                    </div>

                    <v-row v-if="movie.screenings.length" dense>
                        <v-col
                            v-for="screening in movie.screenings"
                            :key="screening.id"
                            cols="12"
                            sm="6"
                            lg="4"
                        >
                            <v-card
                                class="screening-card screening-choice-card h-100 rounded-xl pa-4"
                                :class="{ 'screening-choice-card--selected': selectedScreeningId === screening.id }"
                            >
                                <div class="d-flex align-center justify-space-between mb-3">
                                    <v-chip size="small" color="#E50914" variant="flat">
                                        {{ screening.date }}
                                    </v-chip>
                                    <v-chip size="small" variant="outlined" class="detail-chip">
                                        {{ screening.price }}
                                    </v-chip>
                                </div>
                                <p class="screening-meta mb-2">
                                    <v-icon size="16" class="mr-1">mdi-clock-outline</v-icon>{{ screening.time }}
                                </p>
                                <p class="screening-meta mb-4">
                                    <v-icon size="16" class="mr-1">mdi-sofa-outline</v-icon>{{ screening.hall }}
                                </p>
                                <v-btn
                                    color="#E50914"
                                    block
                                    rounded="lg"
                                    class="text-none reserve-btn"
                                    :to="`/reservation/${screening.id}`"
                                >
                                    Rezervēt biļeti
                                </v-btn>
                            </v-card>
                        </v-col>
                    </v-row>

                    <v-card v-else class="empty-state-card rounded-xl pa-6 pa-md-8 text-center">
                        <p class="empty-state-copy mb-0">Šobrīd šai filmai nav pieejamu seansu.</p>
                    </v-card>
                </v-container>
            </section>
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
                                :key="link"
                                :title="link"
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
import { computed, onMounted, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useAuth } from '@/services/auth'

const route = useRoute()
const drawer = ref(false)
const { user, isAuthenticated, authLoading: navAuthLoading, logout } = useAuth()
const authDialog = ref(false)
const authMode = ref('login')
const authLoading = ref(false)
const authError = ref('')
const authSuccess = ref('')
const loginForm = ref({ email: '', password: '' })
const registerForm = ref({ name: '', email: '', password: '', confirmPassword: '' })
const movie = ref(null)
const movieLoading = ref(false)
const movieError = ref('')
const selectedScreeningId = ref(null)
const screeningsSection = ref(null)
const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000'

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
            { title: 'Mans profils', icon: 'mdi-account-outline', to: '/profils' },
            { title: 'Manas rezervācijas', icon: 'mdi-ticket-confirmation-outline', to: '/rezervacijas' },
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
const footerUserLinks = ['Mans profils', 'Rezervācijas', 'Atbalsts', 'Privātuma politika']
const socialIcons = ['mdi-facebook', 'mdi-instagram', 'mdi-youtube', 'mdi-twitter']

const movieId = computed(() => route.params.id)
const selectedScreening = computed(() =>
    movie.value?.screenings.find((screening) => screening.id === selectedScreeningId.value) || null
)

const formatDate = (value) => {
    if (!value) return 'Nav ieplānots'

    const [year, month, day] = String(value).replace('T', ' ').slice(0, 10).split('-')

    if (!year || !month || !day) {
        return String(value)
    }

    return `${day}.${month}.${year}.`
}

const formatTime = (value) => {
    if (!value) return 'Laiks nav norādīts'

    return String(value).slice(0, 5)
}

const formatDateTime = (value) => {
    if (!value) return 'Nav ieplānots'

    const normalized = String(value).replace('T', ' ')
    const [date, time] = normalized.split(' ')

    return `${formatDate(date)} ${formatTime(time)}`
}

const formatPrice = (value) => {
    if (value === null || value === undefined || value === '') return '-'

    return `${Number(value).toFixed(2)} €`
}

const normalizeScreening = (screening) => ({
    id: screening.id,
    date: formatDate(screening.screening_date || screening.date || screening.datetime?.slice(0, 10)),
    time: formatTime(screening.screening_time || screening.time || screening.datetime?.slice(11, 16)),
    price: formatPrice(screening.price ?? screening.cost),
    hall: screening.hall?.name || (typeof screening.hall === 'string' ? screening.hall : 'Zāle nav norādīta'),
})

const normalizeMovie = (payload) => {
    const genres = Array.isArray(payload.genres)
        ? payload.genres.map((genre) => genre.name || genre).filter(Boolean)
        : [payload.genre].filter(Boolean)
    const screenings = Array.isArray(payload.screenings) ? payload.screenings.map(normalizeScreening) : []
    const nextScreening = payload.next_screening?.datetime ||
        [payload.next_screening?.date, payload.next_screening?.time].filter(Boolean).join(' ') ||
        payload.nextSession

    return {
        id: payload.id,
        title: payload.title || payload.name || 'Filmas nosaukums nav pieejams',
        description: payload.description || 'Apraksts šobrīd nav pieejams.',
        director: payload.director || 'Nav norādīts',
        duration: payload.duration ?? payload.length ?? '-',
        genresLabel: genres.length ? genres.join(', ') : 'Nav norādīts',
        ageRating: payload.ageRating || payload.age_restriction || 'Nav norādīts',
        rating: Number(payload.rating ?? payload.average_rating) || 0,
        price: formatPrice(payload.price ?? payload.lowest_price ?? payload.minPrice),
        poster: payload.poster || payload.image || '',
        nextScreeningLabel: formatDateTime(nextScreening),
        screenings,
    }
}

const fetchMovie = async () => {
    movieLoading.value = true
    movieError.value = ''
    selectedScreeningId.value = null

    try {
        const response = await fetch(`${apiBaseUrl}/api/movies/${movieId.value}`, {
            headers: {
                Accept: 'application/json',
            },
        })

        if (!response.ok) {
            throw new Error('Neizdevās ielādēt filmas detaļas.')
        }

        const data = await response.json()
        movie.value = normalizeMovie(data)
    } catch (error) {
        movie.value = null
        movieError.value = error.message || 'Neizdevās ielādēt filmas detaļas.'
    } finally {
        movieLoading.value = false
    }
}

onMounted(fetchMovie)
watch(movieId, fetchMovie)

const selectScreening = (screening) => {
    selectedScreeningId.value = screening.id
}

const scrollToScreenings = () => {
    const target = screeningsSection.value?.$el || screeningsSection.value

    if (!target) return

    const headerOffset = 96
    const top = target.getBoundingClientRect().top + window.scrollY - headerOffset

    window.scrollTo({ top, behavior: 'smooth' })
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
.screening-card,
.empty-state-card {
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
    position: relative;
    padding-top: 0;
    overflow: hidden;
    background:
        radial-gradient(circle at 12% 18%, rgba(68, 111, 203, 0.34), transparent 42%),
        radial-gradient(circle at 82% 14%, rgba(220, 54, 88, 0.3), transparent 38%),
        radial-gradient(circle at 56% 86%, rgba(66, 141, 106, 0.22), transparent 36%),
        linear-gradient(130deg, #0f1628 0%, #17172a 45%, #2a141d 100%);
}

.main-content::before {
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

.nav-link-btn,
.back-link {
    color: #d7dff2;
    border: 1px solid transparent;
    transition: color 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, transform 0.2s ease,
        box-shadow 0.2s ease;
}

.nav-link-btn:hover,
.back-link:hover {
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
.auth-dialog-card :deep(.v-icon),
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
    background: transparent;
}

.hero-section::before {
    content: none;
}

.screenings-section {
    position: relative;
    overflow: hidden;
    background: transparent;
}

.screenings-section::before {
    content: none;
}

.hero-section :deep(.v-container),
.screenings-section :deep(.v-container) {
    position: relative;
    z-index: 1;
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

.detail-title {
    max-width: 16ch;
    font-size: clamp(2rem, 4vw, 3.6rem);
    line-height: 1.05;
    letter-spacing: -0.02em;
}

.detail-description {
    max-width: 72ch;
    color: #d2d9e7;
    line-height: 1.7;
}

.detail-muted {
    color: #c8d0df;
}

.detail-poster {
    border: 1px solid rgba(255, 255, 255, 0.14);
    box-shadow: 0 24px 42px rgba(0, 0, 0, 0.34);
}

.detail-facts {
    color: #f4f6fb;
}

.fact-label {
    display: block;
    margin-bottom: 4px;
    color: #9eabc4;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.section-title {
    font-size: clamp(1.2rem, 2vw, 1.8rem);
    font-weight: 700;
}

.movie-price-chip {
    background: linear-gradient(135deg, #24b26b, #149e59);
    color: #ffffff;
    font-weight: 700;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 20px rgba(23, 167, 95, 0.35);
}

.detail-chip {
    color: #f4f6fb;
    border-color: rgba(255, 255, 255, 0.24);
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

.reserve-btn.v-btn--disabled {
    opacity: 0.48;
    box-shadow: none;
}

.outline-btn {
    color: #edf2ff;
    border-color: rgba(255, 255, 255, 0.28);
}

.screening-card,
.empty-state-card {
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: linear-gradient(180deg, #141926, #0f131d);
}

.screening-choice-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, filter 0.2s ease;
}

.screening-choice-card:hover {
    transform: translateY(-3px);
    border-color: rgba(255, 255, 255, 0.2);
    box-shadow: 0 18px 32px rgba(0, 0, 0, 0.26);
}

.screening-choice-card--selected {
    border-color: rgba(36, 178, 107, 0.88);
    box-shadow: 0 0 0 1px rgba(36, 178, 107, 0.44), 0 18px 36px rgba(9, 61, 38, 0.32);
    filter: brightness(1.04);
}

.screening-card :deep(.v-card-text),
.screening-card :deep(.v-chip) {
    color: #f4f6fb;
}

.screening-title {
    color: #ffffff;
    font-size: 1.1rem;
    line-height: 1.2;
}

.screening-meta {
    color: #d6def0;
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

.drawer-list :deep(.v-list-item--active) {
    background: rgba(229, 9, 20, 0.08);
    border-color: transparent;
    box-shadow: inset 3px 0 0 rgba(229, 9, 20, 0.72);
}

.drawer-list :deep(.v-list-item--active .v-list-item-title),
.drawer-list :deep(.v-list-item--active .v-icon),
.drawer-list :deep(.v-icon) {
    color: #eef3ff;
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

.footer-text {
    color: #c5cddd;
    line-height: 1.55;
    font-size: 0.92rem;
}

.footer-heading {
    font-size: 0.95rem;
    color: #ffffff;
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
    text-align: center;
}

.empty-state-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 76px;
    height: 76px;
    border-radius: 50%;
    background: rgba(229, 9, 20, 0.12);
    color: #ff7a70;
}

.empty-state-title {
    color: #ffffff;
    font-size: 1.35rem;
}

.empty-state-copy {
    max-width: 44ch;
    margin-inline: auto;
    color: #c8d0df;
}

:deep(.v-btn) {
    transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease, background-color 0.2s ease,
        border-color 0.2s ease;
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

@media (max-width: 600px) {
    .detail-title {
        max-width: 100%;
    }
}
</style>
