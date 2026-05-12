<template>
  <div class="profile-page">

    <v-main class="main-content">
      <v-container class="py-8 py-md-10">
        <v-card v-if="!isAuthenticated" class="state-card pa-6 pa-md-8 text-center">
          <v-icon icon="mdi-account-lock-outline" size="46" class="mb-4 state-icon" />
          <h1 class="section-title mb-3">Profils</h1>
          <p class="muted-copy mb-5">Lai apskatītu profilu, nepieciešams pieslēgties.</p>
          <v-btn color="#E50914" rounded="lg" class="text-none login-btn" to="/login">
            Pieslēgties
          </v-btn>
        </v-card>

        <template v-else>
          <v-card class="profile-hero pa-5 pa-md-7 mb-6">
            <div class="d-flex align-center justify-space-between flex-wrap ga-4">
              <div class="d-flex align-center ga-4">
                <v-avatar color="#E50914" size="64">
                  <v-icon icon="mdi-account" size="34" />
                </v-avatar>
                <div>
                  <p class="hero-badge mb-2">SEEKINO profils</p>
                  <h1 class="profile-title mb-1">{{ user?.nickname }}</h1>
                  <p class="muted-copy mb-0">{{ user?.email }}</p>
                </div>
              </div>

              <v-chip class="role-chip" prepend-icon="mdi-shield-account-outline">
                {{ roleLabel(user?.role) }}
              </v-chip>
            </div>
          </v-card>

          <v-card class="profile-data-card pa-4 pa-md-5 mb-6">
            <div class="d-flex align-center justify-space-between flex-wrap ga-3 mb-4">
              <h2 class="section-title mb-0">Profila dati</h2>
              <v-btn
                v-if="!profileEditing"
                variant="outlined"
                rounded="lg"
                class="text-none retry-btn"
                prepend-icon="mdi-account-edit-outline"
                @click="startProfileEdit"
              >
                Rediģēt profilu
              </v-btn>
            </div>

            <v-alert v-if="profileSuccess" type="success" variant="tonal" class="mb-4">
              {{ profileSuccess }}
            </v-alert>

            <v-alert v-if="profileError" type="error" variant="tonal" class="mb-4">
              {{ profileError }}
            </v-alert>

            <div v-if="!profileEditing" class="details-list">
              <div class="detail-row">
                <span>Segvārds</span>
                <strong>{{ user?.nickname }}</strong>
              </div>
              <div class="detail-row">
                <span>E-pasts</span>
                <strong>{{ user?.email }}</strong>
              </div>
            </div>

            <v-form v-else @submit.prevent="saveProfile">
              <v-text-field
                v-model="profileForm.nickname"
                label="Segvārds"
                variant="outlined"
                prepend-inner-icon="mdi-account-outline"
                class="mb-3"
                :disabled="profileSaving"
              />
              <v-text-field
                v-model="profileForm.email"
                label="E-pasts"
                type="email"
                variant="outlined"
                prepend-inner-icon="mdi-email-outline"
                class="mb-4"
                :disabled="profileSaving"
              />
              <div class="d-flex flex-wrap ga-3">
                <v-btn
                  type="submit"
                  color="#E50914"
                  rounded="lg"
                  class="text-none login-btn"
                  :loading="profileSaving"
                >
                  Saglabāt izmaiņas
                </v-btn>
                <v-btn
                  variant="outlined"
                  rounded="lg"
                  class="text-none retry-btn"
                  :disabled="profileSaving"
                  @click="cancelProfileEdit"
                >
                  Atcelt
                </v-btn>
              </div>
            </v-form>
          </v-card>

          <section class="reservations-section">
            <div class="d-flex align-center justify-space-between flex-wrap ga-3 mb-4">
              <h2 class="section-title">Manas rezervācijas</h2>
              <v-btn
                variant="outlined"
                rounded="lg"
                class="text-none retry-btn"
                prepend-icon="mdi-refresh"
                :loading="reservationsLoading"
                @click="fetchReservations"
              >
                Atjaunot
              </v-btn>
            </div>

            <div v-if="reservationsLoading" class="py-8 text-center">
              <v-progress-circular indeterminate color="#E50914" />
              <p class="movie-state-text mt-3 mb-0">Ielādē rezervācijas...</p>
            </div>

            <v-alert v-else-if="reservationsError" type="error" variant="tonal" class="mb-4">
              <div class="d-flex align-center justify-space-between flex-wrap ga-3">
                <span>{{ reservationsError }}</span>
                <v-btn variant="outlined" rounded="lg" class="text-none" @click="fetchReservations">
                  Mēģināt vēlreiz
                </v-btn>
              </div>
            </v-alert>

            <v-alert v-else-if="reservations.length === 0" type="info" variant="tonal" class="mb-4">
              {{ reservationsMessage || 'Tev vēl nav nevienas rezervācijas.' }}
            </v-alert>

            <v-alert v-if="cancellationSuccess" type="success" variant="tonal" class="mb-4">
              {{ cancellationSuccess }}
            </v-alert>

            <v-alert v-if="cancellationError" type="error" variant="tonal" class="mb-4">
              {{ cancellationError }}
            </v-alert>

            <template v-if="!reservationsLoading && !reservationsError && reservations.length > 0">
              <div class="reservation-group mb-8">
                <h3 class="reservation-group-title mb-3">Aktīvās rezervācijas</h3>

                <v-alert v-if="activeReservations.length === 0" type="info" variant="tonal" class="mb-4">
                  Tev šobrīd nav aktīvu rezervāciju.
                </v-alert>

                <v-row v-else>
                  <v-col v-for="reservation in activeReservations" :key="reservation.id" cols="12" md="6">
                    <v-card class="reservation-card h-100 pa-4 pa-md-5">
                      <div class="d-flex align-start justify-space-between ga-3 mb-4">
                        <div>
                          <h3 class="reservation-title mb-2">{{ reservation.movieTitle }}</h3>
                          <p class="reservation-meta mb-0">
                            {{ reservation.screeningDate }} plkst. {{ reservation.screeningTime }}
                          </p>
                        </div>
                        <v-chip size="small" class="status-chip" :class="`status-chip--${reservation.paymentStatus}`">
                          {{ paymentStatusLabel(reservation.paymentStatus) }}
                        </v-chip>
                      </div>

                      <div class="details-list">
                        <div class="detail-row">
                          <span>Zāle</span>
                          <strong>{{ reservation.hallName }}</strong>
                        </div>
                        <div class="detail-row">
                          <span>Sēdvietas</span>
                          <strong>{{ reservation.seatLabels }}</strong>
                        </div>
                        <div class="detail-row">
                          <span>Kopējā cena</span>
                          <strong>{{ formatPrice(reservation.totalPrice) }}</strong>
                        </div>
                      </div>

                      <div class="reservation-actions mt-4">
                        <v-btn
                          variant="outlined"
                          rounded="lg"
                          class="text-none cancel-reservation-btn"
                          prepend-icon="mdi-close-circle-outline"
                          :loading="cancelingReservationId === reservation.id"
                          :disabled="Boolean(cancelingReservationId)"
                          @click="cancelReservation(reservation)"
                        >
                          Atcelt rezervāciju
                        </v-btn>
                      </div>
                    </v-card>
                  </v-col>
                </v-row>
              </div>

              <div class="reservation-group">
                <h3 class="reservation-group-title mb-3">Rezervāciju vēsture</h3>

                <v-alert v-if="cancelledReservations.length === 0" type="info" variant="tonal" class="mb-4">
                  Rezervāciju vēsturē vēl nav atceltu rezervāciju.
                </v-alert>

                <v-row v-else>
                  <v-col v-for="reservation in cancelledReservations" :key="reservation.id" cols="12" md="6">
                    <v-card class="reservation-card h-100 pa-4 pa-md-5">
                      <div class="d-flex align-start justify-space-between ga-3 mb-4">
                        <div>
                          <h3 class="reservation-title mb-2">{{ reservation.movieTitle }}</h3>
                          <p class="reservation-meta mb-0">
                            {{ reservation.screeningDate }} plkst. {{ reservation.screeningTime }}
                          </p>
                        </div>
                        <v-chip size="small" class="status-chip" :class="`status-chip--${reservation.paymentStatus}`">
                          {{ paymentStatusLabel(reservation.paymentStatus) }}
                        </v-chip>
                      </div>

                      <div class="details-list">
                        <div class="detail-row">
                          <span>Zāle</span>
                          <strong>{{ reservation.hallName }}</strong>
                        </div>
                        <div class="detail-row">
                          <span>Sēdvietas</span>
                          <strong>{{ reservation.seatLabels }}</strong>
                        </div>
                        <div class="detail-row">
                          <span>Kopējā cena</span>
                          <strong>{{ formatPrice(reservation.totalPrice) }}</strong>
                        </div>
                      </div>
                    </v-card>
                  </v-col>
                </v-row>
              </div>
            </template>
          </section>
        </template>
      </v-container>
    </v-main>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { apiBaseUrl, useAuth } from '@/services/auth'

