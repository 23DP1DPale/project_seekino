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
                <h1 class="admin-title mb-2">Lietotāju pārvaldība</h1>
                <p class="muted-copy mb-0">Labo lietotāju segvārdus, e-pastus un piekļuves lomas.</p>
              </div>

              <v-btn
                variant="outlined"
                rounded="lg"
                class="text-none retry-btn"
                prepend-icon="mdi-refresh"
                :loading="usersLoading"
                @click="fetchUsers"
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

          <v-card class="admin-card pa-4 pa-md-5">
            <div class="d-flex align-center justify-space-between flex-wrap ga-3 mb-4">
              <h2 class="panel-title mb-0">Lietotāju saraksts</h2>
              <v-chip class="user-chip" size="small">{{ users.length }} lietotāji</v-chip>
            </div>

            <div v-if="usersLoading" class="py-8 text-center">
              <v-progress-circular indeterminate color="#E50914" />
              <p class="table-state-text mt-3 mb-0">Ielādē lietotājus...</p>
            </div>

            <v-alert v-else-if="usersError" type="error" variant="tonal" class="mb-4">
              <div class="d-flex align-center justify-space-between flex-wrap ga-3">
                <span>{{ usersError }}</span>
                <v-btn variant="outlined" rounded="lg" class="text-none" @click="fetchUsers">
                  Mēģināt vēlreiz
                </v-btn>
              </div>
            </v-alert>

            <v-alert v-else-if="users.length === 0" type="info" variant="tonal" class="mb-4">
              Lietotāju saraksts šobrīd ir tukšs.
            </v-alert>

            <div v-else class="users-table-wrap">
              <v-table class="users-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Segvārds</th>
                    <th>E-pasts</th>
                    <th>Loma</th>
                    <th>Darbības</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="entry in users" :key="entry.id">
                    <td class="user-id-cell">{{ entry.id }}</td>
                    <td>
                      <v-text-field
                        v-model="entry.nickname"
                        variant="outlined"
                        density="compact"
                        hide-details
                        :disabled="savingUserId === entry.id || deletingUserId === entry.id"
                      />
                    </td>
                    <td>
                      <v-text-field
                        v-model="entry.email"
                        type="email"
                        variant="outlined"
                        density="compact"
                        hide-details
                        :disabled="savingUserId === entry.id || deletingUserId === entry.id"
                      />
                    </td>
                    <td>
                      <v-select
                        v-model="entry.role"
                        :items="roleOptions"
                        item-title="title"
                        item-value="value"
                        variant="outlined"
                        density="compact"
                        hide-details
                        :disabled="isCurrentAdmin(entry) || savingUserId === entry.id || deletingUserId === entry.id"
                      />
                    </td>
                    <td>
                      <div class="user-actions">
                        <v-btn
                          color="#E50914"
                          rounded="lg"
                          class="text-none admin-submit-btn"
                          :loading="savingUserId === entry.id"
                          :disabled="Boolean(deletingUserId)"
                          @click="saveUser(entry)"
                        >
                          Saglabāt
                        </v-btn>
                        <v-btn
                          variant="outlined"
                          rounded="lg"
                          class="text-none admin-delete-btn"
                          :loading="deletingUserId === entry.id"
                          :disabled="Boolean(savingUserId)"
                          @click="deleteUser(entry)"
                        >
                          Dzēst
                        </v-btn>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </v-table>
            </div>
          </v-card>
        </template>
      </v-container>
    </v-main>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { apiBaseUrl, useAuth } from '@/services/auth'

const router = useRouter()
const { token, user, fetchMe, clearSession } = useAuth()

const accessLoading = ref(true)
const accessError = ref('')
const users = ref([])
const usersLoading = ref(false)
const usersError = ref('')
const savingUserId = ref(null)
const deletingUserId = ref(null)
const successMessage = ref('')
const errorMessage = ref('')
const roleOptions = [
  { title: 'user', value: 'user' },
  { title: 'admin', value: 'admin' },
]

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

const resetMessages = () => {
  successMessage.value = ''
  errorMessage.value = ''
}

const isCurrentAdmin = (entry) => entry.id === user.value?.id

