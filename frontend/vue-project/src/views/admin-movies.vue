<template>
  <v-app class="admin-page">
    <v-app-bar color="#101114" flat location="top" height="76" class="sticky-app-bar app-bar-shell">
      <v-container class="d-flex align-center px-2 px-md-6 app-bar-inner">
        <RouterLink to="/" class="brand-link ml-2">
          <v-img src="/img/logo_seekino.png" width="160" height="52" class="logo brand-logo" />
        </RouterLink>

        <v-spacer />

        <div class="nav-pages ga-2 mr-2">
          <v-btn variant="text" class="text-none nav-link-btn" to="/filmas">Filmas</v-btn>
          <v-btn variant="text" class="text-none nav-link-btn" to="/seansi">Seansi</v-btn>
          <v-btn variant="text" class="text-none nav-link-btn" to="/profile">Profils</v-btn>
        </div>

        <v-chip v-if="user" class="user-chip" prepend-icon="mdi-shield-account-outline">
          {{ user.nickname }}
        </v-chip>
      </v-container>
    </v-app-bar>

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
                <h1 class="admin-title mb-2">Filmu pārvaldība</h1>
                <p class="muted-copy mb-0">Izveido, labo un dzēs filmas, kas pieejamas SEEKINO katalogā.</p>
              </div>

              <v-btn
                variant="outlined"
                rounded="lg"
                class="text-none retry-btn"
                prepend-icon="mdi-refresh"
                :loading="moviesLoading"
                @click="fetchMovies"
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
                  <h2 class="panel-title mb-0">{{ editingMovieId ? 'Labot filmu' : 'Jauna filma' }}</h2>
                  <v-btn
                    v-if="editingMovieId"
                    variant="text"
                    rounded="lg"
                    class="text-none nav-link-btn"
                    @click="resetForm"
                  >
                    Atcelt labošanu
                  </v-btn>
                </div>

                <v-form @submit.prevent="submitMovie">
                  <v-text-field
                    v-model="form.name"
                    label="Filmas nosaukums"
                    variant="outlined"
                    prepend-inner-icon="mdi-movie-open-outline"
                    class="mb-3"
                    :disabled="formLoading"
                  />
                  <v-text-field
                    v-model.number="form.length"
                    label="Garums minūtēs"
                    type="number"
                    min="1"
                    variant="outlined"
                    prepend-inner-icon="mdi-clock-outline"
                    class="mb-3"
                    :disabled="formLoading"
                  />
                  <v-text-field
                    v-model="form.director"
                    label="Režisors"
                    variant="outlined"
                    prepend-inner-icon="mdi-account-edit-outline"
                    class="mb-3"
                    :disabled="formLoading"
                  />
                  <v-select
                    v-model="form.age_restriction"
                    label="Vecuma ierobežojums"
                    :items="ageRestrictionOptions"
                    variant="outlined"
                    prepend-inner-icon="mdi-shield-alert-outline"
                    class="mb-3"
                    :disabled="formLoading"
                  />
                  <v-select
                    v-model="form.genre_ids"
                    label="Žanri"
                    :items="genres"
                    item-title="name"
                    item-value="id"
                    variant="outlined"
                    prepend-inner-icon="mdi-tag-multiple-outline"
                    multiple
                    chips
                    closable-chips
                    class="mb-3"
                    :loading="genresLoading"
                    :disabled="formLoading || genresLoading"
                  />
                  <v-textarea
                    v-model="form.description"
                    label="Apraksts"
                    variant="outlined"
                    rows="5"
                    class="mb-4"
                    :disabled="formLoading"
                  />

                  <v-btn
                    type="submit"
                    color="#E50914"
                    rounded="lg"
                    class="text-none admin-submit-btn"
                    :loading="formLoading"
                  >
                    {{ editingMovieId ? 'Saglabāt izmaiņas' : 'Izveidot filmu' }}
                  </v-btn>
                </v-form>
              </v-card>
            </v-col>

            <v-col cols="12" lg="7">
              <v-card class="admin-card pa-4 pa-md-5">
                <div class="d-flex align-center justify-space-between flex-wrap ga-3 mb-4">
                  <h2 class="panel-title mb-0">Filmu saraksts</h2>
                  <v-chip class="user-chip" size="small">{{ movies.length }} filmas</v-chip>
                </div>

                <div v-if="moviesLoading" class="py-8 text-center">
                  <v-progress-circular indeterminate color="#E50914" />
                  <p class="movie-state-text mt-3 mb-0">Ielādē filmas...</p>
                </div>

                <v-alert v-else-if="moviesError" type="error" variant="tonal" class="mb-4">
                  <div class="d-flex align-center justify-space-between flex-wrap ga-3">
                    <span>{{ moviesError }}</span>
                    <v-btn variant="outlined" rounded="lg" class="text-none" @click="fetchMovies">
                      Mēģināt vēlreiz
                    </v-btn>
                  </div>
                </v-alert>

                <v-alert v-else-if="movies.length === 0" type="info" variant="tonal" class="mb-4">
                  Filmu saraksts šobrīd ir tukšs.
                </v-alert>

                <div v-else class="movie-list">
                  <div v-for="movie in movies" :key="movie.id" class="movie-row">
                    <div class="movie-row-main">
                      <h3 class="movie-title mb-1">{{ movie.name }}</h3>
                      <p class="movie-meta mb-0">
                        {{ movie.director }} | {{ movie.length }} min | {{ movie.age_restriction }} | {{ genreLabel(movie) }}
                      </p>
                    </div>

                    <div class="movie-actions">
                      <v-btn
                        icon="mdi-pencil-outline"
                        variant="outlined"
                        size="small"
                        class="admin-icon-btn"
                        :disabled="formLoading || deleteLoadingId === movie.id"
                        @click="startEdit(movie)"
                      />
                      <v-btn
                        icon="mdi-delete-outline"
                        variant="outlined"
                        size="small"
                        class="admin-delete-btn"
                        :loading="deleteLoadingId === movie.id"
                        :disabled="formLoading"
                        @click="deleteMovie(movie)"
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
  </v-app>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { apiBaseUrl, useAuth } from '@/services/auth'

