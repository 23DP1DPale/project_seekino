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

                <div class="d-none d-sm-flex ga-2 mr-2">
                    <v-btn variant="text" class="text-none nav-link-btn" to="/filmas">Filmas</v-btn>
                    <v-btn variant="text" class="text-none nav-link-btn" to="/seansi">Seansi</v-btn>
                </div>

                <v-btn
                    rounded="xl"
                    class="text-none login-btn"
                    prepend-icon="mdi-account-circle-outline"
                    @click="openAuth('login')"
                >
                    Pieslēgties
                </v-btn>
            </v-container>
        </v-app-bar>

        <v-navigation-drawer
            v-model="drawer"
            temporary
            color="#101114"
            location="left"
            width="320"
            class="position-fixed app-drawer"
        >
            <div class="drawer-header pa-4">
                <p class="drawer-kicker mb-1">SEEKINO MENU</p>
                <h3 class="drawer-title mb-1">Laipni lūgts kinoteātrī</h3>
                <p class="drawer-subtitle mb-0">Ātra piekļuve filmām, seansiem un rezervācijām.</p>
            </div>

            <v-divider />

            <p class="drawer-section-label px-4 pt-4 pb-2">Navigācija</p>
            <v-list nav class="drawer-list px-2">
                <v-list-item
                    v-for="item in menuItems"
                    :key="item.title"
                    :to="item.to"
                    :prepend-icon="item.icon"
                    :title="item.title"
                    link
                    rounded="lg"
                    class="mb-1"
                    @click="drawer = false"
                />
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
                        <v-row align="center">
                            <v-col cols="12" md="7">
                                <p class="hero-badge">SEEKINO filmu katalogs</p>
                                <h1 class="hero-title mb-3">Atrodi savu nākamo kino vakaru vienā skatā</h1>
                                <p class="hero-subtitle mb-6">
                                    Izvēlies filmas pēc žanra, salīdzini vērtējumus un atrodi sev piemērotāko kino
                                    vakaru vienotā SEEKINO stilā.
                                </p>
                                <div class="d-flex flex-wrap ga-3">
                                    <v-btn color="#E50914" size="large" rounded="lg" class="text-none" to="/seansi">
                                        Skatīt seansus
                                    </v-btn>
                                </div>
                            </v-col>

                            <v-col cols="12" md="5">
                                <v-card class="movie-card hero-feature-card rounded-xl">
                                    <v-img :src="featuredMovie.poster" height="240" cover />
                                    <v-card-text>
                                        <div class="d-flex justify-space-between align-center mb-2">
                                            <h3 class="movie-title">{{ featuredMovie.title }}</h3>
                                            <v-chip size="small" class="movie-price-chip">
                                                no {{ featuredMovie.price }} EUR
                                            </v-chip>
                                        </div>
                                        <p class="text-caption movie-meta mb-2">
                                            {{ featuredMovie.director }} | {{ featuredMovie.duration }} min
                                        </p>
                                        <div class="d-flex flex-wrap ga-2 mb-3">
                                            <v-chip size="small" variant="outlined">{{ featuredMovie.genre }}</v-chip>
                                            <v-chip size="small" variant="outlined">{{ featuredMovie.ageRating }}</v-chip>
                                        </div>
                                        <v-rating
                                            :model-value="featuredMovie.rating"
                                            half-increments
                                            readonly
                                            density="compact"
                                            color="#FFD166"
                                        />
                                        <p class="mt-3 text-body-2">{{ featuredMovie.description }}</p>
                                    </v-card-text>
                                </v-card>
                            </v-col>
                        </v-row>
                    </div>
                </v-container>
            </section>

            <v-container class="py-8">
                <v-card class="filter-card pa-4 pa-md-6 rounded-xl mb-8" color="#151821">
                    <div class="d-flex align-center justify-space-between flex-wrap ga-3 mb-4">
                        <div>
                            <p class="text-overline filter-kicker mb-1">Atlase</p>
                            <h2 class="section-title mb-0">{{ filteredMovies.length }} filmas</h2>
                        </div>
                    </div>

                    <v-row>
                        <v-col cols="12" md="5">
                            <v-text-field
                                v-model="searchQuery"
                                prepend-inner-icon="mdi-magnify"
                                label="Meklēt pēc nosaukuma vai režisora"
                                variant="outlined"
                                hide-details
                            />
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-select
                                v-model="selectedGenre"
                                :items="genreOptions"
                                label="Žanrs"
                                variant="outlined"
                                hide-details
                            />
                        </v-col>
                        <v-col cols="12" sm="6" md="2">
                            <v-select
                                v-model="sortBy"
                                :items="sortOptions"
                                label="Kārtot pēc"
                                variant="outlined"
                                hide-details
                            />
                        </v-col>
                        <v-col cols="12" md="2" class="d-flex align-center">
                            <v-btn
                                block
                                variant="outlined"
                                rounded="lg"
                                class="text-none reset-filters-btn"
                                prepend-icon="mdi-refresh"
                                @click="resetFilters"
                            >
                                Notīrīt
                            </v-btn>
                        </v-col>
                    </v-row>

                    <div v-if="activeFilters.length" class="active-filters mt-4">
                        <v-chip
                            v-for="filter in activeFilters"
                            :key="filter.key"
                            size="small"
                            closable
                            variant="outlined"
                            class="active-filter-chip"
                            @click:close="removeFilter(filter.key)"
                        >
                            {{ filter.label }}
                        </v-chip>
                    </div>
                </v-card>

                <div class="d-flex align-center justify-space-between mb-4 flex-wrap ga-3">
                    <h2 class="section-title">Filmu izvēle</h2>

                </div>

                <v-row v-if="filteredMovies.length">
                    <v-col
                        v-for="movie in filteredMovies"
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
                                    <v-chip size="small" class="movie-price-chip">
                                        no {{ movie.price }} EUR
                                    </v-chip>
                                </div>
                                <p class="text-caption movie-meta mb-2">
                                    Režisors: {{ movie.director }} | {{ movie.duration }} min
                                </p>
                                <div class="d-flex flex-wrap ga-2 mb-2">
                                    <v-chip size="small" variant="outlined">{{ movie.genre }}</v-chip>
                                    <v-chip size="small" variant="outlined">{{ movie.ageRating }}</v-chip>
                                </div>
                                <v-rating
                                    :model-value="movie.rating"
                                    half-increments
                                    readonly
                                    density="compact"
                                    color="#FFD166"
                                />
                                <div class="movie-session-row mt-4">
                                    <span>Nākamais seanss: {{ movie.nextSession }}</span>
                                    <span>{{ movie.formats.join(' | ') }}</span>
                                </div>
                            </v-card-text>
                            <v-card-actions class="px-4 pb-4">
                                <v-btn
                                    color="#E50914"
                                    block
                                    rounded="lg"
                                    class="text-none reserve-btn"
                                    append-icon="mdi-arrow-right"
                                    to="/seansi"
                                >
                                    Rezervēt biļetes
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-col>
                </v-row>

                <v-card
                    v-else
                    class="empty-state-card rounded-xl pa-6 pa-md-8 text-center"
                >
                    <div class="empty-state-icon mb-4">
                        <v-icon size="34">mdi-movie-search-outline</v-icon>
                    </div>
                    <h3 class="empty-state-title mb-2">Nav atrastu filmu</h3>
                    <p class="empty-state-copy mb-5">
                        Pamēģini notīrīt filtrus vai mainīt meklēšanas frāzi, lai redzētu visu repertuāru.
                    </p>
                    <v-btn
                        color="#E50914"
                        rounded="lg"
                        class="text-none"
                        prepend-icon="mdi-refresh"
                        @click="resetFilters"
                    >
                        Notīrīt filtrus
                    </v-btn>
                </v-card>

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
import { computed, ref } from 'vue'
import { RouterLink } from 'vue-router'

