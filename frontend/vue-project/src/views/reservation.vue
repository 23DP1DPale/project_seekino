<template>
    <div class="reservation-page">

        <v-main class="main-content">
            <v-container class="pt-4 pb-8 pt-md-5 pb-md-10">
                <v-btn
                    variant="text"
                    class="text-none back-link mb-4"
                    prepend-icon="mdi-arrow-left"
                    :to="backLinkTarget"
                >
                    {{ backLinkLabel }}
                </v-btn>

                <v-card v-if="loading" class="state-card rounded-xl pa-6 pa-md-8 text-center">
                    <v-progress-circular indeterminate color="#E50914" size="46" class="mb-4" />
                    <h1 class="section-title mb-2">Rezervācija tiek ielādēta</h1>
                    <p class="muted-copy mb-0">Lūdzu uzgaidi, kamēr saņemam seansa informāciju.</p>
                </v-card>

                <v-alert v-else-if="error" type="error" variant="tonal" class="state-card rounded-xl mb-6">
                    <div class="d-flex align-center justify-space-between flex-wrap ga-3">
                        <span>{{ error }}</span>
                        <v-btn color="#E50914" rounded="lg" class="text-none" @click="fetchScreening">
                            Mēģināt vēlreiz
                        </v-btn>
                    </div>
                </v-alert>

                <template v-else-if="screening">
                    <div class="reservation-panel pa-5 pa-md-7 mb-6">
                        <v-alert
                            v-if="reservationSuccess"
                            type="success"
                            variant="tonal"
                            class="mb-4"
                        >
                            {{ reservationSuccess }}
                        </v-alert>

                        <v-alert
                            v-if="reservationError"
                            type="error"
                            variant="tonal"
                            class="mb-4"
                        >
                            {{ reservationError }}
                        </v-alert>

                        <div class="d-flex align-center justify-space-between flex-wrap ga-3 mb-5">
                            <div>
                                <p class="hero-badge">SEEKINO rezervācija</p>
                                <h1 class="reservation-title mb-2">{{ screening.movieTitle }}</h1>
                            </div>
                            <v-chip class="price-chip" size="small">1 biļete: {{ screening.price }}</v-chip>
                        </div>

                        <v-row>
                            <v-col cols="12" md="7">
                                <v-card class="seat-map-card rounded-xl pa-4 pa-md-5">
                                    <div class="screen-label mb-5">Ekrāns</div>

                                    <div class="seat-grid" :style="{ gridTemplateColumns: `repeat(${seatColumns}, 1fr)` }">
                                        <button
                                            v-for="seat in seats"
                                            :key="seat.id"
                                            type="button"
                                            class="seat-button"
                                            :class="{
                                                'seat-button--selected': selectedSeatIds.includes(seat.id),
                                                'seat-button--reserved': seat.isReserved,
                                            }"
                                            :disabled="seat.isReserved"
                                            @click="toggleSeat(seat.id)"
                                        >
                                            {{ seat.label }}
                                        </button>
                                    </div>
                                </v-card>
                            </v-col>

                            <v-col cols="12" md="5">
                                <v-card class="summary-card rounded-xl pa-4 pa-md-5">
                                    <h2 class="section-title mb-4">Seansa informācija</h2>

                                    <div class="summary-row">
                                        <span>Datums un laiks</span>
                                        <strong>{{ screening.date }} {{ screening.time }}</strong>
                                    </div>
                                    <div class="summary-row">
                                        <span>Zāle</span>
                                        <strong>{{ screening.hallName }}</strong>
                                    </div>
                                    <div class="summary-row">
                                        <span>Cena par vienu biļeti</span>
                                        <strong>{{ screening.price }}</strong>
                                    </div>

                                    <v-divider class="my-4" />

                                    <h3 class="summary-heading mb-3">Izvēlētās sēdvietas</h3>
                                    <div v-if="selectedSeats.length" class="selected-seats mb-4">
                                        <v-chip
                                            v-for="seat in selectedSeats"
                                            :key="seat.id"
                                            size="small"
                                            variant="outlined"
                                            class="selected-seat-chip"
                                        >
                                            {{ seat.label }}
                                        </v-chip>
                                    </div>
                                    <p v-else class="muted-copy mb-4">Vēl nav izvēlēta neviena sēdvieta.</p>

                                    <div class="total-row mb-5">
                                        <span>Kopējā cena</span>
                                        <strong>{{ totalPrice }}</strong>
                                    </div>

                                    <v-btn
                                        color="#E50914"
                                        block
                                        rounded="lg"
                                        size="large"
                                        class="text-none reserve-btn"
                                        :disabled="selectedSeats.length === 0 || reservationLoading"
                                        :loading="reservationLoading"
                                        @click="submitReservation"
                                    >
                                        {{ reservationLoading ? 'Rezervē...' : 'Apstiprināt rezervāciju' }}
                                    </v-btn>
                                </v-card>
                            </v-col>
                        </v-row>
                    </div>
                </template>
            </v-container>
        </v-main>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '@/services/auth'

