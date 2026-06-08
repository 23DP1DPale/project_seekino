<template>
    <div class="home-page">

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
                                    <v-chip size="small" class="movie-price-chip">
                                        {{ movie.priceLabel }}
                                    </v-chip>
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
                                        :disabled="!movie.screenings.length"
                                        @click="scrollToScreenings"
                                    >
                                        {{ movie.screenings.length ? 'Izvēlēties seansu' : 'Seansi nav pieejami' }}
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
                                    <template v-if="screening.availableSeats !== null">
                                        | {{ screening.availableSeats }} brīvas vietas
                                    </template>
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

            <section v-if="movie && !movieLoading && !movieError" class="feedbacks-section">
                <v-container class="pt-2 pb-10">
                    <div class="d-flex align-center justify-space-between mb-4 flex-wrap ga-3">
                        <h2 class="section-title mb-0">Atsauksmes</h2>
                        <v-chip size="small" variant="outlined" class="detail-chip" prepend-icon="mdi-star-outline">
                            Vidējais vērtējums: {{ movie.rating || 'Nav vērtējumu' }}
                        </v-chip>
                    </div>

                    <v-row>
                        <v-col cols="12" md="5">
                            <v-card class="feedback-form-card rounded-xl pa-4 pa-md-5">
                                <h3 class="feedback-card-title mb-3">Pievieno atsauksmi</h3>

                                <template v-if="isAuthenticated">
                                    <v-alert
                                        v-if="feedbackSuccess"
                                        type="success"
                                        variant="tonal"
                                        density="comfortable"
                                        class="mb-3"
                                    >
                                        {{ feedbackSuccess }}
                                    </v-alert>

                                    <v-alert
                                        v-if="feedbackError"
                                        type="error"
                                        variant="tonal"
                                        density="comfortable"
                                        class="mb-3"
                                    >
                                        {{ feedbackError }}
                                    </v-alert>

                                    <v-form @submit.prevent="submitFeedback">
                                        <div class="mb-3">
                                            <span class="fact-label">Vērtējums</span>
                                            <v-rating
                                                v-model="feedbackForm.rating"
                                                color="#FFD166"
                                                hover
                                                density="comfortable"
                                                length="5"
                                                :disabled="feedbackSubmitting"
                                            />
                                        </div>

                                        <v-textarea
                                            v-model="feedbackForm.comment"
                                            label="Atsauksme"
                                            variant="outlined"
                                            rows="5"
                                            counter="1000"
                                            maxlength="1000"
                                            :disabled="feedbackSubmitting"
                                            class="feedback-textarea mb-3"
                                        />

                                        <v-btn
                                            type="submit"
                                            color="#E50914"
                                            rounded="lg"
                                            class="text-none reserve-btn"
                                            :loading="feedbackSubmitting"
                                            :disabled="feedbackSubmitting"
                                        >
                                            Pievienot atsauksmi
                                        </v-btn>
                                    </v-form>
                                </template>

                                <template v-else>
                                    <p class="detail-muted mb-4">Lai pievienotu atsauksmi, lūdzu, pieslēdzies.</p>
                                    <v-btn rounded="lg" class="text-none login-btn" :to="loginRedirectRoute">
                                        Pieslēgties
                                    </v-btn>
                                </template>
                            </v-card>
                        </v-col>

                        <v-col cols="12" md="7">
                            <div v-if="feedbacksLoading" class="feedback-state text-center py-8">
                                <v-progress-circular indeterminate color="#E50914" class="mb-3" />
                                <p class="detail-muted mb-0">Atsauksmes tiek ielādētas...</p>
                            </div>

                            <v-alert v-else-if="feedbacksError" type="error" variant="tonal" class="mb-4">
                                <div class="d-flex align-center justify-space-between flex-wrap ga-3">
                                    <span>{{ feedbacksError }}</span>
                                    <v-btn variant="outlined" rounded="lg" class="text-none" @click="fetchFeedbacks">
                                        Mēģināt vēlreiz
                                    </v-btn>
                                </div>
                            </v-alert>

                            <v-card v-else-if="feedbacks.length === 0" class="empty-state-card rounded-xl pa-6 text-center">
                                <p class="empty-state-copy mb-0">{{ feedbacksMessage || 'Šai filmai vēl nav atsauksmju.' }}</p>
                            </v-card>

                            <div v-else class="feedback-list">
                                <v-card
                                    v-for="feedback in feedbacks"
                                    :key="feedback.id"
                                    class="feedback-card rounded-xl pa-4 mb-3"
                                >
                                    <div class="d-flex align-start justify-space-between flex-wrap ga-3 mb-2">
                                        <div>
                                            <strong class="feedback-author">{{ feedback.userName }}</strong>
                                            <p class="feedback-date mb-0">{{ feedback.createdAt }}</p>
                                        </div>
                                        <div class="d-flex align-center ga-2">
                                            <v-rating
                                                :model-value="feedback.rating"
                                                half-increments
                                                readonly
                                                density="compact"
                                                color="#FFD166"
                                            />
                                            <strong class="feedback-rating">{{ feedback.rating }}</strong>
                                        </div>
                                    </div>
                                    <p class="feedback-comment mb-0">{{ feedback.comment }}</p>
                                </v-card>
                            </div>
                        </v-col>
                    </v-row>
                </v-container>
            </section>
        </v-main>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useAuth } from '@/services/auth'