const drawer = ref(false)
const searchQuery = ref('')
const selectedGenre = ref('Visi')
const sortBy = ref('Reitings')
const authDialog = ref(false)
const authMode = ref('login')
const authLoading = ref(false)
const authError = ref('')
const authSuccess = ref('')
const loginForm = ref({ email: '', password: '' })
const registerForm = ref({ name: '', email: '', password: '', confirmPassword: '' })

const menuItems = [
    { title: 'Sākums', icon: 'mdi-home-variant-outline', to: '/' },
    { title: 'Filmas', icon: 'mdi-movie-open-outline', to: '/filmas' },
    { title: 'Seansi', icon: 'mdi-calendar-clock-outline', to: '/seansi' },
    { title: 'Kontakti', icon: 'mdi-phone-outline', to: '/kontakti' },
]

const footerNavLinks = [
    { title: 'Sākums', to: '/' },
    { title: 'Filmas', to: '/filmas' },
    { title: 'Seansi', to: '/seansi' },
    { title: 'Kontakti', to: '/kontakti' },
]
const footerUserLinks = ['Mans profils', 'Rezervācijas', 'Atbalsts', 'Privātuma politika']

const socialIcons = ['mdi-facebook', 'mdi-instagram', 'mdi-youtube', 'mdi-twitter']