const router = useRouter()
const { token, user, fetchMe, clearSession } = useAuth()

const accessLoading = ref(true)
const accessError = ref('')
const movies = ref([])
const genres = ref([])
const moviesLoading = ref(false)
const genresLoading = ref(false)
const moviesError = ref('')
const formLoading = ref(false)
const deleteLoadingId = ref(null)
const editingMovieId = ref(null)
const successMessage = ref('')
const errorMessage = ref('')
const form = ref(emptyForm())
const ageRestrictionOptions = ['Bez ierobežojuma', '7+', '12+', '13+', '16+', '18+']

function emptyForm() {
  return {
    name: '',
    length: null,
    description: '',
    director: '',
    age_restriction: 'Bez ierobežojuma',
    genre_ids: [],
  }
}

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
    router.push({ path: '/login', query: { redirect: '/admin/movies' } })
    return
  }

  try {
    const currentUser = user.value || await fetchMe()

    if (currentUser?.role !== 'admin') {
      accessError.value = 'Šī lapa pieejama tikai administratoriem.'
      return
    }

    await Promise.all([fetchGenres(), fetchMovies()])
  } catch {
    clearSession()
    router.push({ path: '/login', query: { redirect: '/admin/movies' } })
  } finally {
    accessLoading.value = false
  }
}

const fetchGenres = async () => {
  genresLoading.value = true

  try {
    const response = await fetch(`${apiBaseUrl}/api/admin/genres`, {
      headers: authHeaders(),
    })
    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      throw new Error(responseError(data))
    }

    genres.value = data.genres || []
  } catch (error) {
    genres.value = []
    errorMessage.value = error.message || 'Žanrus neizdevās ielādēt.'
  } finally {
    genresLoading.value = false
  }
}

const fetchMovies = async () => {
  moviesLoading.value = true
  moviesError.value = ''

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
    moviesError.value = error.message || 'Filmas neizdevās ielādēt.'
  } finally {
    moviesLoading.value = false
  }
}

const resetMessages = () => {
  successMessage.value = ''
  errorMessage.value = ''
}

const resetForm = () => {
  editingMovieId.value = null
  form.value = emptyForm()
  resetMessages()
}

const startEdit = (movie) => {
  editingMovieId.value = movie.id
  form.value = {
    name: movie.name || '',
    length: movie.length || null,
    description: movie.description || '',
    director: movie.director || '',
    age_restriction: movie.age_restriction || 'Bez ierobežojuma',
    genre_ids: movie.genre_ids || [],
  }
  resetMessages()
}

const genreLabel = (movie) => {
  const movieGenres = movie.genres || []

  return movieGenres.length ? movieGenres.map((genre) => genre.name).join(', ') : 'Žanrs nav norādīts'
}

const submitMovie = async () => {
  resetMessages()
  formLoading.value = true

  const isEditing = Boolean(editingMovieId.value)
  const path = isEditing ? `/api/admin/movies/${editingMovieId.value}` : '/api/admin/movies'

  try {
    const response = await fetch(`${apiBaseUrl}${path}`, {
      method: isEditing ? 'PUT' : 'POST',
      headers: authHeaders(),
      body: JSON.stringify(form.value),
    })
    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      throw new Error(responseError(data))
    }

    successMessage.value = data.ziņa || (isEditing ? 'Filma veiksmīgi atjaunota.' : 'Filma veiksmīgi izveidota.')
    editingMovieId.value = null
    form.value = emptyForm()
    await fetchMovies()
  } catch (error) {
    errorMessage.value = error.message || 'Filmu neizdevās saglabāt.'
  } finally {
    formLoading.value = false
  }
}

const deleteMovie = async (movie) => {
  if (!window.confirm(`Vai tiešām vēlies dzēst filmu "${movie.name}"?`)) {
    return
  }

  resetMessages()
  deleteLoadingId.value = movie.id

  try {
    const response = await fetch(`${apiBaseUrl}/api/admin/movies/${movie.id}`, {
      method: 'DELETE',
      headers: authHeaders(),
    })
    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      throw new Error(responseError(data))
    }

    successMessage.value = data.ziņa || 'Filma veiksmīgi dzēsta.'

    if (editingMovieId.value === movie.id) {
      editingMovieId.value = null
      form.value = emptyForm()
    }

    await fetchMovies()
  } catch (error) {
    errorMessage.value = error.message || 'Filmu neizdevās dzēst.'
  } finally {
    deleteLoadingId.value = null
  }
}

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
.movie-meta {
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

.movie-list {
  display: grid;
  gap: 12px;
}

.movie-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 14px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.04);
}

.movie-row-main {
  min-width: 0;
}

.movie-title {
  color: #ffffff;
  font-size: 1.08rem;
  line-height: 1.25;
}

.movie-actions {
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

  .movie-row {
    align-items: flex-start;
    flex-direction: column;
  }

  .movie-actions {
    width: 100%;
    justify-content: flex-end;
  }
}
</style>
