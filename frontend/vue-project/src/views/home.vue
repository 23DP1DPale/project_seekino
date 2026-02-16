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

                <v-img
                    src="/img/logo_seekino.png"
                    max-width="160"
                    height="52"
                    class="ml-2 logo"
                />

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
                    :to="item.to || undefined"
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
                        <p class="hero-badge">SEEKINO Kino apmeklējumu un biļešu tiešsaistes rezervēšanas sistēma</p>
                        <h1 class="hero-title mb-3">Rezervē biļetes, atrodi filmas un dalies atsauksmēs</h1>
                        <p class="hero-subtitle mb-6">
                            Vienuviet pieejami seansi, sēdvietu izvēle, žanru un cenu filtri, kā arī lietotāju vērtējumi.
                        </p>
                        <div class="d-flex flex-wrap ga-3">
                            <v-btn color="#E50914" size="large" rounded="lg" class="text-none">Sākt rezervāciju</v-btn>
                        </div>
                    </div>
                </v-container>
            </section>

            <v-container class="py-8">
                <v-card class="filter-card pa-4 pa-md-6 rounded-xl mb-8" color="#151821">
                    <v-row>
                        <v-col cols="12" md="4">
                            <v-text-field
                                v-model="searchQuery"
                                prepend-inner-icon="mdi-magnify"
                                label="Meklēt pēc nosaukuma vai režisora"
                                variant="outlined"
                                hide-details
                            />
                        </v-col>
                        <v-col cols="12" sm="6" md="2">
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
                                v-model="selectedAge"
                                :items="ageOptions"
                                label="Vecuma ierobežojums"
                                variant="outlined"
                                hide-details
                            />
                        </v-col>
                        <v-col cols="12" md="2">
                            <v-select
                                v-model="sortBy"
                                :items="sortOptions"
                                label="Kārtot pēc"
                                variant="outlined"
                                hide-details
                            />
                        </v-col>
                        <v-col cols="12" md="2">
                            <div class="text-caption mb-2">Cena: {{ priceRange[0] }}€ - {{ priceRange[1] }}€</div>
                            <v-range-slider
                                v-model="priceRange"
                                :min="6"
                                :max="25"
                                :step="1"
                                strict
                                hide-details
                                color="#E50914"
                            />
                        </v-col>
                    </v-row>
                </v-card>

                <div class="d-flex align-center justify-space-between mb-4">
                    <h2 class="section-title">Populārākās filmas</h2>
                    <v-chip color="#E50914" variant="flat">{{ filteredMovies.length }} atrastas</v-chip>
                </div>

                <v-row>
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
                                        no {{ movie.minPrice }}€
                                    </v-chip>
                                </div>
                                <p class="text-caption movie-meta mb-2">
                                    Režisors: {{ movie.director }} | {{ movie.length }} min
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
                                <p class="mt-2 text-body-2">{{ movie.description }}</p>
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

const drawer = ref(false)
const searchQuery = ref('')
const selectedGenre = ref('Visi')
const selectedAge = ref('Visi')
const sortBy = ref('Reitings')
const priceRange = ref([6, 25])
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
    { title: 'Biļešu rezervēšana', icon: 'mdi-ticket-confirmation-outline', to: '' },
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
        length: 122,
        ageRating: '16+',
        rating: 4.5,
        minPrice: 8,
        description: 'Kibertrilleris par žurnālisti, kura atklāj bīstamu datu noplūdi.',
        poster: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1000&q=80',
    },
    {
        id: 2,
        title: 'Vasaras logs',
        director: 'Māris Liepa',
        genre: 'Drāma',
        length: 110,
        ageRating: '12+',
        rating: 4.1,
        minPrice: 7,
        description: 'Dziļš stāsts par ģimeni, kas mēģina sākt dzīvi no jauna.',
        poster: 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?auto=format&fit=crop&w=1000&q=80',
    },
    {
        id: 3,
        title: 'Orbīta 9',
        director: 'Jānis Ozols',
        genre: 'Sci-Fi',
        length: 134,
        ageRating: '13+',
        rating: 4.8,
        minPrice: 10,
        description: 'Kosmosa ekspedīcija, kuras laikā apkalpe atrod noslēpumainu signālu.',
        poster: 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?auto=format&fit=crop&w=1000&q=80',
    },
    {
        id: 4,
        title: 'Smieklu terapija',
        director: 'Elīna Siliņa',
        genre: 'Komēdija',
        length: 98,
        ageRating: '7+',
        rating: 3.9,
        minPrice: 6,
        description: 'Divi draugi cenšas glābt savu teātri ar negaidītu izrādi.',
        poster: 'https://images.unsplash.com/photo-1524985069026-dd778a71c7b4?auto=format&fit=crop&w=1000&q=80',
    },
    {
        id: 5,
        title: 'Tumšā upe',
        director: 'Rihards Kalniņš',
        genre: 'Detektīvs',
        length: 126,
        ageRating: '16+',
        rating: 4.3,
        minPrice: 9,
        description: 'Izmeklētājs meklē pazudušu liecinieku pilsētā bez uzticības.',
        poster: 'https://images.unsplash.com/photo-1478720568477-152d9b164e26?auto=format&fit=crop&w=1000&q=80',
    },
    {
        id: 6,
        title: 'Sniega bērni',
        director: 'Laura Vītola',
        genre: 'Ģimenes',
        length: 95,
        ageRating: 'U',
        rating: 4.0,
        minPrice: 6,
        description: 'Piedzīvojums bērniem par draudzību un drosmi ziemeļu ciematā.',
        poster: 'https://images.unsplash.com/photo-1440404653325-ab127d49abc1?auto=format&fit=crop&w=1000&q=80',
    },
])