const movies = ref([
    {
        id: 1,
        title: 'Klusuma kods',
        director: 'Anna Bērziņa',
        genre: 'Trilleris',
        duration: 122,
        ageRating: '16+',
        language: 'LV subtitri',
        rating: 4.8,
        price: 8,
        nextSession: 'Šodien 18:30',
        formats: ['2D', 'Dolby Atmos'],
        poster: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1000&q=80',
        description: 'Žurnāliste nonāk datu noplūdes epicentrā un atklāj sazvērestību, kas skar visu pilsētu.',
    },
    {
        id: 2,
        title: 'Orbīta 9',
        director: 'Jānis Ozols',
        genre: 'Sci-Fi',
        duration: 134,
        ageRating: '13+',
        language: 'EN subtitri',
        rating: 4.9,
        price: 10,
        nextSession: 'Šodien 20:40',
        formats: ['IMAX', '3D'],
        poster: 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?auto=format&fit=crop&w=1000&q=80',
        description: 'Kosmosa ekspedīcija seko noslēpumainam signālam, kas liek pārskatīt pašas misijas mērķi.',
    },
    {
        id: 3,
        title: 'Vasaras logs',
        director: 'Māris Liepa',
        genre: 'Drāma',
        duration: 110,
        ageRating: '12+',
        language: 'Latviešu valodā',
        rating: 4.3,
        price: 7,
        nextSession: 'Rīt 17:20',
        formats: ['2D'],
        poster: 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?auto=format&fit=crop&w=1000&q=80',
        description: 'Intīms stāsts par ģimeni, kas mēģina atgūt tuvību pēc dzīves lielākajām pārmaiņām.',
    },
    {
        id: 4,
        title: 'Smieklu terapija',
        director: 'Elīna Siliņa',
        genre: 'Komēdija',
        duration: 98,
        ageRating: '7+',
        language: 'Latviešu valodā',
        rating: 4.0,
        price: 6,
        nextSession: 'Šodien 19:00',
        formats: ['2D'],
        poster: 'https://images.unsplash.com/photo-1524985069026-dd778a71c7b4?auto=format&fit=crop&w=1000&q=80',
        description: 'Divi draugi glābj teātri ar neprātīgu izrādi, kas kļūst par negaidītu sensāciju.',
    },
    {
        id: 5,
        title: 'Tumšā upe',
        director: 'Rihards Kalniņš',
        genre: 'Detektīvs',
        duration: 126,
        ageRating: '16+',
        language: 'EN subtitri',
        rating: 4.5,
        price: 9,
        nextSession: 'Rīt 21:10',
        formats: ['2D', 'VIP zāle'],
        poster: 'https://images.unsplash.com/photo-1478720568477-152d9b164e26?auto=format&fit=crop&w=1000&q=80',
        description: 'Pilsētā bez uzticības izmeklētājs meklē liecinieku, kura pazušana var mainīt visu lietu.',
    },
    {
        id: 6,
        title: 'Sniega bērni',
        director: 'Laura Vītola',
        genre: 'Ģimenes',
        duration: 95,
        ageRating: 'U',
        language: 'Dublēta latviski',
        rating: 4.2,
        price: 6,
        nextSession: 'Sestdien 12:10',
        formats: ['2D'],
        poster: 'https://images.unsplash.com/photo-1440404653325-ab127d49abc1?auto=format&fit=crop&w=1000&q=80',
        description: 'Sirsnīgs ziemas piedzīvojums par draudzību, drosmi un maziem brīnumiem lielā sniegā.',
    },
])