const router = useRouter()
const drawer = ref(false)
const { token, user, isAuthenticated, authLoading, fetchMe, logout, clearSession, updateStoredUser } = useAuth()
const reservations = ref([])
const reservationsLoading = ref(false)
const reservationsError = ref('')
const reservationsMessage = ref('')
const cancellationSuccess = ref('')
const cancellationError = ref('')
const cancelingReservationId = ref(null)
const profileEditing = ref(false)
const profileSaving = ref(false)
const profileSuccess = ref('')
const profileError = ref('')
const profileForm = ref({
  nickname: '',
  email: '',
})
const activeReservations = computed(() => reservations.value.filter((reservation) => !reservation.isCancelled))
const cancelledReservations = computed(() => reservations.value.filter((reservation) => reservation.isCancelled))

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

const formatDate = (value) => {
  const [year, month, day] = String(value || '').slice(0, 10).split('-')

  return year && month && day ? `${day}.${month}.${year}.` : 'Datums nav norādīts'
}

const formatTime = (value) => String(value || 'Laiks nav norādīts').slice(0, 5)
const formatPrice = (value) => `${Number(value || 0).toFixed(2)} €`

const roleLabel = (role) => {
  const labels = {
    admin: 'Administrators',
    user: 'Lietotājs',
  }

  return labels[role] || 'Lietotājs'
}

