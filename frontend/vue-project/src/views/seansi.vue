<template>
    <div class="screenings-page">

        <v-main class="main-content">
            <section class="hero-section">
                <v-container class="py-8 py-md-10">
                    <div class="hero-panel pa-5 pa-md-7">
                        <p class="hero-badge">SEEKINO seansi</p>
                        <h1 class="hero-title mb-3">Izvēlies piemērotāko seansu</h1>
                        <p class="hero-subtitle mb-0">Pārlūko pieejamos nākotnes seansus, atrodi filmu un rezervē vietas dažu klikšķu laikā.</p>
                    </div>
                </v-container>
            </section>

            <v-container class="py-8">
                <v-card class="filter-card pa-4 pa-md-6 rounded-xl mb-8" color="#151821">
                    <div class="d-flex align-center justify-space-between flex-wrap ga-3 mb-4">
                        <div>
                            <p class="text-overline filter-kicker mb-1">Atlase</p>
                            <h2 class="section-title mb-0">{{ filteredScreenings.length }} seansi</h2>
                        </div>
                        <v-btn v-if="hasActiveFilters" variant="text" rounded="pill" class="text-none reset-filters-inline" prepend-icon="mdi-filter-remove-outline" @click="resetFilters">
                            Notīrīt filtrus
                        </v-btn>
                    </div>
                    <v-row>
                        <v-col cols="12" md="5">
                            <v-text-field v-model="searchQuery" prepend-inner-icon="mdi-magnify" label="Meklēt pēc filmas nosaukuma" variant="outlined" hide-details />
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-select v-model="selectedHall" :items="hallOptions" label="Zāle" variant="outlined" hide-details />
                        </v-col>
                        <v-col cols="12" sm="6" md="4">
                            <v-select v-model="sortBy" :items="sortOptions" label="Kārtot pēc" variant="outlined" hide-details />
                        </v-col>
                    </v-row>
                </v-card>

                <v-card v-if="screeningsLoading" class="state-card rounded-xl pa-6 pa-md-8 text-center">
                    <v-progress-circular indeterminate color="#E50914" size="44" class="mb-4" />
                    <h3 class="state-title mb-2">Ielādē seansus...</h3>
                </v-card>

                <v-alert v-else-if="screeningsError" type="error" variant="tonal" class="state-card rounded-xl mb-6">
                    <div class="d-flex align-center justify-space-between flex-wrap ga-3">
                        <span>{{ screeningsError }}</span>
                        <v-btn color="#E50914" rounded="lg" class="text-none" @click="fetchScreenings">Mēģināt vēlreiz</v-btn>
                    </div>
                </v-alert>

                <v-alert v-else-if="screenings.length === 0" type="info" variant="tonal" class="state-card rounded-xl mb-6">
                    Šobrīd nav pieejamu seansu.
                </v-alert>

                <v-card v-else-if="filteredScreenings.length === 0" class="state-card rounded-xl pa-6 pa-md-8 text-center">
                    <div class="state-icon mb-4"><v-icon size="34">mdi-calendar-search-outline</v-icon></div>
                    <h3 class="state-title mb-2">Nav atrastu seansu</h3>
                    <p class="state-copy mb-5">Pamēģini citu filmas nosaukumu vai zāles filtru.</p>
                    <v-btn color="#E50914" rounded="lg" class="text-none" prepend-icon="mdi-refresh" @click="resetFilters">Notīrīt filtrus</v-btn>
                </v-card>

                <v-row v-else>
                    <v-col v-for="screening in filteredScreenings" :key="screening.id" cols="12" sm="6" lg="4">
                        <v-card class="screening-card h-100 rounded-xl pa-4">
                            <div class="d-flex align-center justify-space-between ga-3 mb-4">
                                <v-chip size="small" color="#E50914" variant="flat">{{ screening.date }}</v-chip>
                                <v-chip size="small" variant="outlined" class="price-chip">{{ screening.price }}</v-chip>
                            </div>
                            <h3 class="screening-title mb-4">{{ screening.movieTitle }}</h3>
                            <div class="screening-meta">
                                <p class="mb-2"><v-icon size="18" class="mr-2">mdi-clock-outline</v-icon>{{ screening.time }}</p>
                                <p class="mb-0">
                                    <v-icon size="18" class="mr-2">mdi-sofa-outline</v-icon>{{ screening.hallName }}
                                    <template v-if="screening.availableSeats !== null">
                                        | {{ screening.availableSeats }} brīvas vietas
                                    </template>
                                </p>
                            </div>
                            <v-btn color="#E50914" block rounded="lg" class="text-none mt-5" :to="`/reservation/${screening.id}?from=seansi`">
                                Rezervēt biļeti
                            </v-btn>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
        </v-main>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuth } from '@/services/auth'

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000'
const drawer = ref(false)
const { user, isAuthenticated, authLoading: navAuthLoading, logout } = useAuth()
const screenings = ref([])
const screeningsLoading = ref(false)
const screeningsError = ref('')
const searchQuery = ref('')
const selectedHall = ref('Visas zāles')
const sortBy = ref('Datums un laiks')
const authDialog = ref(false)
const authMode = ref('login')
const authLoading = ref(false)
const authError = ref('')
const authSuccess = ref('')
const loginForm = ref({ email: '', password: '' })
const registerForm = ref({ name: '', email: '', password: '', confirmPassword: '' })