const genreOptions = computed(() => ['Visi', ...new Set(movies.value.map((movie) => movie.genre))])
const sortOptions = ['Reitings', 'Cena augoši', 'Cena dilstoši', 'Ilgums']
const quickGenres = computed(() => genreOptions.value.filter((genre) => genre !== 'Visi').slice(0, 4))

const filteredMovies = computed(() => {
    const query = searchQuery.value.trim().toLowerCase()

    const result = movies.value.filter((movie) => {
        const matchesQuery =
            !query ||
            movie.title.toLowerCase().includes(query) ||
            movie.director.toLowerCase().includes(query)
        const matchesGenre = selectedGenre.value === 'Visi' || movie.genre === selectedGenre.value

        return matchesQuery && matchesGenre
    })

    return result.sort((a, b) => {
        if (sortBy.value === 'Cena augoši') return a.price - b.price
        if (sortBy.value === 'Cena dilstoši') return b.price - a.price
        if (sortBy.value === 'Ilgums') return b.duration - a.duration
        return b.rating - a.rating
    })
})

const featuredMovie = computed(() => filteredMovies.value[0] || movies.value[0])
const resultsLabel = computed(() =>
    filteredMovies.value.length === 1 ? '1 filma atlasē' : `${filteredMovies.value.length} filmas atlasē`
)
const activeFilters = computed(() => {
    const filters = []

    if (searchQuery.value.trim()) {
        filters.push({ key: 'search', label: `Meklejums: ${searchQuery.value.trim()}` })
    }

    if (selectedGenre.value !== 'Visi') {
        filters.push({ key: 'genre', label: selectedGenre.value })
    }

    if (sortBy.value !== 'Reitings') {
        filters.push({ key: 'sort', label: `Kartot: ${sortBy.value}` })
    }

    return filters
})

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

const resetFilters = () => {
    searchQuery.value = ''
    selectedGenre.value = 'Visi'
    sortBy.value = 'Reitings'
}

const removeFilter = (key) => {
    if (key === 'search') searchQuery.value = ''
    if (key === 'genre') selectedGenre.value = 'Visi'
    if (key === 'sort') sortBy.value = 'Reitings'
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
    transition: color 0.16s ease, background-color 0.16s ease, border-color 0.16s ease;
}

.nav-link-btn:hover {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.16);
}

.login-btn {
    background: linear-gradient(135deg, #ff5a44, #e50914);
    color: #ffffff !important;
    font-weight: 700;
    letter-spacing: 0.01em;
    box-shadow: 0 5px 26px rgba(229, 9, 20, 0.38);
    border: 1px solid rgba(255, 255, 255, 0.24);
    transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
}

.login-btn :deep(.v-btn__content),
.login-btn :deep(.v-icon) {
    color: #ffffff !important;
}

.login-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 5px 28px rgba(229, 9, 20, 0.5);
    filter: brightness(1.07);
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
    max-width: 12ch;
    font-size: clamp(2rem, 4vw, 3.6rem);
    line-height: 1.05;
    letter-spacing: -0.02em;
}

.hero-subtitle {
    max-width: 64ch;
    color: #c1c7d8;
}

.hero-chip {
    color: #f4f6fb;
    border-color: rgba(255, 255, 255, 0.24);
}