const ensureAdminAccess = async () => {
  accessLoading.value = true
  accessError.value = ''

  if (!token.value) {
    router.push({ path: '/login', query: { redirect: '/admin/users' } })
    return
  }

  try {
    const currentUser = user.value || await fetchMe()

    if (currentUser?.role !== 'admin') {
      accessError.value = 'Šī lapa pieejama tikai administratoriem.'
      return
    }

    await fetchUsers()
  } catch {
    clearSession()
    router.push({ path: '/login', query: { redirect: '/admin/users' } })
  } finally {
    accessLoading.value = false
  }
}

const fetchUsers = async () => {
  usersLoading.value = true
  usersError.value = ''

  try {
    const response = await fetch(`${apiBaseUrl}/api/admin/users`, {
      headers: authHeaders(),
    })
    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      throw new Error(responseError(data))
    }

    users.value = data.users || []
  } catch (error) {
    users.value = []
    usersError.value = error.message || 'Lietotājus neizdevās ielādēt.'
  } finally {
    usersLoading.value = false
  }
}

const saveUser = async (entry) => {
  resetMessages()
  savingUserId.value = entry.id

  try {
    const response = await fetch(`${apiBaseUrl}/api/admin/users/${entry.id}`, {
      method: 'PUT',
      headers: authHeaders(),
      body: JSON.stringify({
        nickname: entry.nickname,
        email: entry.email,
        role: entry.role,
      }),
    })
    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      throw new Error(responseError(data))
    }

    successMessage.value = data.ziņa || 'Lietotājs veiksmīgi atjaunināts.'
    await fetchUsers()
  } catch (error) {
    errorMessage.value = error.message || 'Lietotāju neizdevās saglabāt.'
  } finally {
    savingUserId.value = null
  }
}

const deleteUser = async (entry) => {
  if (!window.confirm(`Vai tiešām vēlies dzēst lietotāju "${entry.nickname}"?`)) {
    return
  }

  resetMessages()
  deletingUserId.value = entry.id

  try {
    const response = await fetch(`${apiBaseUrl}/api/admin/users/${entry.id}`, {
      method: 'DELETE',
      headers: authHeaders(),
    })
    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      throw new Error(responseError(data))
    }

    successMessage.value = data.ziņa || 'Lietotājs veiksmīgi dzēsts.'
    await fetchUsers()
  } catch (error) {
    errorMessage.value = error.message || 'Lietotāju neizdevās dzēst.'
  } finally {
    deletingUserId.value = null
  }
}

onMounted(ensureAdminAccess)
</script>

<style scoped>
.admin-page {
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

.state-card,
.admin-hero,
.admin-card {
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 16px;
  background: linear-gradient(165deg, rgba(18, 23, 36, 0.96), rgba(12, 15, 24, 0.96));
  color: #edf2ff;
  box-shadow: 0 22px 58px rgba(0, 0, 0, 0.34);
}

.admin-card :deep(:where(.v-field, .v-label, .v-field__input, .v-icon, .v-select__selection-text)) {
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
.table-state-text {
  color: #d2d9e7;
}

.retry-btn {
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

.users-table-wrap {
  overflow-x: auto;
}

.users-table {
  min-width: 920px;
  background: transparent;
  color: #edf2ff;
}

.users-table :deep(table) {
  border-collapse: separate;
  border-spacing: 0 12px;
}

.users-table :deep(th) {
  color: #aeb8cc;
  font-weight: 700;
  white-space: nowrap;
}

.users-table :deep(td) {
  padding-top: 12px;
  padding-bottom: 12px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.04);
}

.users-table :deep(td:first-child) {
  border-left: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px 0 0 12px;
}

.users-table :deep(td:last-child) {
  border-right: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0 12px 12px 0;
}

.user-id-cell {
  color: #ffffff;
  font-weight: 700;
  white-space: nowrap;
}

.user-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  min-width: 220px;
}

.admin-delete-btn {
  border-color: rgba(229, 9, 20, 0.5);
  color: #ffd7d9;
}

.admin-delete-btn:hover {
  background: rgba(229, 9, 20, 0.12);
}
</style>