const upcomingShows = ref([
    { id: 1, movie: 'Klusuma kods', date: '17.02.2026', time: '18:30', price: 8, hall: '1.', freeSeats: 42 },
    { id: 2, movie: 'Orbīta 9', date: '17.02.2026', time: '20:40', price: 10, hall: 'IMAX', freeSeats: 16 },
    { id: 3, movie: 'Vasaras logs', date: '18.02.2026', time: '17:20', price: 7, hall: '2.', freeSeats: 58 },
    { id: 4, movie: 'Smieklu terapija', date: '18.02.2026', time: '19:00', price: 6, hall: '3.', freeSeats: 64 },
])

const genreOptions = computed(() => ['Visi', ...new Set(movies.value.map((movie) => movie.genre))])
const ageOptions = computed(() => ['Visi', ...new Set(movies.value.map((movie) => movie.ageRating))])
const sortOptions = ['Reitings', 'Cena augoši', 'Cena dilstoši', 'Ilgums']

const filteredMovies = computed(() => {
    const query = searchQuery.value.trim().toLowerCase()

    const result = movies.value.filter((movie) => {
        const matchesQuery =
            !query ||
            movie.title.toLowerCase().includes(query) ||
            movie.director.toLowerCase().includes(query)
        const matchesGenre = selectedGenre.value === 'Visi' || movie.genre === selectedGenre.value
        const matchesAge = selectedAge.value === 'Visi' || movie.ageRating === selectedAge.value
        const matchesPrice = movie.minPrice >= priceRange.value[0] && movie.minPrice <= priceRange.value[1]

        return matchesQuery && matchesGenre && matchesAge && matchesPrice
    })

    return result.sort((a, b) => {
        if (sortBy.value === 'Cena augoši') return a.minPrice - b.minPrice
        if (sortBy.value === 'Cena dilstoši') return b.minPrice - a.minPrice
        if (sortBy.value === 'Ilgums') return b.length - a.length
        return b.rating - a.rating
    })
})

const displayedMovies = computed(() => filteredMovies.value.slice(0, 6))
const featuredShows = computed(() => upcomingShows.value.slice(0, 4))

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
    max-width: 16ch;
    font-size: clamp(2rem, 4vw, 3.6rem);
    line-height: 1.05;
    letter-spacing: -0.02em;
}

.hero-subtitle {
    max-width: 64ch;
    color: #c1c7d8;
}

.section-title {
    font-size: clamp(1.2rem, 2vw, 1.8rem);
    font-weight: 700;
}

.filter-card {
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.movie-card {
    border: 1px solid rgba(255, 255, 255, 0.09);
    background: linear-gradient(180deg, #141926, #0f131d);
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

.movie-card :deep(.v-card-text),
.movie-card :deep(.v-chip) {
    color: #f4f6fb;
}

.show-card {
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: linear-gradient(180deg, #161b29, #111522);
}

.show-movie {
    font-size: 1.02rem;
    line-height: 1.3;
    color: #ffffff;
}

.show-meta {
    display: flex;
    align-items: center;
    color: #cfd8ef;
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
    margin-top: 56px;
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
    color: #b6bfd4;
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
}

.footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(6, 8, 12, 0.7);
    color: #9ea8bf;
    font-size: 0.82rem;
}
</style>