.hero-feature-card {
    background: linear-gradient(180deg, #141926, #0f131d);
}

.section-title {
    font-size: clamp(1.2rem, 2vw, 1.8rem);
    font-weight: 700;
}

.filter-card {
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.filter-kicker {
    color: #9aa5be;
    letter-spacing: 0.08em;
}

.filter-card :deep(.v-field),
.filter-card :deep(.v-label),
.filter-card :deep(.v-field__input),
.filter-card :deep(.v-select__selection-text),
.filter-card :deep(.v-icon) {
    color: #f4f6fb;
}

.filter-card :deep(.v-label.v-field-label) {
    opacity: 0.85;
}

.reset-filters-btn {
    color: #f4f6fb;
    border-color: rgba(255, 255, 255, 0.2);
}

.active-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.active-filter-chip {
    color: #d7dff2;
    border-color: rgba(255, 255, 255, 0.22);
    background: rgba(255, 255, 255, 0.04);
}

.results-chip {
    font-weight: 700;
}

.results-copy {
    color: #a9b4cb;
    font-size: 0.94rem;
}

.movie-card {
    border: 1px solid rgba(255, 255, 255, 0.09);
    background: linear-gradient(180deg, #141926, #0f131d);
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.movie-card :deep(.v-card-text),
.movie-card :deep(.v-chip) {
    color: #f4f6fb;
}

.movie-card:hover {
    transform: translateY(-6px) scale(1.01);
    box-shadow: 0 18px 36px rgba(0, 0, 0, 0.28);
    border-color: rgba(255, 255, 255, 0.18);
}

.movie-title {
    font-size: 1.1rem;
    line-height: 1.2;
}

.movie-meta {
    color: #ffffff;
}

.movie-price-chip {
    background: linear-gradient(135deg, #24b26b, #149e59);
    color: #ffffff;
    font-weight: 700;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 20px rgba(23, 167, 95, 0.35);
}

.movie-session-row {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    font-size: 0.86rem;
    color: #c1c7d8;
}

.reserve-btn {
    background: linear-gradient(135deg, #ff3b30, #e50914);
    color: #ffffff !important;
    font-weight: 700;
    letter-spacing: 0.02em;
    box-shadow: 0 10px 24px rgba(229, 9, 20, 0.4);
    transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
}

.reserve-btn :deep(.v-btn__content),
.reserve-btn :deep(.v-icon) {
    color: #ffffff !important;
}

.reserve-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 14px 28px rgba(229, 9, 20, 0.5);
    filter: brightness(1.08);
}

.movie-card:hover .reserve-btn {
    box-shadow: 0 16px 30px rgba(229, 9, 20, 0.58);
    filter: brightness(1.1);
}

.drawer-list :deep(.v-list-item-title) {
    color: #f4f6fb;
}

.app-drawer {
    border-right: 1px solid rgba(255, 255, 255, 0.12);
}

.drawer-header {
    background: linear-gradient(160deg, rgba(36, 53, 88, 0.65), rgba(94, 27, 46, 0.45));
}

.drawer-kicker {
    color: #d9e3ff;
    letter-spacing: 1px;
    font-size: 0.72rem;
}

.drawer-title {
    color: #ffffff;
    font-size: 1rem;
}

.drawer-subtitle {
    color: #c1c7d8;
    font-size: 0.82rem;
}

.drawer-section-label {
    color: #9aa5be;
    font-size: 0.75rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.drawer-list :deep(.v-list-item) {
    min-height: 44px;
}

.site-footer {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    margin-top: 56px;
    border-top: 1px solid rgba(255, 255, 255, 0.12);
    background:
        linear-gradient(180deg, rgba(14, 16, 24, 0.95), rgba(9, 11, 16, 1)),
        radial-gradient(circle at 15% 0%, rgba(51, 76, 126, 0.2), transparent 40%);
}

.footer-text {
    color: #b6bfd4;
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
}

.footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(6, 8, 12, 0.7);
    color: #9ea8bf;
    font-size: 0.82rem;
    text-align: center;
}

.empty-state-card {
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: linear-gradient(180deg, #141926, #0f131d);
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
    color: #b6bfd4;
}

@media (max-width: 960px) {
    .movie-session-row {
        flex-direction: column;
    }
}

@media (max-width: 600px) {
    .hero-title {
        max-width: 100%;
    }
}
</style>