const route = useRoute()
const router = useRouter()
const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000'
const { token, isAuthenticated, clearSession } = useAuth()
const loading = ref(false)
const error = ref('')
const reservationLoading = ref(false)
const reservationError = ref('')
const reservationSuccess = ref('')
const screening = ref(null)
const selectedSeatIds = ref([])

const screeningId = computed(() => Number(route.params.screeningId))
const cameFromScreenings = computed(() => route.query.from === 'seansi')
const cameFromHome = computed(() => route.query.from === 'home')
const backLinkLabel = computed(() => {
    if (cameFromScreenings.value) return 'Atpakaļ uz seansiem'
    if (cameFromHome.value) return 'Atpakaļ uz sākumlapu'
    return 'Atpakaļ uz filmām'
})
const backLinkTarget = computed(() => {
    if (cameFromScreenings.value) return '/seansi'
    if (cameFromHome.value) return '/'
    return '/filmas'
})
const seatColumns = computed(() => {
    const maxSeatNumber = Math.max(...(screening.value?.seats || []).map((seat) => seat.seat_number), 0)

    return maxSeatNumber || 6
})

const formatDate = (value) => {
    const [year, month, day] = String(value || '').slice(0, 10).split('-')
    return year && month && day ? `${day}.${month}.${year}.` : 'Datums nav norādīts'
}

const formatTime = (value) => String(value || 'Laiks nav norādīts').slice(0, 5)
const formatPrice = (value) => `${Number(value || 0).toFixed(2)} €`

const normalizeScreening = (item) => ({
    id: item.id,
    movieTitle: item.movie?.title || item.movie?.name || 'Filmas nosaukums nav pieejams',
    date: formatDate(item.screening_date || item.date),
    time: formatTime(item.screening_time || item.time),
    hallName: item.hall?.name || 'Zāle nav norādīta',
    priceValue: Number(item.price ?? item.cost ?? 0),
    price: formatPrice(item.price ?? item.cost),
    seats: (item.seats || []).map((seat) => ({
        id: Number(seat.id),
        row_number: Number(seat.row_number),
        seat_number: Number(seat.seat_number),
        isReserved: Boolean(seat.is_reserved),
        label: `${String.fromCharCode(64 + Number(seat.row_number))}${seat.seat_number}`,
    })),
})

const seats = computed(() => screening.value?.seats || [])

const selectedSeats = computed(() => seats.value.filter((seat) => selectedSeatIds.value.includes(seat.id)))
const totalPrice = computed(() => formatPrice(selectedSeats.value.length * (screening.value?.priceValue || 0)))

const fetchScreening = async () => {
    loading.value = true
    error.value = ''
    reservationError.value = ''
    selectedSeatIds.value = []

    try {
        const response = await fetch(`${apiBaseUrl}/api/screenings/${screeningId.value}`, {
            headers: {
                Accept: 'application/json',
            },
        })

        if (!response.ok) {
            throw new Error('Neizdevās ielādēt seansa informāciju.')
        }

        const data = await response.json()
        screening.value = normalizeScreening(data)
    } catch (caughtError) {
        screening.value = null
        error.value = caughtError.message || 'Neizdevās ielādēt seansa informāciju.'
    } finally {
        loading.value = false
    }
}

const toggleSeat = (seatId) => {
    const seat = seats.value.find((item) => item.id === seatId)

    if (!seat || seat.isReserved || reservationLoading.value) {
        return
    }

    selectedSeatIds.value = selectedSeatIds.value.includes(seatId)
        ? selectedSeatIds.value.filter((id) => id !== seatId)
        : [...selectedSeatIds.value, seatId]
}

const submitReservation = async () => {
    if (!selectedSeatIds.value.length || reservationLoading.value) {
        return
    }

    if (!isAuthenticated.value) {
        router.push({
            path: '/login',
            query: { redirect: route.fullPath },
        })

        return
    }

    reservationLoading.value = true
    reservationError.value = ''
    reservationSuccess.value = ''

    try {
        const response = await fetch(`${apiBaseUrl}/api/reservations`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                ...(token.value ? { Authorization: `Bearer ${token.value}` } : {}),
            },
            body: JSON.stringify({
                screening_id: screeningId.value,
                seat_ids: selectedSeatIds.value,
            }),
        })
        const data = await response.json().catch(() => ({}))

        if (!response.ok) {
            if (response.status === 401) {
                clearSession()
                router.push({
                    path: '/login',
                    query: { redirect: route.fullPath },
                })
            }

            throw new Error(data.message || 'Rezervāciju neizdevās izveidot.')
        }

        selectedSeatIds.value = []
        await fetchScreening()
        reservationSuccess.value = 'Rezervācija veiksmīgi izveidota!'
    } catch (caughtError) {
        reservationError.value = caughtError.message || 'Rezervāciju neizdevās izveidot.'
        reservationSuccess.value = ''
    } finally {
        reservationLoading.value = false
    }
}

