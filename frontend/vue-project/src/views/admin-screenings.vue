<template>
  <div class="admin-page">

    <v-main class="main-content">
      <v-container class="py-8 py-md-10">
        <v-card v-if="accessLoading" class="state-card pa-6 pa-md-8 text-center">
          <v-progress-circular indeterminate color="#E50914" size="46" class="mb-4" />
          <h1 class="section-title mb-2">Pārbaudām piekļuvi</h1>
          <p class="muted-copy mb-0">Lūdzu uzgaidi, kamēr pārbaudām administratora tiesības.</p>
        </v-card>

        <v-card v-else-if="accessError" class="state-card pa-6 pa-md-8 text-center">
          <v-icon icon="mdi-shield-alert-outline" size="46" class="state-icon mb-4" />
          <h1 class="section-title mb-3">Piekļuve liegta</h1>
          <p class="muted-copy mb-5">{{ accessError }}</p>
          <v-btn color="#E50914" rounded="lg" class="text-none admin-submit-btn" to="/">
            Atgriezties sākumā
          </v-btn>
        </v-card>

        <template v-else>
          <v-card class="admin-hero pa-5 pa-md-7 mb-6">
            <div class="d-flex align-center justify-space-between flex-wrap ga-4">
              <div>
                <p class="hero-badge mb-2">SEEKINO administrācija</p>
                <h1 class="admin-title mb-2">Seansu pārvaldība</h1>
                <p class="muted-copy mb-0">Izveido, labo un dzēs kino seansus esošajām filmām un zālēm.</p>
              </div>

              <v-btn
                variant="outlined"
                rounded="lg"
                class="text-none retry-btn"
                prepend-icon="mdi-refresh"
                :loading="screeningsLoading"
                @click="fetchScreenings"
              >
                Atjaunot
              </v-btn>
            </div>
          </v-card>

          <v-alert v-if="successMessage" type="success" variant="tonal" class="mb-4">
            {{ successMessage }}
          </v-alert>

          <v-alert v-if="errorMessage" type="error" variant="tonal" class="mb-4">
            {{ errorMessage }}
          </v-alert>

          <v-row>
            <v-col cols="12" lg="5">
              <v-card class="admin-card pa-4 pa-md-5">
                <div class="d-flex align-center justify-space-between ga-3 mb-4">
                  <h2 class="panel-title mb-0">{{ editingScreeningId ? 'Labot seansu' : 'Jauns seanss' }}</h2>
                  <v-btn
                    v-if="editingScreeningId"
                    variant="text"
                    rounded="lg"
                    class="text-none nav-link-btn"
                    @click="resetForm"
                  >
                    Atcelt labošanu
                  </v-btn>
                </div>

                <v-form @submit.prevent="submitScreening">
                  <v-select
                    v-model="form.movie"
                    label="Filma"
                    :items="movies"
                    item-title="name"
                    item-value="id"
                    variant="outlined"
                    prepend-inner-icon="mdi-movie-open-outline"
                    class="mb-3"
                    :loading="optionsLoading"
                    :disabled="formLoading || optionsLoading"
                  />
                  <v-select
                    v-model="form.hall"
                    label="Zāle"
                    :items="halls"
                    item-title="name"
                    item-value="id"
                    variant="outlined"
                    prepend-inner-icon="mdi-sofa-outline"
                    class="mb-3"
                    :loading="optionsLoading"
                    :disabled="formLoading || optionsLoading"
                  />
                  <v-text-field
                    v-model="form.screening_date"
                    label="Datums"
                    type="date"
                    lang="lv-LV"
                    variant="outlined"
                    prepend-inner-icon="mdi-calendar-outline"
                    class="date-field mb-3"
                    :disabled="formLoading"
                    @mousedown.prevent="openNativePicker"
                    @click="openNativePicker"
                    @focus="openNativePicker"
                  />
                  <v-text-field
                    v-model="form.screening_time"
                    label="Laiks"
                    placeholder="HH:mm"
                    variant="outlined"
                    prepend-inner-icon="mdi-clock-outline"
                    class="mb-3"
                    :disabled="formLoading"
                    @update:model-value="form.screening_time = normalizeTime($event)"
                  />
                  <v-text-field
                    v-model="form.cost"
                    label="Cena"
                    type="number"
                    min="0"
                    step="0.01"
                    variant="outlined"
                    prepend-inner-icon="mdi-cash"
                    class="mb-4"
                    :disabled="formLoading"
                    @blur="form.cost = formatCostInput(form.cost)"
                  />

                  <v-btn
                    type="submit"
                    color="#E50914"
                    rounded="lg"
                    class="text-none admin-submit-btn"
                    :loading="formLoading"
                  >
                    {{ editingScreeningId ? 'Saglabāt izmaiņas' : 'Izveidot seansu' }}
                  </v-btn>
                </v-form>
              </v-card>
            </v-col>

            <v-col cols="12" lg="7">
              <v-card class="admin-card pa-4 pa-md-5">
                <div class="d-flex align-center justify-space-between flex-wrap ga-3 mb-4">
                  <h2 class="panel-title mb-0">Seansu saraksts</h2>
                  <div class="d-flex align-center flex-wrap ga-3">
                    <v-switch
                      v-model="includePastScreenings"
                      color="#E50914"
                      density="compact"
                      hide-details
                      label="Rādīt vecos seansus"
                      class="archive-switch"
                      :disabled="screeningsLoading"
                      @update:model-value="fetchScreenings"
                    />
                    <v-chip class="user-chip" size="small">{{ screenings.length }} seansi</v-chip>
                  </div>
                </div>

                <div v-if="screeningsLoading" class="py-8 text-center">
                  <v-progress-circular indeterminate color="#E50914" />
                  <p class="movie-state-text mt-3 mb-0">Ielādē seansus...</p>
                </div>

                <v-alert v-else-if="screeningsError" type="error" variant="tonal" class="mb-4">
                  <div class="d-flex align-center justify-space-between flex-wrap ga-3">
                    <span>{{ screeningsError }}</span>
                    <v-btn variant="outlined" rounded="lg" class="text-none" @click="fetchScreenings">
                      Mēģināt vēlreiz
                    </v-btn>
                  </div>
                </v-alert>

                <v-alert v-else-if="screenings.length === 0" type="info" variant="tonal" class="mb-4">
                  Seansu saraksts šobrīd ir tukšs.
                </v-alert>

                <div v-else class="screening-list">
                  <div v-for="screening in screenings" :key="screening.id" class="screening-row">
                    <div class="screening-row-main">
                      <h3 class="screening-title mb-1">{{ screening.movieName }}</h3>
                      <p class="screening-meta mb-0">
                        {{ screening.hallName }} | {{ formatDate(screening.screening_date) }}
                        plkst. {{ screening.screening_time }} | {{ formatPrice(screening.cost) }}
                      </p>
                    </div>

                    <div class="screening-actions">
                      <v-btn
                        icon="mdi-pencil-outline"
                        variant="outlined"
                        size="small"
                        class="admin-icon-btn"
                        :disabled="formLoading || deleteLoadingId === screening.id"
                        @click="startEdit(screening)"
                      />
                      <v-btn
                        icon="mdi-delete-outline"
                        variant="outlined"
                        size="small"
                        class="admin-delete-btn"
                        :loading="deleteLoadingId === screening.id"
                        :disabled="formLoading"
                        @click="deleteScreening(screening)"
                      />
                    </div>
                  </div>
                </div>
              </v-card>
            </v-col>
          </v-row>
        </template>
      </v-container>
    </v-main>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { apiBaseUrl, useAuth } from '@/services/auth'