const menuGroups = [
    { title: 'Kino', items: [
        { title: 'Sākums', icon: 'mdi-home-variant-outline', to: '/' },
        { title: 'Filmas', icon: 'mdi-movie-open-outline', to: '/filmas' },
        { title: 'Seansi', icon: 'mdi-calendar-clock-outline', to: '/seansi' },
    ] },
    { title: 'Lietotājs', isLast: true, items: [
        { title: 'Mans profils', icon: 'mdi-account-outline', to: '/profile' },
        { title: 'Manas rezervācijas', icon: 'mdi-ticket-confirmation-outline', to: '/profile' },
        { title: 'Kontakti', icon: 'mdi-phone-outline', to: '/kontakti' },
    ] },
]

const sortOptions = ['Datums un laiks', 'Datums un laiks: dilstoši', 'Cena: augoši', 'Cena: dilstoši']
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
const numericValue = (value) => Number.isFinite(Number(value)) ? Number(value) : null
const formatDate = (value) => {
    const [year, month, day] = String(value || '').slice(0, 10).split('-')
    return year && month && day ? `${day}.${month}.${year}` : 'Datums nav norādīts'
}
const formatTime = (value) => String(value || 'Laiks nav norādīts').slice(0, 5)
const formatPrice = (value) => `${Number(value || 0).toFixed(2)} €`
const screeningTimestamp = (date, time) => new Date(`${date || '1970-01-01'}T${String(time || '00:00').slice(0, 5)}:00`).getTime()
const extractScreenings = (payload) => Array.isArray(payload) ? payload : Array.isArray(payload?.data) ? payload.data : Array.isArray(payload?.screenings) ? payload.screenings : []
const normalizeScreening = (screening) => {
    const rawDate = screening.screening_date || screening.date || ''
    const rawTime = screening.screening_time || screening.time || ''
    const priceValue = numericValue(screening.price ?? screening.cost)
    const availableSeats = numericValue(screening.available_seats ?? screening.availableSeats)
    return {
        id: screening.id,
        movieTitle: screening.movie?.title || screening.movie?.name || 'Filmas nosaukums nav pieejams',
        hallName: screening.hall?.name || 'Zāle nav norādīta',
        date: formatDate(rawDate),
        time: formatTime(rawTime),
        priceValue,
        price: formatPrice(priceValue),
        availableSeats,
        timestamp: screeningTimestamp(rawDate, rawTime),
    }
}

const fetchScreenings = async () => {
    screeningsLoading.value = true
    screeningsError.value = ''
    try {
        const response = await fetch(`${apiBaseUrl}/api/screenings`, { headers: { Accept: 'application/json' } })
        if (!response.ok) throw new Error('Neizdevās ielādēt seansus.')
        screenings.value = extractScreenings(await response.json()).map(normalizeScreening)
    } catch (error) {
        screenings.value = []
        screeningsError.value = error.message || 'Neizdevās ielādēt seansus.'
    } finally {
        screeningsLoading.value = false
    }
}

const hallOptions = computed(() => ['Visas zāles', ...new Set(screenings.value.map((screening) => screening.hallName).filter(Boolean))])
const filteredScreenings = computed(() => {
    const query = searchQuery.value.trim().toLowerCase()
    const result = screenings.value.filter((screening) => {
        const matchesMovie = !query || screening.movieTitle.toLowerCase().includes(query)
        const matchesHall = selectedHall.value === 'Visas zāles' || screening.hallName === selectedHall.value
        return matchesMovie && matchesHall
    })
    return result.sort((left, right) => {
        if (sortBy.value === 'Datums un laiks: dilstoši') return right.timestamp - left.timestamp
        if (sortBy.value === 'Cena: augoši') return (left.priceValue ?? Number.POSITIVE_INFINITY) - (right.priceValue ?? Number.POSITIVE_INFINITY)
        if (sortBy.value === 'Cena: dilstoši') return (right.priceValue ?? Number.NEGATIVE_INFINITY) - (left.priceValue ?? Number.NEGATIVE_INFINITY)
        return left.timestamp - right.timestamp
    })
})
const hasActiveFilters = computed(() => Boolean(searchQuery.value.trim()) || selectedHall.value !== 'Visas zāles' || sortBy.value !== 'Datums un laiks')
const resetFilters = () => { searchQuery.value = ''; selectedHall.value = 'Visas zāles'; sortBy.value = 'Datums un laiks' }
const isEmailValid = (value) => /^\S+@\S+\.\S+$/.test(value)
const closeAuth = () => { authDialog.value = false; authLoading.value = false }
const switchAuth = (mode) => { authMode.value = mode; authError.value = ''; authSuccess.value = '' }
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
        if (!registerForm.value.name || !registerForm.value.email || !registerForm.value.password || !registerForm.value.confirmPassword) {
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
    authSuccess.value = authMode.value === 'login'
        ? 'Pieslēgšanās forma gatava. Nākamais solis: savienot ar Laravel API.'
        : 'Reģistrācijas forma gatava. Nākamais solis: savienot ar Laravel API.'
}
onMounted(fetchScreenings)
</script>