const route = useRoute()
const { token, isAuthenticated } = useAuth()
const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000'
const movie = ref(null)
const movieLoading = ref(false)
const movieError = ref('')
const feedbacks = ref([])
const feedbacksLoading = ref(false)
const feedbacksError = ref('')
const feedbacksMessage = ref('')
const feedbackSubmitting = ref(false)
const feedbackError = ref('')
const feedbackSuccess = ref('')
const feedbackForm = ref({ rating: 5, comment: '' })
const selectedScreeningId = ref(null)
const screeningsSection = ref(null)

const movieId = computed(() => route.params.id)
const loginRedirectRoute = computed(() => ({
    path: '/login',
    query: {
        redirect: route.fullPath,
    },
}))
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

const formatReviewDate = (value) => {
    if (!value) return 'Datums nav norādīts'

    return formatDateTime(value)
}

const formatPrice = (value) => {
    if (value === null || value === undefined || value === '') return '-'

    return `${Number(value).toFixed(2)} €`
}

const normalizePriceValue = (value) => {
    const price = Number(value)

    return Number.isFinite(price) ? price : null
}

const normalizeCountValue = (value) => {
    const count = Number(value)

    return Number.isFinite(count) ? count : null
}

const normalizeScreening = (screening) => {
    const priceValue = normalizePriceValue(screening.price ?? screening.cost)
    const availableSeats = normalizeCountValue(screening.available_seats ?? screening.availableSeats)

    return {
        id: screening.id,
        date: formatDate(screening.screening_date || screening.date || screening.datetime?.slice(0, 10)),
        time: formatTime(screening.screening_time || screening.time || screening.datetime?.slice(11, 16)),
        price: formatPrice(priceValue),
        priceValue,
        availableSeats,
        hall: screening.hall?.name || (typeof screening.hall === 'string' ? screening.hall : 'Zāle nav norādīta'),
    }
}

const normalizeMovie = (payload) => {
    const genres = Array.isArray(payload.genres)
        ? payload.genres.map((genre) => genre.name || genre).filter(Boolean)
        : [payload.genre].filter(Boolean)
    const screenings = Array.isArray(payload.screenings) ? payload.screenings.map(normalizeScreening) : []
    const screeningPrices = screenings
        .map((screening) => screening.priceValue)
        .filter((price) => price !== null)
    const lowestScreeningPrice = screeningPrices.length ? Math.min(...screeningPrices) : null
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
        price: lowestScreeningPrice === null ? null : formatPrice(lowestScreeningPrice),
        priceLabel: lowestScreeningPrice === null ? 'Cena nav pieejama' : `no ${formatPrice(lowestScreeningPrice)}`,
        poster: payload.image || payload.poster || '',
        nextScreeningLabel: formatDateTime(nextScreening),
        screenings,
    }
}

const normalizeFeedback = (feedback) => ({
    id: feedback.id,
    rating: Number(feedback.rating || 0),
    comment: feedback.comment || feedback.feedback || '',
    userName: feedback.user?.nickname || feedback.user?.name || feedback.nickname || 'Lietotājs',
    createdAt: formatReviewDate(feedback.created_at),
})