onMounted(fetchScreening)
watch(screeningId, fetchScreening)
</script>

<style scoped>
.reservation-page {
    background: #0a0c12;
    color: #f4f6fb;
}

.main-content {
    min-height: 100vh;
    background:
        radial-gradient(circle at 12% 18%, rgba(68, 111, 203, 0.34), transparent 42%),
        radial-gradient(circle at 82% 14%, rgba(220, 54, 88, 0.3), transparent 38%),
        radial-gradient(circle at 56% 86%, rgba(66, 141, 106, 0.22), transparent 36%),
        linear-gradient(130deg, #0f1628 0%, #17172a 45%, #2a141d 100%);
}

.reservation-panel,
.state-card,
.seat-map-card,
.summary-card {
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: linear-gradient(150deg, rgba(10, 13, 21, 0.82), rgba(17, 20, 31, 0.76));
    box-shadow: 0 16px 48px rgba(6, 8, 13, 0.45);
}

.reservation-panel {
    border-radius: 24px;
    backdrop-filter: blur(10px);
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

.reservation-title {
    font-size: clamp(2rem, 4vw, 3.6rem);
    line-height: 1.05;
}

.section-title {
    color: #ffffff;
    font-size: clamp(1.2rem, 2vw, 1.8rem);
    font-weight: 700;
}

.muted-copy {
    color: #c8d0df;
}

.price-chip {
    background: linear-gradient(135deg, #24b26b, #149e59);
    color: #ffffff;
    font-weight: 700;
}

.screen-label {
    width: min(420px, 100%);
    margin-inline: auto;
    padding: 10px 16px;
    border-radius: 999px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.04));
    color: #d7e2ff;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    font-size: 0.78rem;
    font-weight: 700;
}

.seat-grid {
    display: grid;
    gap: 10px;
    max-width: 560px;
    margin-inline: auto;
}

.seat-button {
    min-height: 42px;
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.06);
    color: #edf2ff;
    font-weight: 700;
    cursor: pointer;
    transition: transform 0.18s ease, border-color 0.18s ease, background-color 0.18s ease, box-shadow 0.18s ease;
}

.seat-button:hover {
    transform: translateY(-2px);
    border-color: rgba(255, 255, 255, 0.34);
    background: rgba(255, 255, 255, 0.1);
}

.seat-button--selected {
    border-color: rgba(36, 178, 107, 0.88);
    background: linear-gradient(135deg, #24b26b, #149e59);
    color: #ffffff;
    box-shadow: 0 10px 22px rgba(23, 167, 95, 0.26);
}

.seat-button--selected:hover {
    border-color: rgba(35, 145, 91, 0.95);
    background: linear-gradient(135deg, #167a4b, #0f5f3a);
    color: #ffffff;
}

.seat-button--reserved,
.seat-button--reserved:hover {
    cursor: not-allowed;
    transform: none;
    border-color: rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.035);
    color: rgba(237, 242, 255, 0.38);
    box-shadow: none;
}

.reservation-panel :deep(.v-card),
.reservation-panel :deep(.v-card-text),
.reservation-panel :deep(.v-card-title),
.reservation-panel :deep(.v-chip),
.reservation-panel :deep(.v-btn),
.state-card :deep(.v-card-text),
.state-card :deep(.v-alert__content) {
    color: #f4f6fb;
}

.summary-row,
.total-row {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    padding: 10px 0;
    color: #c8d0df;
}

.summary-row strong,
.total-row strong {
    color: #ffffff;
    text-align: right;
}

.summary-heading {
    color: #ffffff;
    font-size: 1rem;
}

.selected-seats {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.selected-seat-chip {
    color: #f4f6fb;
    border-color: rgba(255, 255, 255, 0.24);
}

.total-row {
    border-top: 1px solid rgba(255, 255, 255, 0.12);
    font-size: 1.1rem;
    font-weight: 700;
}

.reserve-btn {
    background: linear-gradient(135deg, #ff3b30, #e50914);
    color: #ffffff !important;
    font-weight: 700;
}

.reserve-btn.v-btn--disabled {
    opacity: 0.48;
}

@media (max-width: 600px) {
.seat-grid {
        gap: 7px;
    }

    .seat-button {
        min-height: 36px;
        font-size: 0.8rem;
    }
}
</style>