const router = useRouter()
const { token, user, fetchMe, clearSession } = useAuth()

const accessLoading = ref(true)
const accessError = ref('')
const screenings = ref([])
const movies = ref([])
const halls = ref([])
const screeningsLoading = ref(false)
const moviesLoading = ref(false)
const hallsLoading = ref(false)
const screeningsError = ref('')
const formLoading = ref(false)
const deleteLoadingId = ref(null)
const editingScreeningId = ref(null)
const includePastScreenings = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const form = ref(emptyForm())

const optionsLoading = computed(() => moviesLoading.value || hallsLoading.value)

function emptyForm() {
  return {
    movie: null,
    hall: null,
    screening_date: '',
    screening_time: '',
    cost: '0.00',
  }
}

const openNativePicker = (event) => {
  const input = event.currentTarget?.querySelector?.('input') || event.target?.querySelector?.('input') || event.target

  if (typeof input?.showPicker === 'function') {
    input.showPicker()
  }
}

const normalizeTime = (value) => String(value || '').slice(0, 5)
const isValidTime = (value) => /^([01]\d|2[0-3]):[0-5]\d$/.test(String(value || ''))
const formatCostInput = (value) => Number(value || 0).toFixed(2)

const authHeaders = () => ({
  Accept: 'application/json',
  'Content-Type': 'application/json',
  Authorization: `Bearer ${token.value}`,
})