const paymentStatusLabel = (status) => {
  const labels = {
    pending: 'Gaida apmaksu',
    paid: 'Apmaksāts',
    cancelled: 'Atcelta',
  }

  return labels[status] || 'Nav zināms'
}

const responseErrorMessage = (payload) => payload?.ziņa || payload?.message || 'Pieprasījumu neizdevās izpildīt.'

const profileResponseErrorMessage = (payload) => {
  if (payload?.kļūdas && typeof payload.kļūdas === 'object') {
    const firstError = Object.values(payload.kļūdas).flat().find(Boolean)

    if (firstError) {
      return firstError
    }
  }

  return responseErrorMessage(payload)
}

const resetProfileForm = () => {
  profileForm.value = {
    nickname: user.value?.nickname || '',
    email: user.value?.email || '',
  }
}

const startProfileEdit = () => {
  profileSuccess.value = ''
  profileError.value = ''
  resetProfileForm()
  profileEditing.value = true
}

const cancelProfileEdit = () => {
  profileError.value = ''
  resetProfileForm()
  profileEditing.value = false
}

const saveProfile = async () => {
  const accessToken = localStorage.getItem('seekino_token') || token.value

  if (!accessToken) {
    profileError.value = 'Lai atjauninātu profilu, nepieciešams pieslēgties.'
    return
  }

  profileSaving.value = true
  profileSuccess.value = ''
  profileError.value = ''

  try {
    const response = await fetch(`${apiBaseUrl}/api/profile`, {
      method: 'PUT',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        Authorization: `Bearer ${accessToken}`,
      },
      body: JSON.stringify(profileForm.value),
    })
    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      throw new Error(profileResponseErrorMessage(data))
    }

    updateStoredUser(data.lietotājs)
    profileSuccess.value = data.ziņa || 'Profils veiksmīgi atjaunināts.'
    profileEditing.value = false
    resetProfileForm()
  } catch (caughtError) {
    profileError.value = caughtError.message || 'Profilu neizdevās atjaunināt.'
  } finally {
    profileSaving.value = false
  }
}

