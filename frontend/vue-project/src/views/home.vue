<template>
    <div class="home-page">

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
                            <v-btn color="#E50914" size="large" rounded="lg" class="text-none" to="/seansi">Apskatīt seansus</v-btn>
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
                                        {{ movie.priceLabel }}
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
                                    :to="`/filmas/${movie.id}`"
                                >
                                    Skatīt detaļas
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

                    <div v-if="screeningsLoading" class="py-8 text-center">
                        <v-progress-circular indeterminate color="#E50914" />
                        <p class="movie-state-text mt-3 mb-0">Ielādē seansus...</p>
                    </div>

                    <v-alert
                        v-else-if="screeningsError"
                        type="error"
                        variant="tonal"
                        class="mb-4"
                    >
                        <div class="d-flex align-center justify-space-between flex-wrap ga-3">
                            <span>{{ screeningsError }}</span>
                            <v-btn
                                variant="outlined"
                                rounded="lg"
                                class="text-none"
                                @click="fetchUpcomingShows"
                            >
                                Mēģināt vēlreiz
                            </v-btn>
                        </div>
                    </v-alert>

                    <v-alert
                        v-else-if="featuredShows.length === 0"
                        type="info"
                        variant="tonal"
                        class="mb-4"
                    >
                        Šobrīd nav pieejamu seansu.
                    </v-alert>

                    <v-row v-else>
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
                                    <v-chip size="small" variant="outlined" class="show-price">{{ show.price }}</v-chip>
                                </div>
                                <h3 class="show-movie mb-2">{{ show.movie }}</h3>
                                <p class="show-meta mb-1">
                                    <v-icon size="16" class="mr-1">mdi-clock-outline</v-icon>{{ show.time }}
                                </p>
                                <p class="show-meta mb-4">
                                    <v-icon size="16" class="mr-1">mdi-sofa-outline</v-icon>{{ show.hall }}
                                    <template v-if="show.freeSeats !== null">
                                        | {{ show.freeSeats }} brīvas vietas
                                    </template>
                                </p>
                                <v-btn
                                    color="#E50914"
                                    block
                                    rounded="lg"
                                    class="text-none"
                                    :to="`/reservation/${show.id}?from=home`"
                                >
                                    Rezervēt biļeti
                                </v-btn>
                            </v-card>
                        </v-col>
                    </v-row>
                </div>
            </v-container>
        </v-main>
    </div>
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

const upcomingShows = ref([])
const screeningsLoading = ref(false)
const screeningsError = ref('')

const displayedMovies = computed(() => movies.value.slice(0, 3))
const featuredShows = computed(() => upcomingShows.value.slice(0, 4))
const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000'
const defaultMoviePoster = 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1000&q=80'

const priceLabel = (price) => price === null || price === undefined || price === ''
    ? 'Cena nav pieejama'
    : `no ${Number(price).toFixed(2)} EUR`

const numericValue = (value) => {
    const number = Number(value)

    return Number.isFinite(number) ? number : null
}

const normalizeMovie = (movie, index) => {
    const genre = movie.genre || movie.genres?.find((item) => item.primary)?.name || movie.genres?.[0]?.name
    const hasFutureScreening = Boolean(movie.next_screening || movie.nextSession)
    const price = hasFutureScreening
        ? numericValue(movie.next_screening?.price ?? movie.lowest_price ?? movie.minPrice ?? movie.price)
        : null

    return {
        id: movie.id ?? `${movie.title || 'movie'}-${index}`,
        title: movie.title || 'Filmas nosaukums nav pieejams',
        director: movie.director || 'Nav norādīts',
        duration: movie.duration ?? '-',
        genre: genre || 'Žanrs nav norādīts',
        rating: Number(movie.rating) || 0,
        price,
        priceLabel: priceLabel(price),
        poster: movie.image || movie.poster || defaultMoviePoster,
    }
}

const extractMovies = (payload) => {
    if (Array.isArray(payload)) {
        return payload
    }

    if (Array.isArray(payload?.data)) {
        return payload.data
    }

    return []
}

const formatScreeningDate = (value) => {
    const [year, month, day] = String(value || '').slice(0, 10).split('-')
    return year && month && day ? `${day}.${month}.${year}` : 'Datums nav norādīts'
}

const formatScreeningTime = (value) => String(value || 'Laiks nav norādīts').slice(0, 5)
const formatScreeningPrice = (value) => `${Number(value || 0).toFixed(2)} €`

const extractScreenings = (payload) => {
    if (Array.isArray(payload)) {
        return payload
    }

    if (Array.isArray(payload?.data)) {
        return payload.data
    }

    if (Array.isArray(payload?.screenings)) {
        return payload.screenings
    }

    return []
}

const readFreeSeatCount = (screening) => {
    const freeSeats = screening.free_seats ?? screening.freeSeats ?? screening.available_seats ?? screening.availableSeats

    return Number.isFinite(Number(freeSeats)) ? Number(freeSeats) : null
}

const normalizeScreening = (screening) => ({
    id: screening.id,
    movie: screening.movie?.title || screening.movie?.name || 'Filmas nosaukums nav pieejams',
    date: formatScreeningDate(screening.screening_date || screening.date),
    time: formatScreeningTime(screening.screening_time || screening.time),
    hall: screening.hall?.name || 'Zāle nav norādīta',
    price: formatScreeningPrice(screening.price ?? screening.cost),
    freeSeats: readFreeSeatCount(screening),
})

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

const fetchUpcomingShows = async () => {
    screeningsLoading.value = true
    screeningsError.value = ''

    try {
        const response = await fetch(`${apiBaseUrl}/api/screenings`, {
            headers: {
                Accept: 'application/json',
            },
        })

        if (!response.ok) {
            throw new Error('Neizdevās ielādēt seansus.')
        }

        const payload = await response.json()
        upcomingShows.value = extractScreenings(payload).slice(0, 4).map(normalizeScreening)
    } catch {
        upcomingShows.value = []
        screeningsError.value = 'Neizdevās ielādēt seansus.'
    } finally {
        screeningsLoading.value = false
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

onMounted(() => {
    fetchPopularMovies()
    fetchUpcomingShows()
})

</script>

<style scoped>
.home-page {
    color: #f4f6fb;
}

.hero-panel,
.movie-card,
.show-card {
    animation: subtle-fade-in 0.42s ease both;
}

.main-content {
    padding-top: 0;
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

.movie-state-text {
    color: #d6def0;
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
</style>