const responseError = (payload) => {
  if (payload?.kļūdas && typeof payload.kļūdas === 'object') {
    const firstError = Object.values(payload.kļūdas).flat().find(Boolean)

    if (firstError) {
      return firstError
    }
  }

  return payload?.ziņa || payload?.message || 'Pieprasījumu neizdevās izpildīt.'
}

const ensureAdminAccess = async () => {
  accessLoading.value = true
  accessError.value = ''

  if (!token.value) {
    router.push({ path: '/login', query: { redirect: '/admin/screenings' } })
    return
  }

  try {
    const currentUser = user.value || await fetchMe()

    if (currentUser?.role !== 'admin') {
      accessError.value = 'Šī lapa pieejama tikai administratoriem.'
      return
    }

    await Promise.all([fetchMovies(), fetchHalls(), fetchScreenings()])
  } catch {
    clearSession()
    router.push({ path: '/login', query: { redirect: '/admin/screenings' } })
  } finally {
    accessLoading.value = false
  }
}

const normalizeScreening = (screening) => ({
  ...screening,
  movieName: screening.movie_record?.name || 'Filma nav norādīta',
  hallName: screening.hall_record?.name || 'Zāle nav norādīta',
})

const fetchMovies = async () => {
  moviesLoading.value = true

  try {
    const response = await fetch(`${apiBaseUrl}/api/admin/movies`, {
      headers: authHeaders(),
    })
    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      throw new Error(responseError(data))
    }

    movies.value = data.movies || []
  } catch (error) {
    movies.value = []
    errorMessage.value = error.message || 'Filmas neizdevās ielādēt.'
  } finally {
    moviesLoading.value = false
  }
}

const fetchHalls = async () => {
  hallsLoading.value = true

  try {
    const response = await fetch(`${apiBaseUrl}/api/admin/halls`, {
      headers: authHeaders(),
    })
    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      throw new Error(responseError(data))
    }

    halls.value = data.halls || []
  } catch (error) {
    halls.value = []
    errorMessage.value = error.message || 'Zāles neizdevās ielādēt.'
  } finally {
    hallsLoading.value = false
  }
}

const fetchScreenings = async () => {
  screeningsLoading.value = true
  screeningsError.value = ''

  try {
    const query = includePastScreenings.value ? '?include_past=1' : ''
    const response = await fetch(`${apiBaseUrl}/api/admin/screenings${query}`, {
      headers: authHeaders(),
    })
    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      throw new Error(responseError(data))
    }

    screenings.value = (data.screenings || []).map(normalizeScreening)
  } catch (error) {
    screenings.value = []
    screeningsError.value = error.message || 'Seansus neizdevās ielādēt.'
  } finally {
    screeningsLoading.value = false
  }
}

const resetMessages = () => {
  successMessage.value = ''
  errorMessage.value = ''
}

const resetForm = () => {
  editingScreeningId.value = null
  form.value = emptyForm()
  resetMessages()
}

const startEdit = (screening) => {
  editingScreeningId.value = screening.id
  form.value = {
    movie: screening.movie || null,
    hall: screening.hall || null,
    screening_date: screening.screening_date || '',
    screening_time: normalizeTime(screening.screening_time),
    cost: formatCostInput(screening.cost),
  }
  resetMessages()
}

const submitScreening = async () => {
  resetMessages()

  if (!isValidTime(form.value.screening_time)) {
    errorMessage.value = 'Laikam jābūt formātā HH:mm.'
    return
  }

  const isEditing = Boolean(editingScreeningId.value)
  const path = isEditing ? `/api/admin/screenings/${editingScreeningId.value}` : '/api/admin/screenings'
  formLoading.value = true
  const payload = {
    ...form.value,
    screening_time: normalizeTime(form.value.screening_time),
    cost: Number(form.value.cost || 0).toFixed(2),
  }

  try {
    const response = await fetch(`${apiBaseUrl}${path}`, {
      method: isEditing ? 'PUT' : 'POST',
      headers: authHeaders(),
      body: JSON.stringify(payload),
    })
    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      throw new Error(responseError(data))
    }

    successMessage.value = data.ziņa || (isEditing ? 'Seanss veiksmīgi atjaunots.' : 'Seanss veiksmīgi izveidots.')
    editingScreeningId.value = null
    form.value = emptyForm()
    await fetchScreenings()
  } catch (error) {
    errorMessage.value = error.message || 'Seansu neizdevās saglabāt.'
  } finally {
    formLoading.value = false
  }
}