<style scoped>
.screenings-page { color: #f4f6fb; }
.sticky-app-bar { position: sticky !important; top: 0; z-index: 1100; }
.app-bar-shell { border-bottom: 1px solid rgba(255, 255, 255, 0.12); background: rgba(11, 14, 22, 0.82) !important; backdrop-filter: blur(10px); }
.app-bar-inner { min-height: 76px; }
.main-content { padding-top: 0; }
.logo { filter: invert(1); }
.brand-link { display: inline-flex; align-items: center; text-decoration: none; width: 160px; min-width: 160px; }
.brand-logo { width: 160px; flex: 0 0 160px; }
.nav-btn { color: #f4f6fb; }
.nav-link-btn { color: #d7dff2; border: 1px solid transparent; }
.nav-pages { display: none; }
@media (min-width: 700px) { .nav-pages { display: flex; } }
.login-btn { margin-left: 5px; background: linear-gradient(135deg, #ff5a44, #e50914); color: #ffffff !important; font-weight: 700; border: 1px solid rgba(255, 255, 255, 0.24); }
.auth-dialog-card { border: 1px solid rgba(255, 255, 255, 0.14); background: linear-gradient(165deg, #121724, #10131c); color: #edf2ff; }
.auth-dialog-card :deep(.v-field), .auth-dialog-card :deep(.v-label), .auth-dialog-card :deep(.v-field__input), .auth-dialog-card :deep(.v-icon), .auth-dialog-card :deep(.v-card-title), .auth-dialog-card :deep(.v-card-text), .auth-dialog-card :deep(.v-btn), .auth-dialog-card :deep(.v-alert__content), .auth-dialog-card :deep(.text-caption) { color: #edf2ff; }
.auth-switch-link { color: #ff5a44; text-decoration: none; font-weight: 600; }
.hero-section { background: radial-gradient(circle at 12% 18%, rgba(68, 111, 203, 0.34), transparent 42%), radial-gradient(circle at 82% 14%, rgba(220, 54, 88, 0.3), transparent 38%), linear-gradient(130deg, #0f1628 0%, #17172a 45%, #2a141d 100%); }
.hero-panel, .filter-card, .screening-card, .state-card { border: 1px solid rgba(255, 255, 255, 0.1); background: linear-gradient(180deg, #141926, #0f131d); }
.hero-panel { border-radius: 24px; }
.hero-badge { display: inline-block; margin-bottom: 10px; padding: 6px 12px; border: 1px solid rgba(255, 255, 255, 0.25); border-radius: 999px; font-size: 12px; color: #d7e2ff; }
.hero-title { max-width: 13ch; font-size: clamp(2rem, 4vw, 3.6rem); line-height: 1.05; }
.hero-subtitle, .state-copy { max-width: 64ch; color: #d2d9e7; }
.section-title { font-size: clamp(1.2rem, 2vw, 1.8rem); font-weight: 700; }
.filter-kicker { color: #acb7cf; }
.filter-card :deep(.v-field), .filter-card :deep(.v-label), .filter-card :deep(.v-field__input), .filter-card :deep(.v-select__selection-text), .filter-card :deep(.v-icon) { color: #f4f6fb; }
.reset-filters-inline { color: #ff5a44; font-weight: 700; }
.screening-card { transition: transform 0.2s ease, border-color 0.2s ease; }
.screening-card:hover { transform: translateY(-3px); border-color: rgba(255, 255, 255, 0.18); }
.screening-title { min-height: 3.2rem; font-size: 1.25rem; line-height: 1.3; color: #f4f6fb; }
.screening-meta { color: #d7dff2; }
.price-chip { color: #f4f6fb; border-color: rgba(255, 255, 255, 0.24); }
.state-title { color: #ffffff; font-size: 1.15rem; font-weight: 700; }
.state-icon { width: 58px; height: 58px; margin-inline: auto; display: grid; place-items: center; border-radius: 16px; background: rgba(229, 9, 20, 0.14); color: #ff7a70; }
.site-footer { background: rgba(8, 10, 16, 0.96); border-top: 1px solid rgba(255, 255, 255, 0.1); }
.footer-text { color: #c2ccdf; }
.footer-heading { color: #ffffff; }
.footer-list { background: transparent; }
.footer-list :deep(.v-list-item-title) { color: #d7dff2; }
.footer-social-btn { color: #d7dff2; border-color: rgba(255, 255, 255, 0.18); }
.footer-bottom { color: #aeb8cc; border-top: 1px solid rgba(255, 255, 255, 0.08); background: rgba(255, 255, 255, 0.02); }
</style>