const feedbackResponseError = (payload) => {
    if (payload?.kļūdas && typeof payload.kļūdas === 'object') {
        const firstError = Object.values(payload.kļūdas).flat().find(Boolean)

        if (firstError) {
            return firstError
        }
    }

    return payload?.ziņa || payload?.message || ''
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

const fetchFeedbacks = async () => {
    feedbacksLoading.value = true
    feedbacksError.value = ''
    feedbacksMessage.value = ''

    try {
        const response = await fetch(`${apiBaseUrl}/api/movies/${movieId.value}/feedbacks`, {
            headers: {
                Accept: 'application/json',
            },
        })
        const data = await response.json().catch(() => ({}))

        if (!response.ok) {
            throw new Error(data.ziņa || data.message || 'Atsauksmes neizdevās ielādēt.')
        }

        feedbacks.value = (data.feedbacks || []).map(normalizeFeedback)
        feedbacksMessage.value = data.ziņa || ''
    } catch (error) {
        feedbacks.value = []
        feedbacksError.value = error.message || 'Atsauksmes neizdevās ielādēt.'
    } finally {
        feedbacksLoading.value = false
    }
}

const submitFeedback = async () => {
    feedbackError.value = ''
    feedbackSuccess.value = ''

    if (!feedbackForm.value.rating || feedbackForm.value.rating < 1 || feedbackForm.value.rating > 5) {
        feedbackError.value = 'Vērtējumam jābūt no 1 līdz 5.'
        return
    }

    if (!feedbackForm.value.comment.trim()) {
        feedbackError.value = 'Atsauksmes teksts ir obligāts.'
        return
    }

    if (feedbackForm.value.comment.length > 1000) {
        feedbackError.value = 'Atsauksme nedrīkst pārsniegt 1000 rakstzīmes.'
        return
    }

    feedbackSubmitting.value = true

    try {
        const response = await fetch(`${apiBaseUrl}/api/movies/${movieId.value}/feedbacks`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                Authorization: `Bearer ${token.value}`,
            },
            body: JSON.stringify({
                rating: feedbackForm.value.rating,
                comment: feedbackForm.value.comment.trim(),
            }),
        })
        const data = await response.json().catch(() => ({}))

        if (!response.ok) {
            throw new Error(feedbackResponseError(data) || 'Atsauksmi neizdevās pievienot.')
        }

        feedbackForm.value = { rating: 5, comment: '' }
        feedbackSuccess.value = data.ziņa || 'Atsauksme veiksmīgi pievienota.'

        if (movie.value && data.rating !== undefined) {
            movie.value.rating = Number(data.rating) || movie.value.rating
        }

        await fetchFeedbacks()
    } catch (error) {
        feedbackError.value = error.message || 'Atsauksmi neizdevās pievienot.'
    } finally {
        feedbackSubmitting.value = false
    }
}

const loadMovieDetails = () => {
    fetchMovie()
    fetchFeedbacks()
}

onMounted(loadMovieDetails)
watch(movieId, loadMovieDetails)

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
.empty-state-card,
.feedback-form-card,
.feedback-card {
    animation: subtle-fade-in 0.42s ease both;
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

.hero-section {
    position: relative;
    overflow: hidden;
    background: transparent;
}

.hero-section::before {
    content: none;
}

.screenings-section,
.feedbacks-section {
    position: relative;
    overflow: hidden;
    background: transparent;
}

.screenings-section::before,
.feedbacks-section::before {
    content: none;
}

.hero-section :deep(.v-container),
.screenings-section :deep(.v-container),
.feedbacks-section :deep(.v-container) {
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
    color: #ffffff;
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
.empty-state-card,
.feedback-form-card,
.feedback-card {
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: linear-gradient(180deg, #141926, #0f131d);
}

.feedback-form-card :deep(.v-field),
.feedback-form-card :deep(.v-label),
.feedback-form-card :deep(.v-field__input),
.feedback-form-card :deep(.v-counter),
.feedback-form-card :deep(.v-icon),
.feedback-form-card :deep(.v-alert__content) {
    color: #edf2ff;
}

.feedback-card-title {
    color: #ffffff;
    font-size: 1.2rem;
}

.feedback-state {
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 24px;
    background: rgba(15, 19, 29, 0.78);
}

.feedback-list {
    display: grid;
    gap: 12px;
}

.feedback-card {
    color: #edf2ff;
}

.feedback-author,
.feedback-rating {
    color: #ffffff;
}

.feedback-date {
    color: #9eabc4;
    font-size: 0.86rem;
}

.feedback-comment {
    color: #d2d9e7;
    line-height: 1.65;
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