const seatLabel = (seat) => {
  const rowNumber = Number(seat.row_number)
  const rowLabel = rowNumber > 0 && rowNumber <= 26 ? String.fromCharCode(64 + rowNumber) : rowNumber

  return `${rowLabel}${seat.seat_number}`
}

const normalizeReservation = (reservation) => {
  const seats = reservation.seats || []

  return {
    id: reservation.id,
    movieTitle: reservation.movie?.title || reservation.movie?.name || 'Filmas nosaukums nav pieejams',
    screeningDate: formatDate(reservation.screening_date),
    screeningTime: formatTime(reservation.screening_time),
    hallName: reservation.hall?.name || 'Zāle nav norādīta',
    seatLabels: seats.length ? seats.map(seatLabel).join(', ') : 'Nav norādītas',
    totalPrice: Number(reservation.total_price || 0),
    paymentStatus: reservation.payment_status,
    isCancelled: reservation.payment_status === 'cancelled',
  }
}

const fetchReservations = async () => {
  const accessToken = localStorage.getItem('seekino_token') || token.value

  if (!accessToken) {
    reservations.value = []
    reservationsMessage.value = ''
    return
  }

  reservationsLoading.value = true
  reservationsError.value = ''
  reservationsMessage.value = ''

  try {
    const response = await fetch(`${apiBaseUrl}/api/profile/reservations`, {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${accessToken}`,
      },
    })
    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      throw new Error(data.ziņa || data.message || 'Rezervācijas neizdevās ielādēt.')
    }

    reservations.value = (data.reservations || []).map(normalizeReservation)
    reservationsMessage.value = data.ziņa || ''
  } catch (caughtError) {
    reservations.value = []
    reservationsError.value = caughtError.message || 'Rezervācijas neizdevās ielādēt.'
  } finally {
    reservationsLoading.value = false
  }
}

const cancelReservation = async (reservation) => {
  if (!window.confirm('Vai tiešām vēlies atcelt šo rezervāciju?')) {
    return
  }

  const accessToken = localStorage.getItem('seekino_token') || token.value

  if (!accessToken) {
    cancellationError.value = 'Lai atceltu rezervāciju, nepieciešams pieslēgties.'
    return
  }

  cancelingReservationId.value = reservation.id
  cancellationSuccess.value = ''
  cancellationError.value = ''

  try {
    const response = await fetch(`${apiBaseUrl}/api/profile/reservations/${reservation.id}/cancel`, {
      method: 'PATCH',
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${accessToken}`,
      },
    })
    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      throw new Error(responseErrorMessage(data))
    }

    cancellationSuccess.value = data.ziņa || 'Rezervācija veiksmīgi atcelta.'
    await fetchReservations()
  } catch (caughtError) {
    cancellationError.value = caughtError.message || 'Rezervāciju neizdevās atcelt.'
  } finally {
    cancelingReservationId.value = null
  }
}

const handleLogout = async () => {
  await logout()
  router.push('/')
}

onMounted(async () => {
  if (!token.value) {
    return
  }

  try {
    if (!user.value) {
      await fetchMe()
    }

    resetProfileForm()
    await fetchReservations()
  } catch {
    clearSession()
  }
})
</script>