const deleteScreening = async (screening) => {
  if (!window.confirm(`Vai tiešām vēlies dzēst seansu filmai "${screening.movieName}"?`)) {
    return
  }

  resetMessages()
  deleteLoadingId.value = screening.id

  try {
    const response = await fetch(`${apiBaseUrl}/api/admin/screenings/${screening.id}`, {
      method: 'DELETE',
      headers: authHeaders(),
    })
    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      throw new Error(responseError(data))
    }

    successMessage.value = data.ziņa || 'Seanss veiksmīgi dzēsts.'

    if (editingScreeningId.value === screening.id) {
      editingScreeningId.value = null
      form.value = emptyForm()
    }

    await fetchScreenings()
  } catch (error) {
    errorMessage.value = error.message || 'Seansu neizdevās dzēst.'
  } finally {
    deleteLoadingId.value = null
  }
}

const formatDate = (value) => {
  const [year, month, day] = String(value || '').slice(0, 10).split('-')

  return year && month && day ? `${day}.${month}.${year}.` : 'Datums nav norādīts'
}

const formatPrice = (value) => `${Number(value || 0).toFixed(2)} EUR`

onMounted(ensureAdminAccess)
</script>

<style scoped>
.admin-page {
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
  min-height: 100vh;
  background:
    radial-gradient(circle at 12% 18%, rgba(68, 111, 203, 0.34), transparent 42%),
    radial-gradient(circle at 82% 14%, rgba(220, 54, 88, 0.3), transparent 38%),
    radial-gradient(circle at 56% 86%, rgba(66, 141, 106, 0.22), transparent 36%),
    linear-gradient(130deg, #0f1628 0%, #17172a 45%, #2a141d 100%);
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

.state-card,
.admin-hero,
.admin-card {
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 16px;
  background: linear-gradient(165deg, rgba(18, 23, 36, 0.96), rgba(12, 15, 24, 0.96));
  color: #edf2ff;
  box-shadow: 0 22px 58px rgba(0, 0, 0, 0.34);
}

.admin-card :deep(.v-field),
.admin-card :deep(.v-label),
.admin-card :deep(.v-field__input),
.admin-card :deep(.v-icon),
.admin-card :deep(.v-counter) {
  color: #edf2ff;
}

.date-field {
  user-select: none;
}

.date-field :deep(input) {
  caret-color: transparent;
  user-select: none;
  -webkit-user-select: none;
}

.date-field :deep(input::-webkit-calendar-picker-indicator) {
  display: none;
  opacity: 0;
}

.date-field :deep(input::selection) {
  background: transparent;
  color: inherit;
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

.admin-title {
  color: #ffffff;
  font-size: clamp(2rem, 4vw, 3rem);
  line-height: 1.05;
}

.section-title {
  color: #ffffff;
  font-size: clamp(1.5rem, 3vw, 2rem);
  font-weight: 800;
}

.panel-title {
  color: #ffffff;
  font-size: 1.25rem;
  font-weight: 800;
}

.muted-copy,
.movie-state-text,
.screening-meta {
  color: #d2d9e7;
}

.retry-btn,
.admin-icon-btn {
  border-color: rgba(255, 255, 255, 0.22);
  color: #edf2ff;
}

.user-chip {
  border: 1px solid rgba(255, 255, 255, 0.16);
  background: rgba(255, 255, 255, 0.08);
  color: #edf2ff;
}

.admin-submit-btn {
  background: linear-gradient(135deg, #ff5a44, #e50914);
  color: #ffffff !important;
  font-weight: 700;
  box-shadow: 0 8px 26px rgba(229, 9, 20, 0.36);
}

.screening-list {
  display: grid;
  gap: 12px;
}

.screening-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 14px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.04);
}

.screening-row-main {
  min-width: 0;
}

.screening-title {
  color: #ffffff;
  font-size: 1.08rem;
  line-height: 1.25;
}

.screening-actions {
  display: flex;
  flex: 0 0 auto;
  gap: 8px;
}

.admin-delete-btn {
  border-color: rgba(229, 9, 20, 0.5);
  color: #ffd7d9;
}

.admin-delete-btn:hover {
  background: rgba(229, 9, 20, 0.12);
}

@media (max-width: 720px) {
  .nav-pages {
    display: none;
  }

  .brand-link {
    width: 132px;
    min-width: 132px;
  }

  .screening-row {
    align-items: flex-start;
    flex-direction: column;
  }

  .screening-actions {
    width: 100%;
    justify-content: flex-end;
  }
}
</style>