<style scoped>
.profile-page {
  background: #0a0c12;
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
  background:
    radial-gradient(circle at 12% 18%, rgba(68, 111, 203, 0.34), transparent 42%),
    radial-gradient(circle at 82% 14%, rgba(220, 54, 88, 0.3), transparent 38%),
    radial-gradient(circle at 56% 86%, rgba(66, 141, 106, 0.22), transparent 36%),
    linear-gradient(130deg, #0f1628 0%, #17172a 45%, #2a141d 100%);
}

.nav-btn,
.drawer-close-btn {
  color: #f4f6fb;
}

.logo {
  filter: invert(1);
}

.brand-link {
  display: inline-flex;
  align-items: center;
  width: 160px;
  min-width: 160px;
  text-decoration: none;
}

.nav-pages {
  display: flex;
}

.nav-link-btn {
  color: #d7dff2;
}

.login-btn {
  background: linear-gradient(135deg, #ff5a44, #e50914);
  color: #ffffff !important;
  font-weight: 700;
  box-shadow: 0 8px 26px rgba(229, 9, 20, 0.36);
}

.user-chip,
.role-chip {
  border: 1px solid rgba(255, 255, 255, 0.16);
  background: rgba(255, 255, 255, 0.08);
  color: #edf2ff;
}

.state-card,
.profile-hero,
.profile-data-card,
.reservation-card {
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 16px;
  background: linear-gradient(165deg, rgba(18, 23, 36, 0.96), rgba(12, 15, 24, 0.96));
  color: #edf2ff;
  box-shadow: 0 22px 58px rgba(0, 0, 0, 0.34);
}

.profile-data-card :deep(.v-field),
.profile-data-card :deep(.v-label),
.profile-data-card :deep(.v-field__input),
.profile-data-card :deep(.v-icon) {
  color: #edf2ff;
}

.state-icon {
  color: #ff5a44;
}

.hero-badge {
  display: inline-block;
  padding: 6px 12px;
  border: 1px solid rgba(255, 255, 255, 0.22);
  border-radius: 999px;
  color: #d7e2ff;
  font-size: 12px;
  letter-spacing: 1px;
  text-transform: uppercase;
}

.profile-title {
  font-size: clamp(2rem, 4vw, 3rem);
  line-height: 1.05;
}

.section-title {
  color: #ffffff;
  font-size: clamp(1.5rem, 3vw, 2rem);
  font-weight: 800;
}

.muted-copy,
.movie-state-text,
.reservation-meta {
  color: #d2d9e7;
}

.retry-btn {
  border-color: rgba(255, 255, 255, 0.22);
  color: #edf2ff;
}

.cancel-reservation-btn {
  border-color: rgba(229, 9, 20, 0.5);
  color: #ffd7d9;
}

.cancel-reservation-btn:hover {
  background: rgba(229, 9, 20, 0.12);
}

.reservation-title {
  color: #ffffff;
  font-size: 1.3rem;
  line-height: 1.25;
}

.reservation-group-title {
  color: #ffffff;
  font-size: 1.15rem;
  font-weight: 800;
}

.status-chip {
  color: #ffffff;
  font-weight: 700;
}

.status-chip--pending {
  background: rgba(255, 180, 64, 0.2);
}

.status-chip--paid {
  background: rgba(64, 188, 118, 0.22);
}

.status-chip--cancelled {
  background: rgba(229, 9, 20, 0.22);
}

.details-list {
  display: grid;
  gap: 12px;
}

.detail-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.detail-row:last-child {
  padding-bottom: 0;
  border-bottom: 0;
}

.detail-row span {
  color: #aeb8cc;
}

.detail-row strong {
  color: #ffffff;
  text-align: right;
}

.app-drawer :deep(.v-list),
.app-drawer :deep(.v-list-subheader),
.app-drawer :deep(.v-list-item) {
  color: #edf2ff;
}

.drawer-group-label {
  color: #8f9ab2;
  font-size: 0.78rem;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.drawer-list-item {
  color: #edf2ff;
}

.drawer-group-divider {
  opacity: 0.18;
}

@media (max-width: 720px) {
  .nav-pages {
    display: none;
  }

  .brand-link {
    width: 132px;
    min-width: 132px;
  }

  .detail-row {
    align-items: flex-start;
    flex-direction: column;
    gap: 4px;
  }

  .detail-row strong {
    text-align: left;
  }
}
</style>
